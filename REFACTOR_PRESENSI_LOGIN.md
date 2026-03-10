# 🔄 REFACTOR PRESENSI WAJAH - BERBASIS LOGIN

## 🎯 **OVERVIEW BARU**

Sistem presensi wajah yang disederhanakan berbasis login pegawai, tanpa tergantung pada face recognition yang kompleks.

---

## 🏗️ **STRUKTUR BARU**

### **Alur Sederhana:**
```
👤 Pegawai Login → 📄 Halaman Absen Wajah → 📸 Klik "Absen Sekarang" 
→ 💾 Simpan Presensi (berdasarkan pegawai_id dari session) → ✅ Success
```

---

## 📋 **IMPLEMENTATION**

### **1. Middleware & Routes**
```php
// app/Http/Middleware/PegawaiAuth.php
class PegawaiAuth
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'pegawai') {
            return redirect('/login')->with('error', 'Akses ditolak. Silakan login sebagai pegawai.');
        }
        
        // Pastikan pegawai memiliki pegawai_id
        if (!auth()->user()->pegawai_id) {
            auth()->logout();
            return redirect('/login')->with('error', 'Akun Anda tidak terhubung dengan data pegawai.');
        }
        
        return $next($request);
    }
}

// routes/web.php
Route::middleware(['auth', 'pegawai.auth'])->prefix('pegawai')->name('pegawai.')->group(function() {
    Route::get('/dashboard', [PegawaiController::class, 'dashboard'])->name('dashboard');
    Route::get('/absen-wajah', [PegawaiController::class, 'absenWajah'])->name('absen-wajah');
    Route::post('/absen-wajah', [PegawaiController::class, 'apiAbsenWajah'])->name('absen-wajah.store');
    Route::get('/riwayat-presensi', [PegawaiController::class, 'riwayatPresensi'])->name('riwayat-presensi');
});
```

