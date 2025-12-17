<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Helpers\PresensiHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Format jam kerja tanpa trailing zero
     */
    private function formatJamKerja($jam)
    {
        if ($jam == floor($jam)) {
            return number_format($jam, 0) . ' jam';
        } else {
            return number_format($jam, 1) . ' jam';
        }
    }

    public function index()
    {
        $search = request('search');
        $bulan = request('bulan'); // Filter bulan (format: YYYY-MM)
        
        // Query dasar dengan relasi pegawai
        $query = Presensi::with('pegawai')
            ->when($search, function($query) use ($search) {
                return $query->whereHas('pegawai', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('kode_pegawai', 'like', "%{$search}%");
                })
                ->orWhere('status', 'like', "%{$search}%");
            });
        
        // Filter berdasarkan bulan jika dipilih
        if ($bulan) {
            $startDate = Carbon::parse($bulan . '-01')->startOfMonth();
            $endDate = Carbon::parse($bulan . '-01')->endOfMonth();
            $query->whereBetween('tgl_presensi', [$startDate, $endDate]);
        }
        
        // Group by pegawai untuk mendapatkan total jam per bulan
        if ($bulan) {
            // Mode tampilan ringkasan per bulan
            $presensis = $query->get()
                ->groupBy('pegawai_id')
                ->map(function ($items) {
                    $pegawai = $items->first()->pegawai;
                    $totalJamKerja = $items->where('status', 'Hadir')->sum('jumlah_jam');
                    $totalHadir = $items->where('status', 'Hadir')->count();
                    
                    return (object)[
                        'pegawai_id' => $pegawai->id,
                        'pegawai' => $pegawai,
                        'total_jam_kerja' => $totalJamKerja,
                        'total_hadir' => $totalHadir,
                        'bulan' => $bulan,
                        'total_jam_formatted' => $this->formatJamKerja($totalJamKerja)
                    ];
                })
                ->sortBy('pegawai.nama')
                ->values();
                
            $viewMode = 'ringkasan';
        } else {
            // Mode tampilan detail per hari
            $presensis = $query->orderBy('tgl_presensi', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            // Force load pegawai nama untuk setiap presensi
            $presensis->getCollection()->transform(function ($presensi) {
                if ($presensi->pegawai) {
                    $presensi->pegawai->nama_display = $presensi->pegawai->nama ?: $presensi->pegawai->nomor_induk_pegawai;
                }
                return $presensi;
            });
            
            $viewMode = 'detail';
        }
            
        return view('master-data.presensi.index', compact('presensis', 'search', 'bulan', 'viewMode'));
    }

    public function create()
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('master-data.presensi.create', compact('pegawais'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'pegawai_id' => 'required|exists:pegawais,id',
                'tgl_presensi' => 'required|date',
                'jam_masuk' => 'required_if:status,Hadir|nullable|date_format:H:i',
                'jam_keluar' => 'required_if:status,Hadir|nullable|date_format:H:i|after:jam_masuk',
                'status' => 'required|in:Hadir,Izin,Sakit,Absen,Alpa',
                'keterangan' => 'nullable|string|max:255'
            ]);

            // Normalisasi status agar cocok dengan enum DB (bila enum memakai 'Absen')
            if (($validated['status'] ?? '') === 'Alpa') {
                $validated['status'] = 'Absen';
            }

            // Cek apakah sudah ada presensi untuk pegawai di tanggal yang sama
            $existingPresensi = Presensi::where('pegawai_id', $validated['pegawai_id'])
                ->whereDate('tgl_presensi', $validated['tgl_presensi'])
                ->first();

            if ($existingPresensi) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Pegawai sudah memiliki presensi di tanggal yang sama');
            }

            // Hitung jumlah jam kerja hanya untuk status Hadir
            if (($validated['status'] ?? '') === 'Hadir' && !empty($validated['jam_masuk']) && !empty($validated['jam_keluar'])) {
                // Gunakan PresensiHelper untuk hitung durasi dengan pembulatan ke 0,5 jam
                try {
                    $jamMasuk = Carbon::createFromFormat('H:i', $validated['jam_masuk']);
                    $jamKeluar = Carbon::createFromFormat('H:i', $validated['jam_keluar']);
                    
                    $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
                    $validated['jumlah_menit_kerja'] = $durasi['jumlah_menit_kerja'];
                    $validated['jumlah_jam_kerja'] = $durasi['jumlah_jam_kerja'];
                    $validated['jumlah_jam'] = $durasi['jumlah_jam_kerja'];  // Backward compatibility
                } catch (\Exception $e) {
                    // Jika error parsing, hitung dengan cara sederhana
                    $jamMasuk = Carbon::parse($validated['jam_masuk']);
                    $jamKeluar = Carbon::parse($validated['jam_keluar']);
                    $validated['jumlah_jam'] = $jamKeluar->diffInHours($jamMasuk, true);
                    $validated['jumlah_menit_kerja'] = $jamKeluar->diffInMinutes($jamMasuk, true);
                    $validated['jumlah_jam_kerja'] = PresensiHelper::bulatkanKeSetengahJam($validated['jumlah_menit_kerja']);
                }
            } else {
                $validated['jam_masuk'] = null;
                $validated['jam_keluar'] = null;
                $validated['jumlah_menit_kerja'] = 0;
                $validated['jumlah_jam_kerja'] = 0;
                $validated['jumlah_jam'] = 0;
            }

            Presensi::create($validated);

            return redirect()
                ->route('master-data.presensi.index')
                ->with('success', 'Data presensi berhasil disimpan');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $presensi = Presensi::findOrFail($id);
        $pegawais = Pegawai::orderBy('nama')->get();
        return view('master-data.presensi.edit', compact('presensi', 'pegawais'));
    }

    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'pegawai_id' => 'required|exists:pegawais,id',
                'tgl_presensi' => 'required|date',
                'status' => 'required|in:Hadir,Izin,Sakit,Absen,Alpa',
                'keterangan' => 'nullable|string|max:255',
                'jam_masuk' => 'required_if:status,Hadir|nullable|date_format:H:i',
                'jam_keluar' => 'required_if:status,Hadir|nullable|date_format:H:i|after:jam_masuk'
            ]);

            // Dapatkan data presensi
            $presensi = Presensi::findOrFail($id);
            
            // Hitung jumlah jam kerja jika status Hadir
            if ($validated['status'] === 'Hadir' && !empty($validated['jam_masuk']) && !empty($validated['jam_keluar'])) {
                // Gunakan PresensiHelper untuk hitung durasi dengan pembulatan ke 0,5 jam
                $jamMasuk = Carbon::createFromFormat('H:i', $validated['jam_masuk']);
                $jamKeluar = Carbon::createFromFormat('H:i', $validated['jam_keluar']);
                
                $durasi = PresensiHelper::hitungDurasiKerja($jamMasuk, $jamKeluar);
                $validated['jumlah_menit_kerja'] = $durasi['jumlah_menit_kerja'];
                $validated['jumlah_jam_kerja'] = $durasi['jumlah_jam_kerja'];
                $validated['jumlah_jam'] = $durasi['jumlah_jam_kerja'];  // Backward compatibility
            } else {
                $validated['jam_masuk'] = null;
                $validated['jam_keluar'] = null;
                $validated['jumlah_menit_kerja'] = 0;
                $validated['jumlah_jam_kerja'] = 0;
                $validated['jumlah_jam'] = 0;
            }

            // Update data
            $presensi->update([
                'pegawai_id' => $validated['pegawai_id'],
                'tgl_presensi' => $validated['tgl_presensi'],
                'status' => $validated['status'],
                'jam_masuk' => $validated['jam_masuk'],
                'jam_keluar' => $validated['jam_keluar'],
                'jumlah_menit_kerja' => $validated['jumlah_menit_kerja'],
                'jumlah_jam_kerja' => $validated['jumlah_jam_kerja'],
                'jumlah_jam' => $validated['jumlah_jam'],
                'keterangan' => $validated['keterangan']
            ]);

            return redirect()
                ->route('master-data.presensi.index')
                ->with('success', 'Data presensi berhasil diperbarui');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $presensi = Presensi::findOrFail($id);
            $presensi->delete();

            return redirect()
                ->route('master-data.presensi.index')
                ->with('success', 'Data presensi berhasil dihapus');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}