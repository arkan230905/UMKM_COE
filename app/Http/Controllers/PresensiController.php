<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index()
    {
        $search = request('search');
        $presensis = Presensi::with('pegawai')
            ->when($search, function($query) use ($search) {
                return $query->whereHas('pegawai', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nomor_induk_pegawai', 'like', "%{$search}%");
                })
                ->orWhere('status', 'like', "%{$search}%");
            })
            ->orderBy('tgl_presensi', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('master-data.presensi.index', compact('presensis', 'search'));
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
                $jamMasuk = Carbon::parse($validated['jam_masuk']);
                $jamKeluar = Carbon::parse($validated['jam_keluar']);
                $validated['jumlah_jam'] = $jamKeluar->diffInHours($jamMasuk, true);
            } else {
                $validated['jam_masuk'] = null;
                $validated['jam_keluar'] = null;
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
            $validated = $request->validate([
                'pegawai_id' => 'required|exists:pegawais,id',
                'tgl_presensi' => 'required|date',
                'jam_masuk' => 'required_if:status,Hadir|nullable|date_format:H:i',
                'jam_keluar' => 'required_if:status,Hadir|nullable|date_format:H:i|after:jam_masuk',
                'status' => 'required|in:Hadir,Izin,Sakit,Absen,Alpa',
                'keterangan' => 'nullable|string|max:255'
            ]);

            $presensi = Presensi::findOrFail($id);
            
            // Cek apakah ada presensi lain untuk pegawai di tanggal yang sama
            $existingPresensi = Presensi::where('pegawai_id', $validated['pegawai_id'])
                ->whereDate('tgl_presensi', $validated['tgl_presensi'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingPresensi) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Pegawai sudah memiliki presensi di tanggal yang sama');
            }

            // Normalisasi status Alpa->Absen bila diperlukan
            if (($validated['status'] ?? '') === 'Alpa') {
                $validated['status'] = 'Absen';
            }

            // Hitung jumlah jam kerja hanya untuk status Hadir
            if (($validated['status'] ?? '') === 'Hadir' && !empty($validated['jam_masuk']) && !empty($validated['jam_keluar'])) {
                $jamMasuk = Carbon::parse($validated['jam_masuk']);
                $jamKeluar = Carbon::parse($validated['jam_keluar']);
                $validated['jumlah_jam'] = $jamKeluar->diffInHours($jamMasuk, true);
            } else {
                $validated['jam_masuk'] = null;
                $validated['jam_keluar'] = null;
                $validated['jumlah_jam'] = 0;
            }

            $presensi->update($validated);

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