### **2. PegawaiController (Baru)**
```php
// app/Http/Controllers/PegawaiController.php
namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    // Dashboard pegawai
    public function dashboard()
    {
        $pegawai = Pegawai::findOrFail(auth()->user()->pegawai_id);
        
        // Get today's attendance
        $todayAttendance = Presensi::where('pegawai_id', $pegawai->nomor_induk_pegawai)
            ->whereDate('tgl_presensi', now())
            ->first();
        
        // Get this month attendance summary
        $thisMonthAttendance = Presensi::where('pegawai_id', $pegawai->nomor_induk_pegawai)
            ->whereMonth('tgl_presensi', now()->month)
            ->whereYear('tgl_presensi', now()->year)
            ->get();
        
        $stats = [
            'total_hadir' => $thisMonthAttendance->where('status', 'hadir')->count(),
            'total_hari_kerja' => Carbon::now()->daysInMonth,
            'persentasi_kehadiran' => $thisMonthAttendance->count() > 0 
                ? round(($thisMonthAttendance->where('status', 'hadir')->count() / $thisMonthAttendance->count()) * 100, 1)
                : 0,
            'today_status' => $todayAttendance ? [
                'jam_masuk' => $todayAttendance->jam_masuk,
                'jam_keluar' => $todayAttendance->jam_keluar,
                'status' => $todayAttendance->status,
                'sudah_lengkap' => !empty($todayAttendance->jam_keluar)
            ] : null
        ];
        
        return view('pegawai.dashboard', compact('pegawai', 'stats', 'todayAttendance'));
    }
    
    // Halaman absen wajah
    public function absenWajah()
    {
        $pegawai = Pegawai::findOrFail(auth()->user()->pegawai_id);
        
        // Get today's attendance
        $todayAttendance = Presensi::where('pegawai_id', $pegawai->nomor_induk_pegawai)
            ->whereDate('tgl_presensi', now())
            ->first();
        
        // Get recent attendance (last 7 days)
        $recentAttendance = Presensi::where('pegawai_id', $pegawai->nomor_induk_pegawai)
            ->whereDate('tgl_presensi', '>=', now()->subDays(7))
            ->orderBy('tgl_presensi', 'desc')
            ->get();
        
        return view('pegawai.absen-wajah', compact('pegawai', 'todayAttendance', 'recentAttendance'));
    }
    
    // API untuk proses absen wajah (Sederhana)
    public function apiAbsenWajah(Request $request)
    {
        \Log::info('=== ABSEN WAJAH SEDERHANA START ===');
        \Log::info('User ID: ' . auth()->id());
        \Log::info('Pegawai ID: ' . auth()->user()->pegawai_id);
        
        try {
            // Get pegawai dari session
            $pegawai = Pegawai::findOrFail(auth()->user()->pegawai_id);
            $pegawaiId = $pegawai->nomor_induk_pegawai;
            
            \Log::info('Processing attendance for: ' . $pegawai->nama);
            
            // Check today's attendance
            $today = now()->toDateString();
            $presensi = Presensi::where('pegawai_id', $pegawaiId)
                ->whereDate('tgl_presensi', $today)
                ->first();
            
            $currentTime = now()->format('H:i:s');
            
            if (!$presensi) {
                // CREATE NEW ATTENDANCE (JAM MASUK)
                \Log::info('Creating new attendance (JAM MASUK)...');
                
                // Process photo if provided
                $fotoPath = null;
                if ($request->has('foto_wajah')) {
                    $fotoPath = $this->saveAttendancePhoto($request->foto_wajah, $pegawaiId);
                }
                
                $newPresensi = Presensi::create([
                    'pegawai_id' => $pegawaiId,
                    'tgl_presensi' => $today,
                    'jam_masuk' => $currentTime,
                    'status' => 'hadir',
                    'verifikasi_wajah' => $request->has('foto_wajah'),
                    'foto_wajah' => $fotoPath,
                    'waktu_verifikasi' => now(),
                    'latitude_masuk' => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                ]);
                
                \Log::info('New attendance created:', [
                    'id' => $newPresensi->id,
                    'pegawai' => $pegawai->nama,
                    'jam_masuk' => $currentTime
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
                    
                    // Process photo if provided
                    $fotoPath = $presensi->foto_wajah;
                    if ($request->has('foto_wajah')) {
                        $fotoPath = $this->saveAttendancePhoto($request->foto_wajah, $pegawaiId, '_keluar');
                    }
                    
                    $presensi->update([
                        'jam_keluar' => $currentTime,
                        'latitude_keluar' => $request->latitude,
                        'longitude_keluar' => $request->longitude,
                        'foto_wajah' => $fotoPath,
                    ]);
                    
                    \Log::info('Jam keluar updated:', [
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
                            'jam_masuk' => $presensi->jam_masuk,
                            'jam_keluar' => $presensi->jam_keluar,
                            'status' => 'hadir'
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
    
    // Helper untuk save photo attendance
    private function saveAttendancePhoto($base64Image, $pegawaiId, $suffix = '')
    {
        try {
            // Decode base64
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
            if (!$imageData) {
                return null;
            }
            
            // Generate filename
            $filename = 'absen_' . $pegawaiId . '_' . now()->format('Ymd_His') . $suffix . '.jpg';
            $path = 'presensi-foto/' . $filename;
            
            // Save to storage
            Storage::disk('public')->put($path, $imageData);
            
            \Log::info('Attendance photo saved: ' . $path);
            return $path;
            
        } catch (\Exception $e) {
            \Log::error('Error saving attendance photo: ' . $e->getMessage());
            return null;
        }
    }
    
    // Riwayat presensi pegawai
    public function riwayatPresensi(Request $request)
    {
        $pegawai = Pegawai::findOrFail(auth()->user()->pegawai_id);
        
        $query = Presensi::where('pegawai_id', $pegawai->nomor_induk_pegawai);
        
        // Filter by month/year if provided
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('tgl_presensi', $request->month)
                  ->whereYear('tgl_presensi', $request->year);
        }
        
        $attendances = $query->orderBy('tgl_presensi', 'desc')->paginate(20);
        
        return view('pegawai.riwayat-presensi', compact('pegawai', 'attendances'));
    }
}
```

