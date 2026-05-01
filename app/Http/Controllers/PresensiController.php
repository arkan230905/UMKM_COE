<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Pegawai;
use App\Models\RekapPresensiBulanan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Display presensi harian
     */
    public function index(Request $request)
    {
        $filters = [
            'pegawai_id' => $request->get('pegawai_id'),
            'bulan' => $request->get('bulan'),
            'tahun' => $request->get('tahun'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $query = Presensi::with('pegawai')->orderBy('tgl_presensi', 'desc');

        // Filter by pegawai
        if ($filters['pegawai_id']) {
            $query->where('pegawai_id', $filters['pegawai_id']);
        }

        // Filter by periode
        if ($filters['bulan']) {
            $query->where('periode_bulan', $filters['bulan']);
        }

        if ($filters['tahun']) {
            $query->where('periode_tahun', $filters['tahun']);
        }

        // Filter by status
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Filter by search
        if ($filters['search']) {
            $query->whereHas('pegawai', function($q) use ($filters) {
                $q->where('nama', 'like', '%' . $filters['search'] . '%');
            });
        }

        $presensiList = $query->paginate(20);

        // Get list pegawai untuk filter
        $pegawaiList = Pegawai::orderBy('nama')
            ->get();

        // Get list bulan dan tahun untuk filter
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $tahunList = range(Carbon::now()->year - 2, Carbon::now()->year + 1);

        // For backward compatibility with old view
        $search = $filters['search'] ?? '';
        $presensis = $presensiList; // Old view uses $presensis

        return view('transaksi.presensi.index', compact(
            'presensiList',
            'pegawaiList',
            'bulanList',
            'tahunList',
            'filters',
            'search',
            'presensis'
        ));
    }

    /**
     * Show form untuk input presensi
     */
    public function create()
    {
        $pegawaiList = Pegawai::orderBy('nama')
            ->get();
        
        $pegawais = $pegawaiList; // Alias for old view

        return view('transaksi.presensi.create', compact('pegawaiList', 'pegawais'));
    }

    /**
     * Store presensi baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $presensi = Presensi::create($validated);

            return redirect()->route('presensi.index')
                ->with('success', 'Presensi berhasil dicatat');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show detail presensi
     */
    public function show($id)
    {
        $presensi = Presensi::with('pegawai')->find($id);

        if (!$presensi) {
            return redirect()->route('presensi.index')
                ->with('error', 'Presensi tidak ditemukan');
        }

        return view('transaksi.presensi.show', compact('presensi'));
    }

    /**
     * Show form edit presensi
     */
    public function edit($id)
    {
        $presensi = Presensi::find($id);

        if (!$presensi) {
            return redirect()->route('presensi.index')
                ->with('error', 'Presensi tidak ditemukan');
        }

        $pegawaiList = Pegawai::orderBy('nama')
            ->get();
        
        $pegawais = $pegawaiList; // Alias for old view

        return view('transaksi.presensi.edit', compact('presensi', 'pegawaiList', 'pegawais'));
    }

    /**
     * Update presensi
     */
    public function update(Request $request, $id)
    {
        $presensi = Presensi::find($id);

        if (!$presensi) {
            return redirect()->route('presensi.index')
                ->with('error', 'Presensi tidak ditemukan');
        }

        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $presensi->update($validated);

            return redirect()->route('presensi.index')
                ->with('success', 'Presensi berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Delete presensi
     */
    public function destroy($id)
    {
        $presensi = Presensi::find($id);

        if (!$presensi) {
            return redirect()->route('presensi.index')
                ->with('error', 'Presensi tidak ditemukan');
        }

        $presensi->delete();

        return redirect()->route('presensi.index')
            ->with('success', 'Presensi berhasil dihapus');
    }

    /**
     * Get rekap presensi bulanan untuk pegawai
     */
    public function getRekapBulanan($pegawaiId, $bulan, $tahun)
    {
        $rekap = RekapPresensiBulanan::where('pegawai_id', $pegawaiId)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->first();

        if (!$rekap) {
            // Generate if not exists
            $rekap = RekapPresensiBulanan::generateRekap($pegawaiId, $bulan, $tahun);
        }

        return response()->json([
            'success' => true,
            'data' => $rekap
        ]);
    }

    /**
     * Get presensi detail untuk periode tertentu
     */
    public function getDetailPeriode($pegawaiId, $bulan, $tahun)
    {
        $presensiList = Presensi::where('pegawai_id', $pegawaiId)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->orderBy('tgl_presensi')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $presensiList
        ]);
    }

    /**
     * Bulk import presensi dari file
     */
    public function bulkImport(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        // TODO: Implement bulk import functionality
        return back()->with('success', 'Bulk import functionality coming soon');
    }

    /**
     * Halaman absen wajah untuk pegawai (login-based)
     */
    public function pegawaiAbsenWajah()
    {
        // Get pegawai data from logged in user
        $user = auth()->user();
        $pegawai = Pegawai::where('user_id', $user->id)->first();

        if (!$pegawai) {
            return redirect()->route('pegawai.dashboard')
                ->with('error', 'Data pegawai tidak ditemukan');
        }

        // Get today's attendance
        $today = Carbon::today();
        $attendances = Presensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tgl_presensi', $today)
            ->get();

        return view('pegawai.presensi.absen-wajah', compact('pegawai', 'attendances'));
    }

    /**
     * API untuk absen wajah pegawai (login-based)
     */
    public function pegawaiApiAbsenWajah(Request $request)
    {
        try {
            // Get pegawai data from logged in user
            $user = auth()->user();
            $pegawai = Pegawai::where('user_id', $user->id)->first();

            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pegawai tidak ditemukan'
                ], 404);
            }

            $today = Carbon::today();
            $now = Carbon::now();

            // Check existing attendance today
            $existingAttendance = Presensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tgl_presensi', $today)
                ->first();

            // Determine action: clock_in or clock_out
            if (!$existingAttendance) {
                // Clock in
                $presensi = new Presensi();
                $presensi->pegawai_id = $pegawai->id;
                $presensi->tgl_presensi = $today;
                $presensi->jam_masuk = $now->format('H:i:s');
                $presensi->periode_bulan = $today->month;
                $presensi->periode_tahun = $today->year;
                $presensi->status = 'hadir';
                $presensi->keterangan = 'Absen wajah - Clock in';
                
                // Optional: Save photo if provided
                if ($request->has('foto_wajah')) {
                    // TODO: Save photo to storage if needed
                    // $presensi->foto_masuk = $this->saveBase64Image($request->foto_wajah);
                }
                
                // Optional: Save location if provided
                if ($request->has('latitude') && $request->has('longitude')) {
                    $presensi->latitude_masuk = $request->latitude;
                    $presensi->longitude_masuk = $request->longitude;
                }
                
                $presensi->save();

                return response()->json([
                    'success' => true,
                    'action' => 'clock_in',
                    'message' => 'Clock in berhasil! Selamat bekerja.',
                    'data' => [
                        'jam_masuk' => $presensi->jam_masuk,
                        'tgl_presensi' => $presensi->tgl_presensi->format('d/m/Y')
                    ]
                ]);

            } elseif ($existingAttendance && !$existingAttendance->jam_keluar) {
                // Clock out
                $existingAttendance->jam_keluar = $now->format('H:i:s');
                $existingAttendance->keterangan = ($existingAttendance->keterangan ?? '') . ' | Absen wajah - Clock out';
                
                // Optional: Save photo if provided
                if ($request->has('foto_wajah')) {
                    // TODO: Save photo to storage if needed
                    // $existingAttendance->foto_keluar = $this->saveBase64Image($request->foto_wajah);
                }
                
                // Optional: Save location if provided
                if ($request->has('latitude') && $request->has('longitude')) {
                    $existingAttendance->latitude_keluar = $request->latitude;
                    $existingAttendance->longitude_keluar = $request->longitude;
                }
                
                $existingAttendance->save();

                return response()->json([
                    'success' => true,
                    'action' => 'clock_out',
                    'message' => 'Clock out berhasil! Terima kasih atas kerja keras Anda.',
                    'data' => [
                        'jam_masuk' => $existingAttendance->jam_masuk,
                        'jam_keluar' => $existingAttendance->jam_keluar,
                        'tgl_presensi' => $existingAttendance->tgl_presensi->format('d/m/Y')
                    ]
                ]);

            } else {
                // Already complete
                return response()->json([
                    'success' => false,
                    'action' => 'already_complete',
                    'message' => 'Presensi hari ini sudah lengkap (Clock in & Clock out sudah tercatat)',
                    'data' => [
                        'jam_masuk' => $existingAttendance->jam_masuk,
                        'jam_keluar' => $existingAttendance->jam_keluar,
                        'tgl_presensi' => $existingAttendance->tgl_presensi->format('d/m/Y')
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
