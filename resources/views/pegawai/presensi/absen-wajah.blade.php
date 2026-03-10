@extends('layouts.app')

@section('title', 'Absen Wajah Pegawai')

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

                            <!-- Pegawai Info -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Data Pegawai</h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3" style="width: 60px; height: 60px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="bi bi-person-circle text-primary" viewBox="0 0 16 16">
                                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                                                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $pegawai->nama }}</h6>
                                            <small class="text-muted">{{ $pegawai->nomor_induk_pegawai }}</small>
                                            <div class="badge bg-primary mt-1">{{ $pegawai->jabatan ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Today's Attendance Info -->
                            @if($attendances->count() > 0)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Presensi Hari Ini</h6>
                                    @foreach($attendances as $attendance)
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Jam Masuk</small>
                                            <div class="fw-bold text-success">
                                                <i class="bi bi-clock-fill me-1"></i> {{ $attendance->jam_masuk }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Jam Keluar</small>
                                            <div class="fw-bold text-{{ $attendance->jam_keluar ? 'danger' : 'muted' }}">
                                                <i class="bi bi-clock-fill me-1"></i> {{ $attendance->jam_keluar ?: 'Belum' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-{{ $attendance->jam_keluar ? 'secondary' : 'warning' }}">
                                            {{ $attendance->jam_keluar ? '✅ Presensi Lengkap' : '⏰ Menunggu Absen Keluar' }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Recent Attendance -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-3">Riwayat Presensi Hari Ini</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Jam Masuk</th>
                                                    <th>Jam Keluar</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($attendances->count() > 0)
                                                    @foreach($attendances as $attendance)
                                                    <tr>
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
                                                        <td colspan="3" class="text-center text-muted">
                                                            Belum ada presensi hari ini
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

<!-- Modal for Photo Preview -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" class="img-fluid rounded" src="" alt="Preview">
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
    console.log('Absen Wajah Pegawai page loaded');
    
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

// Process attendance (Login-based - Tanpa Face Recognition)
async function processAttendance() {
    const captureBtn = document.getElementById('captureBtn');
    captureBtn.disabled = true;
    captureBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses...';
    
    updateStatus('Memproses presensi...', 'info');
    
    try {
        // Capture photo (Optional)
        let base64Image = null;
        if (video.srcObject) {
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            base64Image = canvas.toDataURL('image/jpeg');
        }
        
        // Get location (Optional)
        const position = await getCurrentLocation();
        
        // Send to API (Login-based)
        const payload = {
            foto_wajah: base64Image, // Optional
            latitude: position.latitude, // Optional
            longitude: position.longitude, // Optional
        };
        
        console.log('Sending attendance request (pegawai login):', payload);
        
        const response = await fetch('{{ route("pegawai.presensi.api.absen-wajah") }}', {
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
            
            // Show photo preview
            if (base64Image) {
                showPhotoPreview(base64Image);
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

// Get current location (Optional)
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

// Show photo preview
function showPhotoPreview(base64Image) {
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    const previewImg = document.getElementById('previewImage');
    previewImg.src = base64Image;
    modal.show();
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopCamera();
});
</script>
@endpush