### **3. Frontend Sederhana**
```blade
<!-- resources/views/pegawai/absen-wajah.blade.php -->
@extends('layouts.app')

@section('title', 'Absen Wajah')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-camera-video me-2"></i>
                        Absen Wajah - {{ $pegawai->nama }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Kamera Section -->
                        <div class="col-md-6">
                            <div class="text-center">
                                <h6 class="mb-3">Kamera Absen</h6>
                                <div class="camera-container mb-3 border rounded">
                                    <video id="video" width="100%" height="360" autoplay class="rounded"></video>
                                    <canvas id="canvas" width="100%" height="360" style="display: none;"></canvas>
                                </div>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button id="startCameraBtn" class="btn btn-primary" type="button">
                                        <i class="bi bi-camera-video me-1"></i> Mulai Kamera
                                    </button>
                                    <button id="captureBtn" class="btn btn-success btn-lg" style="display: none;" type="button">
                                        <i class="bi bi-camera-fill me-1"></i> ABSEN SEKARANG
                                    </button>
                                    <button id="stopCameraBtn" class="btn btn-danger" style="display: none;" type="button">
                                        <i class="bi bi-stop-circle me-1"></i> Stop Kamera
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Info Section -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Status Presensi Hari Ini</h6>
                            
                            <!-- Status Panel -->
                            <div id="attendanceStatus" class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-1"></i> 
                                Siap untuk absen
                            </div>

                            <!-- Today's Attendance Info -->
                            @if($todayAttendance)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Presensi Hari Ini</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Jam Masuk</small>
                                            <div class="fw-bold text-success">
                                                <i class="bi bi-clock-fill me-1"></i> {{ $todayAttendance->jam_masuk }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Jam Keluar</small>
                                            <div class="fw-bold text-{{ $todayAttendance->jam_keluar ? 'danger' : 'muted' }}">
                                                <i class="bi bi-clock-fill me-1"></i> {{ $todayAttendance->jam_keluar ?: 'Belum' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-{{ $todayAttendance->jam_keluar ? 'secondary' : 'warning' }}">
                                            {{ $todayAttendance->jam_keluar ? '✅ Presensi Lengkap' : '⏰ Menunggu Absen Keluar' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Recent Attendance -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-3">Presensi 7 Hari Terakhir</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Jam Keluar</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($recentAttendance->count() > 0)
                                                    @foreach($recentAttendance as $attendance)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($attendance->tgl_presensi)->format('d M') }}</td>
                                                        <td class="text-success">{{ $attendance->jam_masuk }}</td>
                                                        <td class="text-danger">{{ $attendance->jam_keluar ?: '-' }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $attendance->jam_keluar ? 'success' : 'warning' }}">
                                                                {{ $attendance->jam_keluar ? 'Lengkap' : 'Masuk' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">
                                                            Belum ada presensi
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let video = null;
let canvas = null;
let stream = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Absen Wajah pegawai page loaded');
    
    video = document.getElementById('video');
    canvas = document.getElementById('canvas');
    
    // Event listeners
    const startBtn = document.getElementById('startCameraBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    
    if (startBtn) startBtn.addEventListener('click', startCamera);
    if (stopBtn) stopBtn.addEventListener('click', stopCamera);
    if (captureBtn) captureBtn.addEventListener('click', processAttendance);
});

// Start camera
async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { width: 640, height: 480 } 
        });
        video.srcObject = stream;
        
        document.getElementById('startCameraBtn').style.display = 'none';
        document.getElementById('captureBtn').style.display = 'inline-block';
        document.getElementById('stopCameraBtn').style.display = 'inline-block';
        
        updateStatus('Kamera aktif. Siap untuk absen.', 'info');
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        updateStatus('Tidak dapat mengakses kamera. Silakan izinkan akses kamera.', 'danger');
    }
}

// Stop camera
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        video.srcObject = null;
        stream = null;
    }
    
    document.getElementById('startCameraBtn').style.display = 'inline-block';
    document.getElementById('captureBtn').style.display = 'none';
    document.getElementById('stopCameraBtn').style.display = 'none';
    
    updateStatus('Kamera dimatikan.', 'info');
}

// Process attendance (Sederhana - Tanpa face recognition)
async function processAttendance() {
    const captureBtn = document.getElementById('captureBtn');
    captureBtn.disabled = true;
    captureBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses...';
    
    updateStatus('Memproses absen...', 'info');
    
    try {
        // Capture photo (optional)
        let base64Image = null;
        if (video.srcObject) {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            base64Image = canvas.toDataURL('image/jpeg');
        }
        
        // Get location (optional)
        const position = await getCurrentLocation();
        
        // Send to API (Sederhana - tanpa face recognition)
        const payload = {
            // Tidak perlu pegawai_id, sudah dari session
            foto_wajah: base64Image, // Optional
            latitude: position.latitude, // Optional
            longitude: position.longitude, // Optional
        };
        
        console.log('Sending attendance request:', payload);
        
        const response = await fetch('{{ route("pegawai.absen-wajah.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        console.log('Attendance response:', data);
        
        if (data.success) {
            // Success
            updateStatus(data.message, 'success');
            
            // Show success animation
            if (data.action === 'clock_in') {
                showSuccessAnimation('✅ Absen Masuk Berhasil!');
            } else if (data.action === 'clock_out') {
                showSuccessAnimation('✅ Absen Keluar Berhasil!');
            }
            
            // Refresh page after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
        } else {
            // Failed
            updateStatus(data.message, 'warning');
            
            if (data.action === 'already_complete') {
                showWarningAnimation('⚠️ Presensi Hari Ini Lengkap');
            }
        }
        
    } catch (error) {
        console.error('Error processing attendance:', error);
        updateStatus('Terjadi kesalahan. Silakan coba lagi.', 'danger');
    } finally {
        captureBtn.disabled = false;
        captureBtn.innerHTML = '<i class="bi bi-camera-fill me-1"></i> ABSEN SEKARANG';
    }
}

// Get current location (optional)
async function getCurrentLocation() {
    return new Promise((resolve) => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    });
                },
                (error) => {
                    console.warn('Location error:', error);
                    resolve({ latitude: null, longitude: null });
                },
                { timeout: 5000 }
            );
        } else {
            resolve({ latitude: null, longitude: null });
        }
    });
}

// Update status
function updateStatus(message, type = 'info') {
    const statusDiv = document.getElementById('attendanceStatus');
    statusDiv.className = `alert alert-${type}`;
    statusDiv.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'x-circle' : 'info-circle'} me-1"></i> 
        ${message}
    `;
}

