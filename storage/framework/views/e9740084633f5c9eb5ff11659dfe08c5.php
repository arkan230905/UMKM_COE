<?php $__env->startSection('content'); ?>
<style>
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.employee-recognized {
    transition: all 0.3s ease;
}

.recognition-success {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-camera-fill me-2"></i> Presensi dengan Verifikasi Wajah
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Camera Section -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="camera-container">
                                <video id="video" width="640" height="480" autoplay></video>
                                <canvas id="canvas" width="640" height="480" style="display: none;"></canvas>
                                <div id="faceOverlay" class="face-overlay"></div>
                            </div>
                            <div class="mt-3">
                                <button id="startCameraBtn" class="btn btn-primary">
                                    <i class="bi bi-camera-video me-1"></i> Mulai Kamera
                                </button>
                                <button id="stopCameraBtn" class="btn btn-danger" style="display: none;">
                                    <i class="bi bi-stop-circle me-1"></i> Stop Kamera
                                </button>
                                <button id="captureBtn" class="btn btn-success" style="display: none;">
                                    <i class="bi bi-camera me-1"></i> Absen Sekarang
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="status-panel">
                                <h6>Status Presensi</h6>
                                <div id="attendanceStatus" class="alert alert-info">
                                    <i class="bi bi-info-circle me-1"></i> Siap untuk presensi
                                </div>
                                <div id="faceDetected" class="mt-2">
                                    <small class="text-muted">Deteksi wajah: <span id="faceStatus">Menunggu...</span></small>
                                </div>
                                <div id="confidence" class="mt-2">
                                    <small class="text-muted">Akurasi: <span id="confidenceValue">-</span></small>
                                </div>
                                <div id="pegawaiInfo" class="mt-3" style="display: none;">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">Informasi Pegawai</h6>
                                            <div id="pegawaiDetails"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Details -->
                    <div id="attendanceDetails" style="display: none;">
                        <!-- Details will be inserted here by JavaScript -->
                    </div>

                    <!-- Recent Attendance -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Presensi Hari Ini</h6>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Keterangan</th>
                                            <th>Waktu</th>
                                            <th>Nama Pegawai</th>
                                            <th>NIP</th>
                                            <th>Status</th>
                                            <th>Verifikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentAttendance">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada presensi hari ini</td>
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

<style>
.camera-container {
    position: relative;
    display: inline-block;
}

#video {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    width: 100%;
    max-width: 640px;
    height: auto;
}

.face-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.face-box {
    position: absolute;
    border: 2px solid #28a745;
    border-radius: 4px;
}

.status-panel {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.recognition-success {
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@latest"></script>
<script>
let video = document.getElementById('video');
let canvas = document.getElementById('canvas');
let stream = null;
let model = null;

// Initialize face detection model
async function initializeModel() {
    try {
        model = await faceLandmarksDetection.createDetector(
            faceLandmarksDetection.SupportedModels.MediaPipeFaceMesh,
            {
                runtime: 'tfjs',
                refineLandmarks: true,
                maxFaces: 1
            }
        );
        console.log('Face detection model loaded');
    } catch (error) {
        console.error('Error loading face detection model:', error);
    }
}

// Start camera
document.getElementById('startCameraBtn').addEventListener('click', async function() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: 640, 
                height: 480,
                facingMode: 'user'
            } 
        });
        video.srcObject = stream;
        
        document.getElementById('startCameraBtn').style.display = 'none';
        document.getElementById('stopCameraBtn').style.display = 'inline-block';
        document.getElementById('captureBtn').style.display = 'inline-block';
        
        // Start face detection
        detectFaces();
        loadRecentAttendance();
    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Tidak dapat mengakses kamera. Pastikan kamera sudah diizinkan.');
    }
});

// Stop camera
document.getElementById('stopCameraBtn').addEventListener('click', function() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    
    document.getElementById('startCameraBtn').style.display = 'inline-block';
    document.getElementById('stopCameraBtn').style.display = 'none';
    document.getElementById('captureBtn').style.display = 'none';
});

