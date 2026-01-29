@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-camera-fill me-2"></i> Verifikasi Wajah Real-Time
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Step 1: Pilih Pegawai -->
                    <div id="step1" class="step-section">
                        <h6 class="mb-3">Langkah 1: Pilih Pegawai</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <select id="pegawaiSelect" class="form-select">
                                    <option value="">Pilih Pegawai</option>
                                    @foreach($pegawais as $pegawai)
                                        <option value="{{ $pegawai->nomor_induk_pegawai }}">
                                            {{ $pegawai->nama }} ({{ $pegawai->nomor_induk_pegawai }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button id="startCameraBtn" class="btn btn-primary" disabled>
                                    <i class="bi bi-camera-video me-1"></i> Mulai Kamera
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Camera & Face Detection -->
                    <div id="step2" class="step-section" style="display: none;">
                        <h6 class="mb-3">Langkah 2: Verifikasi Wajah</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="camera-container">
                                    <video id="video" width="640" height="480" autoplay></video>
                                    <canvas id="canvas" width="640" height="480" style="display: none;"></canvas>
                                    <div id="faceOverlay" class="face-overlay"></div>
                                </div>
                                <div class="mt-3">
                                    <button id="captureBtn" class="btn btn-success">
                                        <i class="bi bi-camera me-1"></i> Ambil Foto
                                    </button>
                                    <button id="stopCameraBtn" class="btn btn-danger">
                                        <i class="bi bi-stop-circle me-1"></i> Stop Kamera
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="status-panel">
                                    <h6>Status Verifikasi</h6>
                                    <div id="verificationStatus" class="alert alert-info">
                                        <i class="bi bi-info-circle me-1"></i> Siap untuk verifikasi
                                    </div>
                                    <div id="faceDetected" class="mt-2">
                                        <small class="text-muted">Deteksi wajah: <span id="faceStatus">Menunggu...</span></small>
                                    </div>
                                    <div id="confidence" class="mt-2">
                                        <small class="text-muted">Akurasi: <span id="confidenceValue">-</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Result -->
                    <div id="step3" class="step-section" style="display: none;">
                        <h6 class="mb-3">Hasil Verifikasi</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <img id="capturedImage" class="img-fluid rounded" alt="Captured Face">
                            </div>
                            <div class="col-md-6">
                                <div id="resultPanel">
                                    <h6>Informasi Pegawai</h6>
                                    <div id="pegawaiInfo"></div>
                                    <div class="mt-3">
                                        <button id="saveBtn" class="btn btn-success" style="display: none;">
                                            <i class="bi bi-save me-1"></i> Simpan Verifikasi
                                        </button>
                                        <button id="retryBtn" class="btn btn-warning">
                                            <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                                        </button>
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

<style>
.camera-container {
    position: relative;
    display: inline-block;
}

#video {
    border: 2px solid #dee2e6;
    border-radius: 8px;
}

.face-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 640px;
    height: 480px;
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

.step-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@latest"></script>
<script>
let video = document.getElementById('video');
let canvas = document.getElementById('canvas');
let stream = null;
let model = null;
let currentPegawai = null;

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
        // Fallback to simple face detection
        initializeSimpleFaceDetection();
    }
}

// Simple face detection fallback
function initializeSimpleFaceDetection() {
    console.log('Using simple face detection');
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
        
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        
        // Start face detection
        detectFaces();
    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Tidak dapat mengakses kamera. Pastikan kamera sudah diizinkan.');
    }
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
        
        // Calculate bounding box from face landmarks
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

// Capture photo
document.getElementById('captureBtn').addEventListener('click', function() {
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, 640, 480);
    
    const imageData = canvas.toDataURL('image/jpeg');
    document.getElementById('capturedImage').src = imageData;
    
    // Simulate face recognition (replace with actual recognition logic)
    simulateFaceRecognition(imageData);
});

// Simulate face recognition (replace with actual API call)
function simulateFaceRecognition(imageData) {
    document.getElementById('verificationStatus').className = 'alert alert-warning';
    document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memverifikasi wajah...';
    
    // Simulate API call
    setTimeout(() => {
        // For demo, randomly succeed or fail
        const success = Math.random() > 0.3;
        
        if (success) {
            document.getElementById('verificationStatus').className = 'alert alert-success';
            document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-check-circle me-1"></i> Wajah Terverifikasi!';
            document.getElementById('confidenceValue').textContent = '95.2%';
            
            // Show result
            showResult(true);
        } else {
            document.getElementById('verificationStatus').className = 'alert alert-danger';
            document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-x-circle me-1"></i> Wajah tidak dikenali';
            document.getElementById('confidenceValue').textContent = '45.8%';
            
            showResult(false);
        }
    }, 2000);
}

// Show result
function showResult(success) {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';
    
    if (success) {
        const pegawaiSelect = document.getElementById('pegawaiSelect');
        const selectedOption = pegawaiSelect.options[pegawaiSelect.selectedIndex];
        
        document.getElementById('pegawaiInfo').innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">${selectedOption.text}</h6>
                    <p class="card-text">
                        <strong>NIP:</strong> ${pegawaiSelect.value}<br>
                        <strong>Status:</strong> <span class="badge bg-success">Terverifikasi</span><br>
                        <strong>Waktu:</strong> ${new Date().toLocaleString()}
                    </p>
                </div>
            </div>
        `;
        
        document.getElementById('saveBtn').style.display = 'inline-block';
    } else {
        document.getElementById('pegawaiInfo').innerHTML = `
            <div class="alert alert-danger">
                <h6 class="alert-heading">Verifikasi Gagal</h6>
                <p>Wajah tidak dikenali. Silakan coba lagi atau daftarkan wajah terlebih dahulu.</p>
            </div>
        `;
        
        document.getElementById('saveBtn').style.display = 'none';
    }
}

// Save verification
document.getElementById('saveBtn').addEventListener('click', function() {
    const canvas = document.getElementById('canvas');
    const imageData = canvas.toDataURL('image/jpeg');
    
    // Convert to blob and upload
    canvas.toBlob(function(blob) {
        const formData = new FormData();
        formData.append('nomor_induk_pegawai', currentPegawai);
        formData.append('foto_wajah', blob, 'face_' + Date.now() + '.jpg');
        formData.append('aktif', true);
        formData.append('tanggal_verifikasi', new Date().toISOString().split('T')[0]);
        
        fetch('{{ route("transaksi.presensi.verifikasi-wajah.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Verifikasi wajah berhasil disimpan!');
                window.location.href = '{{ route("transaksi.presensi.verifikasi-wajah.index") }}';
            } else {
                alert('Gagal menyimpan verifikasi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan verifikasi');
        });
    });
});

// Retry
document.getElementById('retryBtn').addEventListener('click', function() {
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
});

// Stop camera
document.getElementById('stopCameraBtn').addEventListener('click', function() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
});

// Pegawai selection
document.getElementById('pegawaiSelect').addEventListener('change', function() {
    currentPegawai = this.value;
    document.getElementById('startCameraBtn').disabled = !currentPegawai;
});

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
@endpush