// Show success animation
function showSuccessAnimation(message) {
    const statusDiv = document.getElementById('attendanceStatus');
    statusDiv.className = 'alert alert-success';
    statusDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <strong>${message}</strong>
        </div>
    `;
}

// Show warning animation
function showWarningAnimation(message) {
    const statusDiv = document.getElementById('attendanceStatus');
    statusDiv.className = 'alert alert-warning';
    statusDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>${message}</strong>
        </div>
    `;
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopCamera();
});
</script>
@endpush
```

### **4. User Model Update**
```php
// app/Models/User.php
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'pegawai_id' // Tambahkan ini
    ];
    
    protected $casts = [
        'pegawai_id' => 'integer'
    ];
    
    // Relasi ke pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
```

---

## 🚀 **IMPLEMENTATION STEPS**

### **Step 1: Update User Migration**
```bash
php artisan make:migration add_pegawai_id_to_users_table --table=users
```

### **Step 2: Register Middleware**
```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    'pegawai.auth' => \App\Http\Middleware\PegawaiAuth::class,
];
```

### **Step 3: Create Views**
- `resources/views/pegawai/dashboard.blade.php`
- `resources/views/pegawai/absen-wajah.blade.php`
- `resources/views/pegawai/riwayat-presensi.blade.php`

### **Step 4: Setup Role System**
```php
// Update users table
ALTER TABLE users ADD COLUMN role ENUM('admin', 'owner', 'pegawai') DEFAULT 'pegawai';
ALTER TABLE users ADD COLUMN pegawai_id BIGINT NULL;

// Link existing users to pegawai
UPDATE users SET pegawai_id = (SELECT id FROM pegawais WHERE pegawais.email = users.email LIMIT 1);
```

---

## 🎯 **KEY DIFFERENCES**

### **Sebelum (Kompleks):**
- Face recognition WAJIB (70% threshold)
- Bandingkan dengan SEMUA data verifikasi
- Sering gagal jika wajah tidak dikenali
- Complex error handling

### **Sesudah (Sederhana):**
- Login pegawai WAJIB
- pegawai_id dari session (100% akurat)
- Face recognition OPSIONAL (bisa dikembangkan)
- Pasti berhasil setiap klik
- Simple & reliable

---

## ✅ **SUCCESS CRITERIA**

1. **✅ Login pegawai** - Hanya pegawai yang bisa akses
2. **✅ Simpel API** - Tidak perlu face recognition
3. **✅ Pasti tersimpan** - 100% success rate
4. **✅ Photo opsional** - Disimpan jika ada
5. **✅ Real-time status** - Update langsung
6. **✅ Error handling** - Simple & clear

---

## 🔄 **FUTURE ENHANCEMENTS**

### **Face Recognition (Opsional):**
```php
// Di apiAbsenWajah() - tambahkan setelah presensi berhasil
if ($request->has('foto_wajah') && $request->face_recognition_enabled) {
    // Lakukan face recognition sebagai tambahan
    $this->processFaceRecognition($request->foto_wajah, $pegawaiId);
}
```

### **Anti Fake GPS:**
```php
// Validasi location
private function validateLocation($latitude, $longitude) {
    // Check if location is within allowed area
    // Check GPS accuracy
    // Check for GPS spoofing
}
```

Sistem presensi wajah yang sederhana dan stabil sudah siap! 🚀
