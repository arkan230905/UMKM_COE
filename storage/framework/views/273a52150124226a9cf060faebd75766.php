

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-camera-fill me-2"></i> Verifikasi Wajah
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pegawai)): ?>
                        <!-- MODE: Face Recognition (sudah pilih pegawai) -->
                        <!-- Informasi Pegawai -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <strong>Pegawai:</strong> <?php echo e($pegawai->nama); ?> (<?php echo e($pegawai->kode_pegawai); ?>)
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="row">
                            <!-- Kamera Section -->
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h6 class="mb-3">Kamera Verifikasi</h6>
                                    <div class="camera-container mb-3">
                                        <video id="video" width="100%" height="360" autoplay></video>
                                        <canvas id="canvas" width="100%" height="360" style="display: none;"></canvas>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button id="startCameraBtn" class="btn btn-primary">
                                            <i class="bi bi-camera-video me-1"></i> Mulai Kamera
                                        </button>
                                        <button id="captureBtn" class="btn btn-success" style="display: none;">
                                            <i class="bi bi-camera me-1"></i> Ambil Foto
                                        </button>
                                        <button id="stopCameraBtn" class="btn btn-danger" style="display: none;">
                                            <i class="bi bi-stop-circle me-1"></i> Stop Kamera
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Info Section -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Status Verifikasi</h6>
                                
                                <!-- Verification Status -->
                                <div id="verificationStatus" class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-1"></i> 
                                    Siap untuk verifikasi
                                </div>

                                <!-- Pegawai Info -->
                                <div id="pegawaiInfo" class="card mb-3">
                                    <div class="card-body">
                                        <h6>Informasi Pegawai</h6>
                                        <div id="pegawaiDetails">
                                            <p><strong>Nama:</strong> <?php echo e($pegawai->nama); ?></p>
                                            <p><strong>Kode:</strong> <?php echo e($pegawai->kode_pegawai); ?></p>
                                            <p><strong>Departemen:</strong> <?php echo e($pegawai->departemen ?? '-'); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    <button id="saveBtn" class="btn btn-success" style="display: none;">
                                        <i class="bi bi-check-circle me-1"></i> Simpan Verifikasi
                                    </button>
                                    <button id="retryBtn" class="btn btn-warning" style="display: none;">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                                    </button>
                                    <a href="<?php echo e(route('transaksi.presensi.verifikasi-wajah.create')); ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- MODE: Pilih Pegawai (belum pilih) -->
                        <div class="row">
                            <div class="col-md-8 mx-auto">
                                <h6 class="mb-3">Langkah 1: Pilih Pegawai</h6>
                                <form method="POST" action="<?php echo e(route('transaksi.presensi.verifikasi-wajah.step1')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <div class="mb-3">
                                        <label for="kode_pegawai" class="form-label">Pilih Pegawai</label>
                                        <select name="kode_pegawai" id="kode_pegawai" class="form-select" required>
                                            <option value="">-- Pilih Pegawai --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pegawais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($p->kode_pegawai); ?>">
                                                    <?php echo e($p->nama); ?> (<?php echo e($p->kode_pegawai); ?>)
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-arrow-right me-1"></i> Lanjut ke Verifikasi
                                        </button>
                                        <a href="<?php echo e(route('transaksi.presensi.verifikasi-wajah.index')); ?>" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i> Kembali
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pegawai)): ?>
<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@latest"></script>
<script>
// Global variables
let video = document.getElementById('video');
let canvas = document.getElementById('canvas');
let stream = null;
let model = null;
let currentPegawai = '<?php echo e($pegawai->kode_pegawai); ?>';

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
async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { width: 640, height: 480 } 
        });
        video.srcObject = stream;
        
        document.getElementById('startCameraBtn').style.display = 'none';
        document.getElementById('captureBtn').style.display = 'inline-block';
        document.getElementById('stopCameraBtn').style.display = 'inline-block';
        
        console.log('Camera started');
    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Tidak dapat mengakses kamera. Pastikan kamera sudah terhubung dan diizinkan.');
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
    
    console.log('Camera stopped');
}

// Capture photo
function capturePhoto() {
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    video.style.display = 'none';
    canvas.style.display = 'block';
    
    document.getElementById('captureBtn').style.display = 'none';
    document.getElementById('stopCameraBtn').style.display = 'none';
    
    // Start face recognition
    recognizeFace();
}

// Extract face encoding (simplified)
function extractFaceEncoding() {
    try {
        const ctx = canvas.getContext('2d');
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        
        const encoding = [];
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = Math.min(canvas.width, canvas.height) / 4;
        
        for (let angle = 0; angle < 360; angle += 30) {
            const x = Math.round(centerX + radius * Math.cos(angle * Math.PI / 180));
            const y = Math.round(centerY + radius * Math.sin(angle * Math.PI / 180));
            
            if (x >= 0 && x < canvas.width && y >= 0 && y < canvas.height) {
                const index = (y * canvas.width + x) * 4;
                const gray = 0.299 * data[index] + 0.587 * data[index + 1] + 0.114 * data[index + 2];
                encoding.push(Math.round(gray * 100) / 100);
            }
        }
        
        return encoding;
    } catch (error) {
        console.error('Error extracting face encoding:', error);
        return [];
    }
}

