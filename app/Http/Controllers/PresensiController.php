<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presensi;
use App\Models\VerifikasiWajah;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $search = $request->get('search');
        $pegawais = Pegawai::all();
        
        // Get presensi with search
        $query = Presensi::with('pegawai')
            ->whereDate('tgl_presensi', $today)
            ->orderBy('jam_masuk', 'desc');
            
        if ($search) {
            $query->whereHas('pegawai', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_pegawai', 'like', "%{$search}%");
            });
        }
        
        $presensis = $query->paginate(10);
        
        // Get pegawais without face verification
        $pegawaiTanpaVerifikasi = [];
        foreach ($pegawais as $pegawai) {
            $hasVerifikasi = VerifikasiWajah::where('nomor_induk_pegawai', $pegawai->kode_pegawai)
                ->where('aktif', true)
                ->exists();
            
            if (!$hasVerifikasi) {
                $pegawaiTanpaVerifikasi[] = $pegawai;
            }
        }
        
        // Get today's presensi without face verification
        $presensiTanpaVerifikasi = [];
        foreach ($presensis as $presensi) {
            if (!$presensi->verifikasi_wajah) {
                $presensiTanpaVerifikasi[] = $presensi;
            }
        }
        
        return view('transaksi.presensi.index', compact(
            'presensis', 
            'pegawaiTanpaVerifikasi', 
            'presensiTanpaVerifikasi',
            'today',
            'search'
        ));
    }
    
    public function create()
    {
        $pegawais = Pegawai::all();
        return view('transaksi.presensi.create', compact('pegawais'));
    }
    
    public function faceAttendance()
    {
        return view('transaksi.presensi.face-attendance');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'nullable',
            'jam_keluar' => 'nullable',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);
        
        $data = $request->all();
        $data['jumlah_jam'] = 0;
        
        // Calculate working hours if both times are provided
        if ($request->filled('jam_masuk') && $request->filled('jam_keluar')) {
            $jamMasuk = Carbon::createFromFormat('H:i', $request->jam_masuk);
            $jamKeluar = Carbon::createFromFormat('H:i', $request->jam_keluar);
            $data['jumlah_jam'] = $jamMasuk->diffInMinutes($jamKeluar) / 60;
        }
        
        Presensi::create($data);
        
        return redirect()->route('transaksi.presensi.index')
            ->with('success', 'Data presensi berhasil ditambahkan');
    }
    
    public function show($id)
    {
        $presensi = Presensi::with('pegawai')->findOrFail($id);
        return view('transaksi.presensi.show', compact('presensi'));
    }
    
    public function edit($id)
    {
        $presensi = Presensi::findOrFail($id);
        $pegawais = Pegawai::all();
        return view('transaksi.presensi.edit', compact('presensi', 'pegawais'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'tgl_presensi' => 'required|date',
            'jam_masuk' => 'nullable',
            'jam_keluar' => 'nullable',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);
        
        $presensi = Presensi::findOrFail($id);
        $data = $request->all();
        
        // Calculate working hours if both times are provided
        if ($request->filled('jam_masuk') && $request->filled('jam_keluar')) {
            $jamMasuk = Carbon::createFromFormat('H:i', $request->jam_masuk);
            $jamKeluar = Carbon::createFromFormat('H:i', $request->jam_keluar);
            $data['jumlah_jam'] = $jamMasuk->diffInMinutes($jamKeluar) / 60;
        } else {
            $data['jumlah_jam'] = 0;
        }
        
        $presensi->update($data);
        
        return redirect()->route('transaksi.presensi.index')
            ->with('success', 'Data presensi berhasil diperbarui');
    }
    
    public function destroy($id)
    {
        $presensi = Presensi::findOrFail($id);
        $presensi->delete();
        
        return redirect()->route('transaksi.presensi.index')
            ->with('success', 'Data presensi berhasil dihapus');
    }
    
    // Face verification methods
    public function verifikasiWajahIndex()
    {
        $pegawais = Pegawai::all();
        $verifikasiWajahs = VerifikasiWajah::with('pegawai')
            ->orderBy('tanggal_verifikasi', 'desc')
            ->paginate(10);
        
        return view('transaksi.presensi.verifikasi-wajah.index', 
            compact('pegawais', 'verifikasiWajahs'));
    }
    
    public function verifikasiWajahCreate()
    {
        $pegawais = Pegawai::all();
        return view('transaksi.presensi.verifikasi-wajah.create', compact('pegawais'));
    }
    
    public function verifikasiWajahFaceRecognition()
    {
        $pegawais = Pegawai::all();
        return view('transaksi.presensi.verifikasi-wajah.face-recognition', compact('pegawais'));
    }
    
    public function verifikasiWajahStore(Request $request)
    {
        \Log::info('=== VERIFIKASI WAJAH STORE START ===');
        \Log::info('Request method:', ['method' => $request->method()]);
        \Log::info('Request input:', $request->all());
        \Log::info('Request files:', $request->files->all());
        \Log::info('AJAX check:', ['ajax' => $request->ajax(), 'wantsJson' => $request->wantsJson(), 'expectsJson' => $request->expectsJson()]);
        
        $request->validate([
            'kode_pegawai' => 'required|exists:pegawais,kode_pegawai',
            'foto_wajah' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        \Log::info('Validation passed');
        
        try {
            // Debug log
            \Log::info('Starting verifikasi wajah store', [
                'kode_pegawai' => $request->kode_pegawai,
                'has_file' => $request->hasFile('foto_wajah')
            ]);
            
            // Upload photo
            if ($request->hasFile('foto_wajah')) {
                $file = $request->file('foto_wajah');
                
                // Check if file is valid
                if (!$file->isValid()) {
                    throw new \Exception('File upload is not valid');
                }
                
                \Log::info('File is valid, storing...');
                
                $path = $file->store('foto-wajah', 'public');
                
                if (!$path) {
                    throw new \Exception('Failed to store file');
                }
                
                \Log::info('File stored successfully', ['path' => $path]);
                
                // Deactivate previous face verifications for this employee
                VerifikasiWajah::where('kode_pegawai', $request->kode_pegawai)
                    ->update(['aktif' => false]);
                
                \Log::info('Previous verifications deactivated');
                
                // Create new face verification
                $verifikasi = VerifikasiWajah::create([
                    'kode_pegawai' => $request->kode_pegawai,
                    'foto_wajah' => $path,
                    'aktif' => $request->aktif ?? true,
                    'tanggal_verifikasi' => $request->tanggal_verifikasi ?? now()->toDateString(),
                ]);
                
                \Log::info('Verifikasi wajah created successfully', ['id' => $verifikasi->id]);
                
                // Always return JSON for AJAX requests
                if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                    \Log::info('Returning JSON response');
                    return response()->json([
                        'success' => true,
                        'message' => 'Verifikasi wajah berhasil disimpan',
                        'verifikasi' => $verifikasi
                    ]);
                }
                
                \Log::info('Returning redirect response');
                return redirect()->route('transaksi.presensi.verifikasi-wajah.index')
                    ->with('success', 'Verifikasi wajah berhasil ditambahkan');
            }
            
            throw new \Exception('No file uploaded');
            
        } catch (\Exception $e) {
            \Log::error('Error in verifikasi wajah store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Always return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function verifikasiWajahEdit($id)
    {
        $verifikasi = VerifikasiWajah::findOrFail($id);
        $pegawais = Pegawai::all();
        return view('transaksi.presensi.verifikasi-wajah.edit', 
            compact('verifikasi', 'pegawais'));
    }
    
    public function verifikasiWajahUpdate(Request $request, $id)
    {
        $request->validate([
            'kode_pegawai' => 'required|exists:pegawais,kode_pegawai',
            'foto_wajah' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        $verifikasi = VerifikasiWajah::findOrFail($id);
        $data = $request->all();
        
        // Upload new photo if provided
        if ($request->hasFile('foto_wajah')) {
            $file = $request->file('foto_wajah');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/foto-wajah', $filename);
            $data['foto_wajah'] = $filename;
        }
        
        $verifikasi->update($data);
        
        return redirect()->route('transaksi.presensi.verifikasi-wajah.index')
            ->with('success', 'Verifikasi wajah berhasil diperbarui');
    }
    
    public function verifikasiWajahDestroy($id)
    {
        $verifikasi = VerifikasiWajah::findOrFail($id);
        
        // Delete photo file
        if ($verifikasi->foto_wajah) {
            Storage::delete('public/foto-wajah/' . $verifikasi->foto_wajah);
        }
        
        $verifikasi->delete();
        
        return redirect()->route('transaksi.presensi.verifikasi-wajah.index')
            ->with('success', 'Verifikasi wajah berhasil dihapus');
    }
    
    // API for face verification (for mobile app and web)
    public function apiVerifikasiWajah(Request $request)
    {
        \Log::info('=== API VERIFIKASI WAJAH START ===');
        \Log::info('Request input:', $request->all());
        
        $request->validate([
            'kode_pegawai' => 'required|exists:pegawais,kode_pegawai',
            'foto_wajah' => 'required|string', // Accept base64 string
        ]);
        
        \Log::info('Validation passed');
        
        $pegawai = Pegawai::where('kode_pegawai', $request->kode_pegawai)->first();
        
        if (!$pegawai) {
            \Log::error('Pegawai tidak ditemukan: ' . $request->kode_pegawai);
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan'], 404);
        }
        
        \Log::info('Pegawai found: ' . $pegawai->nama . ' (ID: ' . $pegawai->kode_pegawai . ')');
        
        if (!$pegawai->kode_pegawai) {
            \Log::error('Pegawai Kode is null for: ' . $pegawai->nama);
            return response()->json(['success' => false, 'message' => 'Kode pegawai tidak valid'], 400);
        }
        
        // Check if employee has active face verification
        $verifikasi = VerifikasiWajah::where('kode_pegawai', $request->kode_pegawai)
            ->where('aktif', true)
            ->first();
        
        if (!$verifikasi) {
            \Log::error('Verifikasi wajah tidak aktif untuk pegawai: ' . $request->kode_pegawai);
            return response()->json(['success' => false, 'message' => 'Pegawai belum memiliki verifikasi wajah aktif'], 400);
        }
        
        \Log::info('Verifikasi wajah aktif ditemukan');

        // Process attendance logic
        $today = Carbon::today();
        $now = Carbon::now();
        
        \Log::info('Processing attendance for date: ' . $today->format('Y-m-d'));
        \Log::info('Current time: ' . $now->format('H:i:s'));

        // Check existing attendance for today
        $presensi = Presensi::where('pegawai_id', $pegawai->kode_pegawai)
            ->whereDate('tgl_presensi', $today)
            ->first();
        
        \Log::info('Existing presensi check:', ['found' => $presensi ? 'yes' : 'no']);
        
        if (!$presensi) {
            // First attendance today - CLOCK IN
            \Log::info('Creating new attendance record (CLOCK IN)');
            \Log::info('Pegawai ID for presensi: ' . $pegawai->kode_pegawai);
            
            $presensiData = [
                'pegawai_id' => $pegawai->kode_pegawai,
                'tgl_presensi' => $today,
                'jam_masuk' => $now->format('H:i:s'),
                'status' => 'hadir',
                'verifikasi_wajah' => true,
                'foto_wajah' => $verifikasi->foto_wajah,
                'waktu_verifikasi' => $now,
                'latitude_masuk' => $request->latitude ?? null,
                'longitude_masuk' => $request->longitude ?? null,
                'jumlah_jam' => 0
            ];
            
            \Log::info('Presensi data to create:', $presensiData);
            
            $presensi = Presensi::create($presensiData);
            
            \Log::info('Attendance record created with ID: ' . $presensi->id);
            
            return response()->json([
                'success' => true, 
                'message' => 'Presensi jam masuk berhasil dicatat',
                'type' => 'clock_in',
                'presensi' => [
                    'jam_masuk' => $presensi->jam_masuk,
                    'tgl_presensi' => $presensi->tgl_presensi->format('d/m/Y'),
                    'pegawai' => [
                        'nama' => $pegawai->nama,
                        'nip' => $pegawai->kode_pegawai
                    ]
                ]
            ]);

        } elseif (!$presensi->jam_keluar) {
            // Second attendance today - CLOCK OUT
            \Log::info('Updating attendance record (CLOCK OUT)');
            
            // Parse jam masuk dengan benar
            $jamMasukString = $presensi->tgl_presensi->format('Y-m-d') . ' ' . $presensi->jam_masuk;
            \Log::info('Jam masuk string to parse: ' . $jamMasukString);
            
            $jamMasuk = Carbon::parse($jamMasukString);
            $jumlahJam = $jamMasuk->diffInMinutes($now) / 60; // Convert to hours
            
            \Log::info('Calculated working hours: ' . $jumlahJam);
            
            $presensi->update([
                'jam_keluar' => $now->format('H:i:s'),
                'verifikasi_wajah_keluar' => true,
                'foto_wajah_keluar' => $verifikasi->foto_wajah,
                'waktu_verifikasi_keluar' => $now,
                'latitude_keluar' => $request->latitude ?? null,
                'longitude_keluar' => $request->longitude ?? null,
                'jumlah_jam' => round($jumlahJam, 2)
            ]);
            
            \Log::info('Attendance record updated');
            
            return response()->json([
                'success' => true, 
                'message' => 'Presensi jam keluar berhasil dicatat',
                'type' => 'clock_out',
                'presensi' => [
                    'jam_masuk' => $presensi->jam_masuk,
                    'jam_keluar' => $presensi->jam_keluar,
                    'jumlah_jam' => $presensi->jumlah_jam,
                    'tgl_presensi' => $presensi->tgl_presensi->format('d/m/Y'),
                    'pegawai' => [
                        'nama' => $pegawai->nama,
                        'nip' => $pegawai->kode_pegawai
                    ]
                ]
            ]);

        } else {
            // Already clocked in and out today
            \Log::info('Attendance already complete for today');
            
            return response()->json([
                'success' => false, 
                'message' => 'Anda sudah melakukan presensi masuk dan keluar hari ini',
                'type' => 'already_complete',
                'presensi' => [
                    'jam_masuk' => $presensi->jam_masuk,
                    'jam_keluar' => $presensi->jam_keluar,
                    'jumlah_jam' => $presensi->jumlah_jam,
                    'tgl_presensi' => $presensi->tgl_presensi->format('d/m/Y')
                ]
            ]);
        }
    }
    
    // API untuk face recognition
    public function apiFaceRecognize(Request $request)
    {
        $request->validate([
            'foto_wajah' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        
        try {
            // Upload temporary photo
            if ($request->hasFile('foto_wajah')) {
                $file = $request->file('foto_wajah');
                $tempPath = $file->store('temp/faces', 'public');
                
                // Get all active face verifications
                $verifikasiWajahs = VerifikasiWajah::with('pegawai')
                    ->where('aktif', true)
                    ->get();
                
                $results = [];
                $bestMatch = null;
                $bestScore = 0;
                
                foreach ($verifikasiWajahs as $verifikasi) {
                    // Compare faces (simplified comparison)
                    $similarity = $this->compareFaces($tempPath, $verifikasi->foto_wajah);
                    
                    $results[] = [
                        'pegawai' => $verifikasi->pegawai,
                        'similarity' => $similarity,
                        'verifikasi_id' => $verifikasi->id
                    ];
                    
                    if ($similarity > $bestScore) {
                        $bestScore = $similarity;
                        $bestMatch = $verifikasi;
                    }
                }
                
                // Clean up temp file
                Storage::disk('public')->delete($tempPath);
                
                // Return results
                if ($bestScore > 0.7) { // 70% threshold
                    return response()->json([
                        'success' => true,
                        'recognized' => true,
                        'pegawai' => $bestMatch->pegawai,
                        'confidence' => round($bestScore * 100, 2),
                        'message' => 'Wajah dikenali'
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'recognized' => false,
                        'confidence' => round($bestScore * 100, 2),
                        'message' => 'Wajah tidak dikenali',
                        'all_results' => $results
                    ]);
                }
            }
            
            return response()->json(['success' => false, 'message' => 'No face image provided'], 400);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error processing face recognition: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function apiFaceCompare(Request $request)
    {
        $request->validate([
            'face1' => 'required|string',
            'face2' => 'required|string',
        ]);
        
        try {
            $similarity = $this->compareFaces($request->face1, $request->face2);
            
            return response()->json([
                'success' => true,
                'similarity' => round($similarity * 100, 2),
                'match' => $similarity > 0.7
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error comparing faces: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // API for recent attendance
    public function apiRecentAttendance()
    {
        try {
            $today = Carbon::today();
            $attendances = Presensi::with('pegawai')
                ->whereDate('tgl_presensi', $today)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $data = $attendances->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'waktu' => $attendance->jam_masuk ? $attendance->jam_masuk : ($attendance->jam_keluar ?? ''),
                    'nama' => $attendance->pegawai->nama ?? 'Unknown',
                    'nip' => $attendance->pegawai->kode_pegawai ?? 'Unknown',
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
                        'nomor_induk_pegawai' => $presensi->pegawai->kode_pegawai ?? 'N/A',
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
    
    // Simplified face comparison (replace with actual face recognition library)
    private function compareFaces($face1Path, $face2Path)
    {
        try {
            // This is a simplified comparison
            // In production, you would use a proper face recognition library
            // like face-api.js, OpenCV, or similar
            
            // For demo purposes, return random similarity
            // In real implementation, this would:
            // 1. Extract face features from both images
            // 2. Compare feature vectors
            // 3. Return similarity score
            
            // Simulate processing time
            usleep(500000); // 0.5 second delay
            
            // Return random similarity between 0.3 and 0.95
            return rand(30, 95) / 100;
            
        } catch (Exception $e) {
            return 0;
        }
    }
}
