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
        $search = $request->get('search');
        $dateFilter = $request->get('date_filter') ?? Carbon::today()->toDateString();
        
        // Build query
        $query = Presensi::with('pegawai')->orderBy('tgl_presensi', 'desc')->orderBy('jam_masuk', 'desc');
        
        // Apply date filter
        if ($dateFilter) {
            $query->whereDate('tgl_presensi', $dateFilter);
        }
        
        // Apply search
        if ($search) {
            $query->whereHas('pegawai', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_pegawai', 'like', "%{$search}%");
            });
        }
        
        $presensis = $query->paginate(15);
        
        return view('transaksi.presensi.index', compact(
            'presensis', 
            'search',
            'dateFilter'
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
    
    // API untuk proses absen wajah otomatis (Sederhana - Berbasis Login)
    public function apiAbsenWajah(Request $request)
    {
        \Log::info('=== ABSEN WAJAH LOGIN-BASED START ===');
        \Log::info('User ID: ' . (auth()->check() ? auth()->id() : 'Not logged in'));
        
        try {
            // 1️⃣ VALIDASI LOGIN PEGAWAI
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu'
                ], 401);
            }
            
            $user = auth()->user();
            if (!$user->pegawai_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak terhubung dengan data pegawai'
                ], 400);
            }
            
            // 2️⃣ AMBIL DATA PEGAWAI (Tanpa Face Recognition)
            $pegawai = Pegawai::where('nomor_induk_pegawai', $user->pegawai_id)
                ->orWhere('id', $user->pegawai_id)
                ->first();
            
            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pegawai tidak ditemukan'
                ], 404);
            }
            
            $pegawaiId = $pegawai->nomor_induk_pegawai;
            \Log::info('Processing attendance for: ' . $pegawai->nama . ' (ID: ' . $pegawaiId . ')');
            
            // 3️⃣ CEK PRESENSI HARI INI
            $today = now()->toDateString();
            $presensi = Presensi::where('pegawai_id', $pegawaiId)
                ->whereDate('tgl_presensi', $today)
                ->first();
            
            $currentTime = now()->format('H:i:s');
            $now = now();
            
            \Log::info('Attendance check:', [
                'pegawai_id' => $pegawaiId,
                'today' => $today,
                'existing_presensi' => $presensi ? 'YES' : 'NO',
                'jam_masuk' => $presensi ? $presensi->jam_masuk : null,
                'jam_keluar' => $presensi ? $presensi->jam_keluar : null
            ]);
            
            // 4️⃣ PROSES FOTO (Optional - Tanpa Face Recognition)
            $fotoPath = null;
            if ($request->has('foto_wajah')) {
                $base64Image = $request->foto_wajah;
                \Log::info('Processing attendance photo...', [
                    'has_photo' => !empty($base64Image),
                    'photo_length' => strlen($base64Image)
                ]);
                
                // Decode dan simpan foto
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                if ($imageData) {
                    $filename = 'absen_' . $pegawaiId . '_' . now()->format('Ymd_His') . '.jpg';
                    $path = 'presensi-foto/' . $filename;
                    Storage::disk('public')->put($path, $imageData);
                    $fotoPath = $path;
                    \Log::info('Attendance photo saved: ' . $path);
                }
            }
            
            // 5️⃣ LOGIKA PRESENSI SEDERHANA
            if (!$presensi) {
                // CREATE NEW ATTENDANCE (JAM MASUK)
                \Log::info('Creating new attendance (JAM MASUK)...');
                
                $newPresensi = Presensi::create([
                    'pegawai_id' => $pegawaiId,
                    'tgl_presensi' => $today,
                    'jam_masuk' => $currentTime,
                    'status' => 'hadir',
                    'verifikasi_wajah' => !empty($fotoPath), // True jika ada foto
                    'foto_wajah' => $fotoPath,
                    'waktu_verifikasi' => $now,
                    'latitude_masuk' => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                ]);
                
                \Log::info('New attendance created successfully:', [
                    'id' => $newPresensi->id,
                    'pegawai' => $pegawai->nama,
                    'jam_masuk' => $currentTime,
                    'has_photo' => !empty($fotoPath)
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Absen masuk berhasil! Selamat bekerja, ' . $pegawai->nama,
                    'action' => 'clock_in',
                    'presensi' => [
                        'id' => $newPresensi->id,
                        'pegawai_nama' => $pegawai->nama,
                        'pegawai_id' => $pegawaiId,
                        'tanggal' => $today,
                        'jam_masuk' => $currentTime,
                        'jam_keluar' => null,
                        'status' => 'hadir',
                        'verifikasi_wajah' => $newPresensi->verifikasi_wajah,
                        'foto_wajah' => $fotoPath
                    ],
                    'pegawai' => [
                        'nama' => $pegawai->nama,
                        'nomor_induk_pegawai' => $pegawai->nomor_induk_pegawai,
                        'jabatan' => $pegawai->jabatan ?? '-',
                    ],
                    'time' => $currentTime
                ]);
                
            } else {
                // UPDATE EXISTING ATTENDANCE
                if (empty($presensi->jam_keluar)) {
                    // UPDATE JAM KELUAR
                    \Log::info('Updating jam keluar...');
                    
                    // Update foto jika ada (opsional)
                    $updateData = [
                        'jam_keluar' => $currentTime,
                        'latitude_keluar' => $request->latitude,
                        'longitude_keluar' => $request->longitude,
                    ];
                    
                    if ($fotoPath) {
                        $updateData['foto_wajah'] = $fotoPath;
                    }
                    
                    $presensi->update($updateData);
                    
                    \Log::info('Jam keluar updated successfully:', [
                        'id' => $presensi->id,
                        'pegawai' => $pegawai->nama,
                        'jam_keluar' => $currentTime
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Absen keluar berhasil! Terima kasih, ' . $pegawai->nama,
                        'action' => 'clock_out',
                        'presensi' => [
                            'id' => $presensi->id,
                            'pegawai_nama' => $pegawai->nama,
                            'pegawai_id' => $pegawaiId,
                            'tanggal' => $today,
                            'jam_masuk' => $presensi->jam_masuk,
                            'jam_keluar' => $currentTime,
                            'status' => 'hadir',
                            'verifikasi_wajah' => $presensi->verifikasi_wajah
                        ],
                        'pegawai' => [
                            'nama' => $pegawai->nama,
                            'nomor_induk_pegawai' => $pegawai->nomor_induk_pegawai,
                            'jabatan' => $pegawai->jabatan ?? '-',
                        ],
                        'time' => $currentTime
                    ]);
                    
                } else {
                    // ALREADY COMPLETE
                    \Log::info('Attendance already complete for today');
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah lengkap absen hari ini (jam masuk: ' . $presensi->jam_masuk . ', jam keluar: ' . $presensi->jam_keluar . ')',
                        'action' => 'already_complete',
                        'presensi' => [
                            'id' => $presensi->id,
                            'pegawai_nama' => $pegawai->nama,
                            'pegawai_id' => $pegawaiId,
                            'tanggal' => $today,
                            'jam_masuk' => $presensi->jam_masuk,
                            'jam_keluar' => $presensi->jam_keluar,
                            'status' => 'hadir'
                        ],
                        'pegawai' => [
                            'nama' => $pegawai->nama,
                            'nomor_induk_pegawai' => $pegawai->nomor_induk_pegawai,
                            'jabatan' => $pegawai->jabatan ?? '-',
                        ]
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in absen wajah: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }
    
    // Halaman absen wajah untuk pegawai yang sedang login
    public function pegawaiAbsenWajah()
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        if (!$pegawai) {
            return redirect()->route('login')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
        }

        $today = now()->toDateString();
        $attendances = Presensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tgl_presensi', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pegawai.presensi.absen-wajah', compact('pegawai', 'attendances'));
    }

    // API absen wajah berbasis user login (tanpa cari pegawai dari face recognition global)
    public function pegawaiApiAbsenWajah(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi.'
                ], 401);
            }
            
            $pegawai = $user->pegawai;

            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ini belum terhubung dengan data pegawai.'
                ], 400);
            }

            // Validasi minimal
            $request->validate([
                'foto_wajah' => 'nullable|string',
                'latitude'   => 'nullable|numeric',
                'longitude'  => 'nullable|numeric',
            ]);

            $today = now()->toDateString();
            $now = now();
            $currentTime = $now->format('H:i:s');

            // Simpan foto jika dikirim
            $fotoPath = null;
            if ($request->filled('foto_wajah')) {
                try {
                    $base64Image = $request->foto_wajah;
                    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                    
                    if ($imageData === false) {
                        throw new \Exception('Invalid base64 image data');
                    }
                    
                    $fileName = 'presensi/' . $pegawai->kode_pegawai . '_' . now()->format('Ymd_His') . '.jpg';
                    Storage::disk('public')->put($fileName, $imageData);
                    $fotoPath = $fileName;
                    
                    // Update foto pegawai jika belum ada
                    if (empty($pegawai->foto_wajah)) {
                        $pegawai->update([
                            'foto_wajah' => $fotoPath,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error saving photo: ' . $e->getMessage());
                    // Continue without photo
                }
            }

            $presensi = Presensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tgl_presensi', $today)
                ->first();

            if (!$presensi) {
                // Jam masuk
                $newPresensi = Presensi::create([
                    'pegawai_id'        => $pegawai->id,
                    'tgl_presensi'      => $today,
                    'jam_masuk'         => $currentTime,
                    'status'            => 'hadir',
                    'verifikasi_wajah'  => true,
                    'foto_wajah'        => $fotoPath,
                    'waktu_verifikasi'  => $now,
                    'latitude_masuk'    => $request->latitude,
                    'longitude_masuk'   => $request->longitude,
                ]);

                return response()->json([
                    'success'   => true,
                    'message'   => 'Absen masuk berhasil.',
                    'action'    => 'clock_in',
                    'presensi'  => $newPresensi,
                    'pegawai'   => [
                        'nama'                => $pegawai->nama,
                        'kode_pegawai'        => $pegawai->kode_pegawai,
                        'jabatan'             => $pegawai->jabatan ?? '-',
                        'foto_wajah'          => $fotoPath,
                    ],
                    'time'      => $currentTime,
                ]);
            }

            if (empty($presensi->jam_keluar)) {
                // Jam keluar
                $presensi->update([
                    'jam_keluar'       => $currentTime,
                    'latitude_keluar'  => $request->latitude,
                    'longitude_keluar' => $request->longitude,
                ]);

                return response()->json([
                    'success'   => true,
                    'message'   => 'Absen keluar berhasil.',
                    'action'    => 'clock_out',
                    'presensi'  => $presensi->fresh(),
                    'pegawai'   => [
                        'nama'                => $pegawai->nama,
                        'kode_pegawai'        => $pegawai->kode_pegawai,
                        'jabatan'             => $pegawai->jabatan ?? '-',
                        'foto_wajah'          => $presensi->foto_wajah,
                    ],
                    'time'      => $currentTime,
                ]);
            }

            // Sudah lengkap
            return response()->json([
                'success'  => false,
                'message'  => 'Presensi hari ini sudah lengkap (masuk & keluar).',
                'action'   => 'already_complete',
                'presensi' => $presensi,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error pegawaiApiAbsenWajah: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses presensi.'
            ], 500);
        }
    }
    
    // API untuk recent attendance
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
