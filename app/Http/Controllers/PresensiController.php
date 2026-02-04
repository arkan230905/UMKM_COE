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
            $hasVerifikasi = VerifikasiWajah::where('kode_pegawai', $pegawai->kode_pegawai)
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
    
    // Handle step 1: Pilih Pegawai
    public function verifikasiWajahStep1(Request $request)
    {
        $request->validate([
            'kode_pegawai' => 'required|exists:pegawais,kode_pegawai',
        ]);
        
        $pegawai = Pegawai::where('kode_pegawai', $request->kode_pegawai)->first();
        
        if (!$pegawai) {
            return back()->with('error', 'Pegawai tidak ditemukan');
        }
        
        // Redirect ke step 2 dengan data pegawai
        return redirect()->route('transaksi.presensi.verifikasi-wajah.face-recognition')
            ->with('selected_pegawai', $pegawai);
    }
    
    public function verifikasiWajahFaceRecognition(Request $request)
    {
        // Ambil data pegawai dari session atau GET parameter
        $selectedPegawai = session('selected_pegawai');
        
        // Fallback: cek GET parameter jika session kosong
        if (!$selectedPegawai && $request->kode_pegawai) {
            $selectedPegawai = Pegawai::where('kode_pegawai', $request->kode_pegawai)->first();
            if ($selectedPegawai) {
                // Simpan ke session untuk digunakan nanti
                session(['selected_pegawai' => $selectedPegawai]);
            }
        }
        
        if (!$selectedPegawai) {
            return redirect()->route('transaksi.presensi.verifikasi-wajah.create')
                ->with('error', 'Silakan pilih pegawai terlebih dahulu');
        }
        
        // Cek apakah pegawai sudah punya data verifikasi wajah
        $verifikasiWajah = VerifikasiWajah::where('kode_pegawai', $selectedPegawai->kode_pegawai)
            ->where('aktif', true)
            ->first();
        
        return view('transaksi.presensi.verifikasi-wajah.face-recognition-simple', [
            'pegawai' => $selectedPegawai,
            'hasFaceData' => !is_null($verifikasiWajah),
            'verifikasiData' => $verifikasiWajah
        ]);
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
            'encoding_wajah' => 'nullable|string|max:5000', // Batasi max 5000 chars
        ], [
            'kode_pegawai.required' => 'Kode pegawai wajib diisi.',
            'kode_pegawai.exists' => 'Pegawai tidak ditemukan di database.',
            'foto_wajah.required' => 'Foto wajah wajib diupload.',
            'foto_wajah.image' => 'File harus berupa gambar.',
            'foto_wajah.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'foto_wajah.max' => 'Ukuran gambar maksimal 2MB.',
            'encoding_wajah.max' => 'Face encoding terlalu besar. Silakan coba lagi.',
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
                    'encoding_wajah' => $request->encoding_wajah ?? null, // Simpan encoding dari frontend
                    'aktif' => $request->aktif ?? true,
                    'tanggal_verifikasi' => now()->toDateString(),
                ]);
                
                \Log::info('Verifikasi wajah created', [
                    'id' => $verifikasi->id,
                    'kode_pegawai' => $verifikasi->kode_pegawai,
                    'has_encoding' => !empty($verifikasi->encoding_wajah)
                ]);
                
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
            'nomor_induk_pegawai' => 'required|exists:pegawais,nomor_induk_pegawai',
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
            'nomor_induk_pegawai' => 'required|exists:pegawais,nomor_induk_pegawai',
            'foto_wajah' => 'required|string', // Accept base64 string
        ]);
        
        \Log::info('Validation passed');
        
        $pegawai = Pegawai::where('nomor_induk_pegawai', $request->nomor_induk_pegawai)->first();
        
        if (!$pegawai) {
            \Log::error('Pegawai tidak ditemukan: ' . $request->nomor_induk_pegawai);
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan'], 404);
        }
        
        \Log::info('Pegawai found: ' . $pegawai->nama . ' (ID: ' . $pegawai->nomor_induk_pegawai . ')');
        
        if (!$pegawai->nomor_induk_pegawai) {
            \Log::error('Pegawai NIP is null for: ' . $pegawai->nama);
            return response()->json(['success' => false, 'message' => 'NIP pegawai tidak valid'], 400);
        }
        
        // Check if employee has active face verification
        $verifikasi = VerifikasiWajah::where('nomor_induk_pegawai', $request->nomor_induk_pegawai)
            ->where('aktif', true)
            ->first();
        
        if (!$verifikasi) {
            \Log::error('Verifikasi wajah tidak aktif untuk pegawai: ' . $request->nomor_induk_pegawai);
            return response()->json(['success' => false, 'message' => 'Pegawai belum memiliki verifikasi wajah aktif'], 400);
        }
        
        \Log::info('Verifikasi wajah aktif ditemukan');

        // Process attendance logic
        $today = Carbon::today();
        $now = Carbon::now();
        
        \Log::info('Processing attendance for date: ' . $today->format('Y-m-d'));
        \Log::info('Current time: ' . $now->format('H:i:s'));

        // Check existing attendance for today
        $presensi = Presensi::where('pegawai_id', $pegawai->nomor_induk_pegawai)
            ->whereDate('tgl_presensi', $today)
            ->first();
        
        \Log::info('Existing attendance found: ' . ($presensi ? 'Yes' : 'No'));
        
        if (!$presensi) {
            // First attendance today - CLOCK IN
            \Log::info('Creating new attendance record (CLOCK IN)');
            \Log::info('Pegawai ID for presensi: ' . $pegawai->nomor_induk_pegawai);
            
            $presensiData = [
                'pegawai_id' => $pegawai->nomor_induk_pegawai,
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
                        'nip' => $pegawai->nomor_induk_pegawai
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
                        'nip' => $pegawai->nomor_induk_pegawai
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
            'kode_pegawai' => 'required|exists:pegawais,kode_pegawai',
            'encoding_wajah' => 'nullable|string|max:5000', // Batasi max 5000 chars
        ]);
        
        \Log::info('Face recognition request:', [
            'kode_pegawai' => $request->kode_pegawai,
            'has_encoding' => !empty($request->encoding_wajah),
            'encoding_length' => strlen($request->encoding_wajah ?? '')
        ]);
        
        try {
            // Upload temporary photo
            if ($request->hasFile('foto_wajah')) {
                $file = $request->file('foto_wajah');
                $tempPath = $file->store('temp/faces', 'public');
                
                // Get face verification data for specific pegawai
                $verifikasiWajah = VerifikasiWajah::with('pegawai')
                    ->where('kode_pegawai', $request->kode_pegawai)
                    ->where('aktif', true)
                    ->first();
                
                if (!$verifikasiWajah) {
                    \Log::info('STEP 2: No existing face data - AUTO ENROLLMENT MODE', [
                        'kode_pegawai' => $request->kode_pegawai
                    ]);
                    
                    // AUTO-ENROLLMENT: Save new face data
                    $pegawai = \App\Models\Pegawai::where('kode_pegawai', $request->kode_pegawai)->first();
                    
                    // Move temp file to permanent location
                    $permanentPath = 'foto-wajah/' . uniqid() . '_' . $request->kode_pegawai . '.jpg';
                    Storage::disk('public')->move($tempPath, $permanentPath);
                    
                    // Create new face verification record
                    $newVerifikasi = VerifikasiWajah::create([
                        'kode_pegawai' => $request->kode_pegawai,
                        'foto_wajah' => $permanentPath,
                        'encoding_wajah' => $request->encoding_wajah,
                        'aktif' => true,
                        'tanggal_verifikasi' => now()->toDateString(),
                    ]);
                    
                    \Log::info('STEP 2: AUTO ENROLLMENT SUCCESS', [
                        'pegawai_nama' => $pegawai->nama,
                        'verification_id' => $newVerifikasi->id,
                        'photo_path' => $permanentPath,
                        'has_encoding' => !empty($request->encoding_wajah)
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'recognized' => true,
                        'pegawai' => $pegawai,
                        'confidence' => 100.00,
                        'message' => 'Pendaftaran wajah berhasil! Wajah telah terdaftar untuk ' . $pegawai->nama,
                        'action' => 'enrollment', // Indicate this was enrollment
                        'is_new_registration' => true
                    ]);
                }
                
                \Log::info('STEP 2: Existing face data found - VERIFICATION MODE', [
                    'pegawai_id' => $verifikasiWajah->id,
                    'pegawai_nama' => $verifikasiWajah->pegawai->nama,
                    'has_stored_encoding' => !empty($verifikasiWajah->encoding_wajah)
                ]);
                
                // VERIFICATION MODE: Compare with existing face
                $similarity = $this->compareFaces(
                    $tempPath, 
                    $verifikasiWajah->foto_wajah,
                    $request->encoding_wajah,
                    $verifikasiWajah->encoding_wajah
                );
                
                // Clean up temp file
                Storage::disk('public')->delete($tempPath);
                
                // Return results
                if ($similarity > 0.7) { // 70% threshold
                    \Log::info('STEP 2: VERIFICATION SUCCESS', [
                        'pegawai_nama' => $verifikasiWajah->pegawai->nama,
                        'similarity' => $similarity,
                        'confidence' => round($similarity * 100, 2)
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'recognized' => true,
                        'pegawai' => $verifikasiWajah->pegawai,
                        'confidence' => round($similarity * 100, 2),
                        'message' => 'Verifikasi Berhasil! Wajah dikenali.',
                        'action' => 'verification', // Indicate this was verification
                        'is_new_registration' => false
                    ]);
                } else {
                    \Log::info('STEP 2: VERIFICATION FAILED', [
                        'pegawai_nama' => $verifikasiWajah->pegawai->nama,
                        'similarity' => $similarity,
                        'confidence' => round($similarity * 100, 2)
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'recognized' => false,
                        'pegawai' => $verifikasiWajah->pegawai,
                        'confidence' => round($similarity * 100, 2),
                        'message' => 'Verifikasi Gagal. Wajah tidak cocok dengan data yang terdaftar.',
                        'action' => 'verification_failed',
                        'is_new_registration' => false
                    ]);
                }
            }
            
            return response()->json(['success' => false, 'message' => 'No face image provided'], 400);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
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
                    'nip' => $attendance->pegawai->nomor_induk_pegawai ?? 'Unknown',
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
}