// Detect faces in real-time
async function detectFaces() {
    if (!stream) return;
    
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, 640, 480);
    
    if (model) {
        try {
            const faces = await model.estimateFaces(canvas);
            drawFaceBoxes(faces);
            updateFaceStatus(faces.length > 0);
        } catch (error) {
            console.error('Face detection error:', error);
        }
    }
    
    requestAnimationFrame(detectFaces);
}

// Draw face boxes
function drawFaceBoxes(faces) {
    const overlay = document.getElementById('faceOverlay');
    overlay.innerHTML = '';
    
    faces.forEach(face => {
        const box = document.createElement('div');
        box.className = 'face-box';
        
        const x = face.box.xMin;
        const y = face.box.yMin;
        const width = face.box.width;
        const height = face.box.height;
        
        box.style.left = x + 'px';
        box.style.top = y + 'px';
        box.style.width = width + 'px';
        box.style.height = height + 'px';
        
        overlay.appendChild(box);
    });
}

// Update face status
function updateFaceStatus(detected) {
    const status = document.getElementById('faceStatus');
    if (detected) {
        status.textContent = 'Wajah Terdeteksi ✓';
        status.className = 'text-success';
    } else {
        status.textContent = 'Tidak ada wajah terdeteksi';
        status.className = 'text-danger';
    }
}

// Capture and recognize face
document.getElementById('captureBtn').addEventListener('click', function() {
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, 640, 480);
    
    const imageData = canvas.toDataURL('image/jpeg');
    
    // Show processing status
    document.getElementById('attendanceStatus').className = 'alert alert-warning';
    document.getElementById('attendanceStatus').innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memverifikasi wajah...';
    
    // Send to API for recognition
    recognizeFace(imageData);
});

// Recognize face via API
function recognizeFace(imageData) {
    // Convert data URL to blob
    fetch(imageData)
        .then(res => res.blob())
        .then(blob => {
            const formData = new FormData();
            formData.append('foto_wajah', blob, 'face_' + Date.now() + '.jpg');
            
            fetch('<?php echo e(route("transaksi.presensi.verifikasi-wajah.api.recognize")); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.recognized) {
                        // Face recognized
                        document.getElementById('attendanceStatus').className = 'alert alert-success recognition-success';
                        document.getElementById('attendanceStatus').innerHTML = '<i class="bi bi-check-circle me-1"></i> ' + data.message;
                        document.getElementById('confidenceValue').textContent = data.confidence + '%';
                        
                        // Show employee info
                        showEmployeeInfo(data.pegawai);
                        
                        // Hide capture button after successful recognition
                        document.getElementById('captureBtn').style.display = 'none';
                        
                    } else {
                        // Face not recognized
                        document.getElementById('attendanceStatus').className = 'alert alert-danger';
                        document.getElementById('attendanceStatus').innerHTML = '<i class="bi bi-x-circle me-1"></i> ' + data.message;
                        document.getElementById('confidenceValue').textContent = data.confidence + '%';
                        document.getElementById('pegawaiInfo').style.display = 'none';
                    }
                } else {
                    document.getElementById('attendanceStatus').className = 'alert alert-danger';
                    document.getElementById('attendanceStatus').innerHTML = '<i class="bi bi-x-circle me-1"></i> Error: ' + data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('attendanceStatus').className = 'alert alert-danger';
                document.getElementById('attendanceStatus').innerHTML = '<i class="bi bi-x-circle me-1"></i> Terjadi kesalahan saat verifikasi';
            });
        });
}