// Face recognition with unified flow
function recognizeFace() {
    console.log('=== FACE RECOGNITION START ===');
    console.log('Current pegawai:', currentPegawai);
    
    document.getElementById('verificationStatus').className = 'alert alert-warning';
    document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memverifikasi wajah...';
    
    const faceEncoding = extractFaceEncoding();
    console.log('Face encoding extracted:', { length: faceEncoding.length });
    
    canvas.toBlob(function(blob) {
        const formData = new FormData();
        formData.append('foto_wajah', blob, 'face_' + Date.now() + '.jpg');
        formData.append('kode_pegawai', currentPegawai);
        formData.append('encoding_wajah', JSON.stringify(faceEncoding));
        
        console.log('Sending API request:', {
            kode_pegawai: currentPegawai,
            hasPhoto: !!blob,
            hasEncoding: faceEncoding.length > 0,
            encodingLength: faceEncoding.length
        });
        
        fetch('<?php echo e(route("transaksi.presensi.verifikasi-wajah.api.recognize")); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('API response:', data);
            
            if (data.success && data.recognized) {
                // SUCCESS - either enrollment or verification
                if (data.is_new_registration) {
                    // New registration
                    document.getElementById('verificationStatus').className = 'alert alert-info';
                    document.getElementById('verificationStatus').innerHTML = `
                        <i class="bi bi-person-plus me-1"></i> 
                        <strong>Pendaftaran Wajah Berhasil!</strong><br>
                        <small>Wajah telah terdaftar untuk ${data.pegawai.nama}</small>
                    `;
                    
                    document.getElementById('saveBtn').innerHTML = '<i class="bi bi-check-circle me-1"></i> Konfirmasi Pendaftaran';
                } else {
                    // Verification success
                    document.getElementById('verificationStatus').className = 'alert alert-success';
                    document.getElementById('verificationStatus').innerHTML = `
                        <i class="bi bi-check-circle me-1"></i> 
                        <strong>Verifikasi Berhasil!</strong><br>
                        <small>Confidence: ${data.confidence}%</small>
                    `;
                    
                    document.getElementById('saveBtn').innerHTML = '<i class="bi bi-check-circle me-1"></i> Konfirmasi Presensi';
                }
                
                document.getElementById('saveBtn').style.display = 'inline-block';
                document.getElementById('retryBtn').style.display = 'none';
                
            } else {
                // FAILED
                document.getElementById('verificationStatus').className = 'alert alert-warning';
                document.getElementById('verificationStatus').innerHTML = `
                    <i class="bi bi-x-circle me-1"></i> 
                    <strong>Verifikasi Gagal</strong><br>
                    <small>${data.message || 'Wajah tidak cocok'}</small>
                `;
                
                document.getElementById('saveBtn').style.display = 'none';
                document.getElementById('retryBtn').style.display = 'inline-block';
            }
        })
        .catch(error => {
            console.error('Face recognition error:', error);
            document.getElementById('verificationStatus').className = 'alert alert-danger';
            document.getElementById('verificationStatus').innerHTML = `
                <i class="bi bi-exclamation-triangle me-1"></i> 
                <strong>Error:</strong> ${error.message}
            `;
        });
    });
}

// Save verification
document.getElementById('saveBtn').addEventListener('click', function() {
    console.log('=== SAVE VERIFICATION ===');
    
    this.disabled = true;
    this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyimpan...';
    
    canvas.toBlob(function(blob) {
        const faceEncoding = extractFaceEncoding();
        
        const formData = new FormData();
        formData.append('kode_pegawai', currentPegawai);
        formData.append('foto_wajah', blob, 'face_' + Date.now() + '.jpg');
        formData.append('encoding_wajah', JSON.stringify(faceEncoding));
        formData.append('aktif', true);
        formData.append('tanggal_verifikasi', new Date().toISOString().split('T')[0]);
        
        fetch('<?php echo e(route("transaksi.presensi.verifikasi-wajah.store")); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Save response:', data);
            
            if (data.success) {
                document.getElementById('verificationStatus').className = 'alert alert-success';
                document.getElementById('verificationStatus').innerHTML = `
                    <i class="bi bi-check-circle me-1"></i> 
                    <strong>Data Berhasil Disimpan!</strong><br>
                    <small>Verifikasi wajah telah tersimpan</small>
                `;
                
                setTimeout(() => {
                    window.location.href = '<?php echo e(route("transaksi.presensi.verifikasi-wajah.index")); ?>';
                }, 2000);
            } else {
                document.getElementById('verificationStatus').className = 'alert alert-danger';
                document.getElementById('verificationStatus').innerHTML = `
                    <i class="bi bi-exclamation-triangle me-1"></i> 
                    <strong>Gagal Menyimpan:</strong><br>
                    <small>${data.message}</small>
                `;
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            document.getElementById('verificationStatus').className = 'alert alert-danger';
            document.getElementById('verificationStatus').innerHTML = `
                <i class="bi bi-exclamation-triangle me-1"></i> 
                <strong>Error:</strong> ${error.message}
            `;
        })
        .finally(() => {
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('saveBtn').innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Verifikasi';
        });
    });
});

// Retry
document.getElementById('retryBtn').addEventListener('click', function() {
    video.style.display = 'block';
    canvas.style.display = 'none';
    
    document.getElementById('verificationStatus').className = 'alert alert-info';
    document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-info-circle me-1"></i> Siap untuk verifikasi';
    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('retryBtn').style.display = 'none';
    document.getElementById('captureBtn').style.display = 'inline-block';
    document.getElementById('stopCameraBtn').style.display = 'inline-block';
});

// Event listeners
document.getElementById('startCameraBtn').addEventListener('click', startCamera);
document.getElementById('stopCameraBtn').addEventListener('click', stopCamera);
document.getElementById('captureBtn').addEventListener('click', capturePhoto);

// Initialize
window.addEventListener('load', function() {
    initializeModel();
    console.log('Face recognition page loaded for pegawai:', currentPegawai);
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    stopCamera();
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/transaksi/presensi/verifikasi-wajah/face-recognition-simple.blade.php ENDPATH**/ ?>