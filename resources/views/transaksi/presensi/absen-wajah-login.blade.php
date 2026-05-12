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
                        Absen Wajah - Sistem Login
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
                            <h6 class="mb-3">Status Presensi</h6>
                            
                            <!-- Status Panel -->
                            <div id="attendanceStatus" class="alert alert-info mb-3">
                                <i class="bi bi-info-circle me-1"></i> 
                                Siap untuk absen (Login-based)
                            </div>

                            <!-- Pegawai Info -->
                            <div id="pegawaiInfo" class="card mb-3" style="display: none;">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img id="pegawaiPhoto" class="rounded-circle me-3" 
                                             src="" alt="Foto Pegawai" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-1" id="pegawaiNama">-</h6>
                                            <small class="text-muted" id="pegawaiNip">-</small>
                                            <div class="badge bg-success mt-1" id="pegawaiJabatan">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Attendance Info -->
                            <div id="attendanceInfo" class="card mb-3" style="display: none;">
                                <div class="card-body">
                                    <h6 class="mb-3">Info Presensi Hari Ini</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Jam Masuk</small>
                                            <div class="fw-bold" id="jamMasuk">-</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Jam Keluar</small>
                                            <div class="fw-bold" id="jamKeluar">-</div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">Status</small>
                                        <div class="badge bg-success" id="attendanceStatusBadge">Hadir</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Attendance -->
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="mb-3">Presensi Hari Ini</h6>
                                    <div id="recentAttendance" class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Pegawai</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Jam Keluar</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="attendanceTableBody">
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        Belum ada presensi hari ini
                                                    </td>
                                                </tr>
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
    console.log('Absen Wajah page loaded (Login-based)');
    
    video = document.getElementById('video');
    canvas = document.getElementById('canvas');
    
    // Event listeners
    const startBtn = document.getElementById('startCameraBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    
    if (startBtn) startBtn.addEventListener('click', startCamera);
    if (stopBtn) stopBtn.addEventListener('click', stopCamera);
    if (captureBtn) captureBtn.addEventListener('click', processAttendance);
    
    // Load recent attendance
    loadRecentAttendance();
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

// Process attendance (Sederhana - Berbasis Login)
async function processAttendance() {
    const captureBtn = document.getElementById('captureBtn');
    captureBtn.disabled = true;
    captureBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memproses...';
    
    updateStatus('Memproses absen...', 'info');
    
    try {
        // Capture photo (Optional - Tanpa Face Recognition)
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
        
        // Send to API (Sederhana - Tanpa Face Recognition)
        // pegawai_id sudah dari session, tidak perlu dikirim
        const payload = {
            foto_wajah: base64Image, // Optional
            latitude: position.latitude, // Optional
            longitude: position.longitude, // Optional
        };
        
        console.log('Sending attendance request (login-based):', payload);
        
        const response = await fetch('{{ route("transaksi.presensi.api.absen-wajah") }}', {
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
            
            // Show employee info
            if (data.pegawai) {
                showPegawaiInfo(data.pegawai);
            }
            
            // Show attendance info
            if (data.presensi) {
                showAttendanceInfo(data.presensi);
            }
            
            // Show success animation
            if (data.action === 'clock_in') {
                showSuccessAnimation('✅ Absen Masuk Berhasil!');
            } else if (data.action === 'clock_out') {
                showSuccessAnimation('✅ Absen Keluar Berhasil!');
            }
            
            // Refresh recent attendance
            loadRecentAttendance();
            
            // Show photo preview
            if (base64Image) {
                showPhotoPreview(base64Image);
            }
            
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

// Show employee info
function showPegawaiInfo(pegawai) {
    const infoDiv = document.getElementById('pegawaiInfo');
    const photo = document.getElementById('pegawaiPhoto');
    const nama = document.getElementById('pegawaiNama');
    const nip = document.getElementById('pegawaiNip');
    const jabatan = document.getElementById('pegawaiJabatan');
    
    if (pegawai.foto_wajah) {
        photo.src = '/storage/' + pegawai.foto_wajah;
    }
    nama.textContent = pegawai.nama;
    nip.textContent = pegawai.nomor_induk_pegawai || '-';
    jabatan.textContent = pegawai.jabatan || '-';
    
    infoDiv.style.display = 'block';
}

// Show attendance info
function showAttendanceInfo(presensi) {
    const infoDiv = document.getElementById('attendanceInfo');
    const jamMasuk = document.getElementById('jamMasuk');
    const jamKeluar = document.getElementById('jamKeluar');
    const statusBadge = document.getElementById('attendanceStatusBadge');
    
    jamMasuk.textContent = presensi.jam_masuk || '-';
    jamKeluar.textContent = presensi.jam_keluar || '-';
    statusBadge.textContent = presensi.status || 'Hadir';
    statusBadge.className = `badge bg-${presensi.status === 'hadir' ? 'success' : 'warning'}`;
    
    infoDiv.style.display = 'block';
}

// Load recent attendance
async function loadRecentAttendance() {
    try {
        const response = await fetch('{{ route("transaksi.presensi.api.recent") }}');
        const data = await response.json();
        
        if (data.success) {
            displayRecentAttendance(data.attendances);
        } else {
            displayRecentAttendance([]);
        }
    } catch (error) {
        console.error('Error loading recent attendance:', error);
        displayRecentAttendance([]);
    }
}

// Display recent attendance
function displayRecentAttendance(attendances) {
    const tbody = document.getElementById('attendanceTableBody');
    
    if (attendances.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    Belum ada presensi hari ini
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = attendances.map(attendance => `
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    ${attendance.foto_wajah ? 
                        `<img src="/storage/${attendance.foto_wajah}" class="rounded-circle me-2" 
                              style="width: 30px; height: 30px; object-fit: cover;">` : 
                        '<i class="bi bi-person-circle me-2"></i>'
                    }
                    <div>
                        <div>${attendance.pegawai_nama || '-'}</div>
                        <small class="text-muted">${attendance.pegawai_nip || '-'}</small>
                    </div>
                </div>
            </td>
            <td>${attendance.jam_masuk || '-'}</td>
            <td>${attendance.jam_keluar || '-'}</td>
            <td>
                <span class="badge bg-${attendance.status === 'hadir' ? 'success' : 'warning'}">
                    ${attendance.status || '-'}
                </span>
            </td>
        </tr>
    `).join('');
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