// Show employee info
function showEmployeeInfo(pegawai) {
    document.getElementById('pegawaiDetails').innerHTML = `
        <div class="employee-recognized">
            <div class="alert alert-success mb-3">
                <i class="bi bi-person-check me-2"></i>
                <strong>Pegawai Dikenali:</strong> ${pegawai.nama}
            </div>
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-3">Informasi Pegawai</h6>
                    <p class="mb-2"><strong>Nama:</strong> ${pegawai.nama}</p>
                    <p class="mb-2"><strong>NIP:</strong> ${pegawai.nomor_induk_pegawai}</p>
                    <p class="mb-0"><strong>Jabatan:</strong> ${pegawai.jabatan}</p>
                    <input type="hidden" id="pegawaiNama" value="${pegawai.nama}">
                    <input type="hidden" id="pegawaiNIP" value="${pegawai.nomor_induk_pegawai}">
                </div>
            </div>
            <div class="mt-3 text-center">
                <button id="saveAttendanceBtn" class="btn btn-success btn-lg">
                    <i class="bi bi-save me-2"></i> Simpan Presensi
                </button>
            </div>
        </div>
    `;
    document.getElementById('pegawaiInfo').style.display = 'block';
    
    // Add animation for appearance
    const pegawaiInfo = document.getElementById('pegawaiInfo');
    pegawaiInfo.classList.add('fade-in');
    setTimeout(() => {
        pegawaiInfo.classList.remove('fade-in');
    }, 500);
    
    // Re-attach event listener to the new button
    const saveBtn = document.getElementById('saveAttendanceBtn');
    saveBtn.addEventListener('click', function() {
        console.log('=== SAVE ATTENDANCE CLICKED ===');
        
        const pegawaiNama = document.getElementById('pegawaiNama')?.value;
        const pegawaiNIP = document.getElementById('pegawaiNIP')?.value;
        
        console.log('Pegawai Nama:', pegawaiNama);
        console.log('Pegawai NIP:', pegawaiNIP);
        
        if (pegawaiNama && pegawaiNIP) {
            const pegawai = {
                nama: pegawaiNama,
                nomor_induk_pegawai: pegawaiNIP
            };
            
            console.log('Pegawai object:', pegawai);
            
            // Show loading state
            this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Menyimpan...';
            this.disabled = true;
            
            // Process attendance
            processAttendance(pegawai);
            
            // Reset button after delay
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-save me-2"></i> Simpan Presensi';
                this.disabled = false;
                
                // Show capture button again for next attendance
                document.getElementById('captureBtn').style.display = 'inline-block';
            }, 3000);
        } else {
            console.error('Data pegawai tidak lengkap');
            alert('Data pegawai tidak lengkap. Silakan coba lagi.');
        }
    });
}

