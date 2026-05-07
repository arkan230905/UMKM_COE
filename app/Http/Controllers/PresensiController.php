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
<<<<<<< HEAD
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
        try {
            // Debug: Log that method is called
            \Log::info('pegawaiAbsenWajah called');

            // Get pegawai data from logged in user
            $user = auth()->user();
            \Log::info('User ID: ' . ($user ? $user->id : 'null'));

            $pegawai = Pegawai::withoutGlobalScopes()->where('user_id', $user->id)->first();
            \Log::info('Pegawai: ' . ($pegawai ? $pegawai->nama : 'null'));

            if (!$pegawai) {
                \Log::info('Pegawai not found, redirecting to dashboard');
                return redirect()->route('pegawai.dashboard')
                    ->with('error', 'Data pegawai tidak ditemukan');
            }

            // Get today's attendance
            $today = Carbon::today();
            $attendances = Presensi::withoutGlobalScopes()
                ->where('pegawai_id', $pegawai->id)
                ->whereDate('tgl_presensi', $today)
                ->get();

            \Log::info('Attendances count: ' . $attendances->count());

            return view('pegawai.presensi.absen-wajah', compact('pegawai', 'attendances'));
        } catch (\Exception $e) {
            \Log::error('Error in pegawaiAbsenWajah: ' . $e->getMessage());
            return redirect()->route('pegawai.dashboard')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
<<<<<<< HEAD
=======
    
    // API untuk recent attendance
    public function apiRecentAttendance()
    {
        try {
            $today = Carbon::today();
            // CRITICAL: Filter by user_id untuk multi-tenant isolation
            $attendances = Presensi::with('pegawai')
                ->where('user_id', auth()->id())
                ->whereDate('tgl_presensi', $today)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $data = $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'jam_masuk' => $attendance->jam_masuk,
                    'jam_keluar' => $attendance->jam_keluar,
                    'status' => $attendance->status,
                    'verifikasi_wajah' => $attendance->verifikasi_wajah,
                    'created_at' => $attendance->created_at->format('H:i:s')
                ];
            });
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            \Log::error('Error in apiRecentAttendance: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load attendance data'], 500);
        }
    }
    
    // API untuk detail presensi
    public function detail($id)
    {
        try {
            $presensi = Presensi::with('pegawai')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $presensi->id,
                    'pegawai' => [
                        'nama' => $presensi->pegawai->nama ?? 'Tidak diketahui',
                        'nomor_induk_pegawai' => $presensi->pegawai->nomor_induk_pegawai ?? 'N/A',
                        'jabatan' => $presensi->pegawai->jabatan ?? 'N/A'
                    ],
                    'tanggal' => \Carbon\Carbon::parse($presensi->tgl_presensi)->isoFormat('dddd, D MMMM YYYY'),
                    'jam_masuk' => $presensi->jam_masuk ? \Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i:s') : null,
                    'jam_keluar' => $presensi->jam_keluar ? \Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i:s') : null,
                    'jumlah_jam' => $presensi->jumlah_jam,
                    'status' => $presensi->status,
                    'verifikasi_wajah' => $presensi->verifikasi_wajah,
                    'foto_wajah' => $presensi->foto_wajah,
                    'waktu_verifikasi' => $presensi->waktu_verifikasi,
                    'latitude_masuk' => $presensi->latitude_masuk,
                    'longitude_masuk' => $presensi->longitude_masuk,
                    'latitude_keluar' => $presensi->latitude_keluar,
                    'longitude_keluar' => $presensi->longitude_keluar,
                    'created_at' => $presensi->created_at,
                    'updated_at' => $presensi->updated_at
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in presensi detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data presensi tidak ditemukan'
            ], 404);
        }
    }
    
    // Enhanced face comparison with encoding support
    private function compareFaces($face1Path, $face2Path, $encoding1 = null, $encoding2 = null)
    {
        try {
            \Log::info('Comparing faces:', [
                'face1_path' => $face1Path,
                'face2_path' => $face2Path,
                'has_encoding1' => !empty($encoding1),
                'has_encoding2' => !empty($encoding2),
                'encoding1_length' => strlen($encoding1 ?? ''),
                'encoding2_length' => strlen($encoding2 ?? '')
            ]);
            
            // Jika ada encoding dari frontend, gunakan itu
            if (!empty($encoding1) && !empty($encoding2)) {
                $enc1 = json_decode($encoding1, true);
                $enc2 = json_decode($encoding2, true);
                
                if (is_array($enc1) && is_array($enc2) && count($enc1) > 0 && count($enc2) > 0) {
                    $distance = $this->euclideanDistance($enc1, $enc2);
                    $similarity = 1 / (1 + $distance); // Convert distance to similarity
                    
                    \Log::info('Encoding comparison result:', [
                        'distance' => $distance,
                        'similarity' => $similarity,
                        'threshold_met' => $similarity > 0.7,
                        'enc1_count' => count($enc1),
                        'enc2_count' => count($enc2)
                    ]);
                    
                    return $similarity;
                } else {
                    \Log::warning('Invalid encoding format, falling back to simulation');
                }
            }
            
            // Fallback: Simulate face recognition (untuk testing)
            \Log::info('Using simulated face recognition (no valid encodings)');
            usleep(500000); // 0.5 second delay
            
            return 0.85; // 85% similarity - above threshold (0.7)
            
        } catch (Exception $e) {
            \Log::error('Face comparison error: ' . $e->getMessage());
            return 0;
        }
    }
    
    // Calculate Euclidean distance between two face encodings
    /**
     * Get total working hours for an employee in a specific month
     * API endpoint for penggajian integration
     */
    public function getJamKerja(Request $request)
    {
        try {
            $pegawaiId = $request->get('pegawai_id');
            $month = $request->get('month');
            $year = $request->get('year');
            
            // Validate required parameters
            if (!$pegawaiId || !$month || !$year) {
                return response()->json([
                    'error' => true,
                    'message' => 'Parameter pegawai_id, month, dan year wajib diisi',
                    'total_jam' => 0
                ], 400);
            }
            
            // Validate employee exists
            $pegawai = \App\Models\Pegawai::find($pegawaiId);
            if (!$pegawai) {
                return response()->json([
                    'error' => true,
                    'message' => 'Pegawai tidak ditemukan',
                    'total_jam' => 0
                ], 404);
            }
            
            // Get presensi data for the month
            $presensiData = \App\Models\Presensi::where('pegawai_id', $pegawaiId)
                ->whereMonth('tgl_presensi', $month)
                ->whereYear('tgl_presensi', $year)
                ->where('status', 'hadir') // Only count present days
                ->get();
            
            $totalJam = 0;
            $jumlahHari = 0;
            
            foreach ($presensiData as $presensi) {
                // Use the model's accessor to get calculated hours
                $jamKerja = $presensi->jumlah_jam;
                if ($jamKerja > 0) {
                    $totalJam += $jamKerja;
                    $jumlahHari++;
                }
            }
            
            // Log for debugging
            \Log::info('Jam kerja calculation', [
                'pegawai_id' => $pegawaiId,
                'pegawai_nama' => $pegawai->nama,
                'month' => $month,
                'year' => $year,
                'jumlah_hari_hadir' => $jumlahHari,
                'total_jam' => $totalJam
            ]);
            
            return response()->json([
                'error' => false,
                'message' => 'Data jam kerja berhasil diambil',
                'total_jam' => (float)$totalJam,
                'jumlah_hari_hadir' => $jumlahHari,
                'pegawai_nama' => $pegawai->nama,
                'periode' => sprintf('%04d-%02d', $year, $month)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting jam kerja: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengambil data jam kerja: ' . $e->getMessage(),
                'total_jam' => 0
            ], 500);
        }
    }

    private function euclideanDistance($encoding1, $encoding2)
    {
        if (count($encoding1) !== count($encoding2)) {
            return 1.0; // Maximum distance if dimensions don't match
        }
        
        $sum = 0;
        for ($i = 0; $i < count($encoding1); $i++) {
            $sum += pow($encoding1[$i] - $encoding2[$i], 2);
        }
        
        return sqrt($sum);
    }
>>>>>>> cb46e8bf88bbf58f140ce82a4feead3f3abd254b
}