// Process attendance
function processAttendance(pegawai) {
    console.log('=== PROCESS ATTENDANCE START ===');
    console.log('Pegawai data:', pegawai);
    
    const formData = new FormData();
    formData.append('nomor_induk_pegawai', pegawai.nomor_induk_pegawai);
    formData.append('foto_wajah', canvas.toDataURL('image/jpeg'));
    
    console.log('FormData created:');
    for (let [key, value] of formData.entries()) {
        console.log(`- ${key}:`, value);
    }
    
    const url = '<?php echo e(route("transaksi.presensi.api.verifikasi-wajah")); ?>';
    console.log('Sending to URL:', url);
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Fetch response status:', response.status);
        console.log('Fetch response headers:', response.headers);
        
        const contentType = response.headers.get('content-type');
        console.log('Response content type:', contentType);
        
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.log('Response text:', text);
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
            });
        }
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            console.log('Attendance saved successfully');
            
            // Reload recent attendance
            loadRecentAttendance();
            
            // Show success message based on type
            let message = '';
            let alertClass = '';
            
            if (data.type === 'clock_in') {
                message = `<i class="bi bi-door-open me-1"></i> ${data.message}`;
                alertClass = 'alert alert-success';
            } else if (data.type === 'clock_out') {
                message = `<i class="bi bi-door-closed me-1"></i> ${data.message}`;
                alertClass = 'alert alert-info';
            } else if (data.type === 'already_complete') {
                message = `<i class="bi bi-exclamation-triangle me-1"></i> ${data.message}`;
                alertClass = 'alert alert-warning';
            }
            
            // Update status
            document.getElementById('attendanceStatus').className = alertClass;
            document.getElementById('attendanceStatus').innerHTML = message;
            
            // Show attendance details
            if (data.presensi) {
                const details = document.getElementById('attendanceDetails');
                if (details) {
                    details.innerHTML = `
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6>Detail Presensi Hari Ini</h6>
                            <p><strong>Tanggal:</strong> ${data.presensi.tgl_presensi}</p>
                            ${data.presensi.jam_masuk ? `<p><strong>Jam Masuk:</strong> ${data.presensi.jam_masuk}</p>` : ''}
                            ${data.presensi.jam_keluar ? `<p><strong>Jam Keluar:</strong> ${data.presensi.jam_keluar}</p>` : ''}
                            ${data.presensi.jumlah_jam ? `<p><strong>Total Jam:</strong> ${data.presensi.jumlah_jam} jam</p>` : ''}
                        </div>
                    `;
                    details.style.display = 'block';
                }
            }
            
        } else {
            console.error('Server returned error:', data);
            document.getElementById('attendanceStatus').className = 'alert alert-warning';
            document.getElementById('attendanceStatus').innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> ' + data.message;
        }
    })
    .catch(error => {
        console.error('Error processing attendance:', error);
        console.error('Error stack:', error.stack);
        document.getElementById('attendanceStatus').className = 'alert alert-danger';
        document.getElementById('attendanceStatus').innerHTML = '<i class="bi bi-x-circle me-1"></i> Terjadi kesalahan saat memproses presensi';
    });
}

// Load recent attendance
function loadRecentAttendance() {
    console.log('=== LOADING RECENT ATTENDANCE ===');
    
    fetch('/api/recent-attendance')
        .then(response => {
            console.log('Recent attendance response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Recent attendance data:', data);
            
            const tbody = document.getElementById('recentAttendance');
            if (tbody) {
                if (data.length > 0) {
                    tbody.innerHTML = data.map(attendance => {
                        // Determine attendance type and keterangan
                        let keterangan = '';
                        let waktu = '';
                        let typeLabel = '';
                        let typeClass = '';
                        
                        if (attendance.jam_keluar) {
                            keterangan = 'Absen Keluar';
                            waktu = attendance.jam_keluar;
                            typeLabel = 'Keluar';
                            typeClass = 'bg-info';
                        } else if (attendance.jam_masuk) {
                            keterangan = 'Absen Masuk';
                            waktu = attendance.jam_masuk;
                            typeLabel = 'Masuk';
                            typeClass = 'bg-success';
                        } else {
                            keterangan = 'Unknown';
                            waktu = attendance.waktu || '';
                            typeLabel = 'Unknown';
                            typeClass = 'bg-secondary';
                        }
                        
                        return `
                            <tr>
                                <td><span class="badge ${typeClass}">${keterangan}</span></td>
                                <td>${waktu}</td>
                                <td>${attendance.nama}</td>
                                <td>${attendance.nip}</td>
                                <td><span class="badge bg-success">${attendance.status}</span></td>
                                <td><span class="badge bg-success">Terverifikasi Wajah</span></td>
                            </tr>
                        `;
                    }).join('');
                    
                    console.log('Table updated with', data.length, 'records');
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="bi bi-clock-history me-2"></i>Belum ada data presensi hari ini
                            </td>
                        </tr>
                    `;
                    console.log('No attendance data found');
                }
            } else {
                console.error('Table tbody element not found');
            }
        })
        .catch(error => {
            console.error('Error loading recent attendance:', error);
            const tbody = document.getElementById('recentAttendance');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>Gagal memuat data presensi
                        </td>
                    </tr>
                `;
            }
        });
}

// Initialize on load
window.addEventListener('load', function() {
    initializeModel();
});

// Cleanup on unload
window.addEventListener('beforeunload', function() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/transaksi/presensi/face-attendance.blade.php ENDPATH**/ ?>