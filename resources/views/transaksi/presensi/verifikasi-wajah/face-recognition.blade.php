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
                    <!-- Status Data Wajah -->
                    @if(!$hasFaceData)
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Pegawai "{{ $pegawai->nama }}" belum terdaftar verifikasi wajah.
                            Silakan daftarkan wajah terlebih dahulu melalui menu pendaftaran verifikasi wajah.
                            <br><small>
                                <a href="{{ route('transaksi.presensi.verifikasi-wajah.create') }}" class="btn btn-sm btn-warning mt-2">
                                    <i class="bi bi-plus-circle me-1"></i> Daftarkan Wajah Sekarang
                                </a>
                            </small>
                        </div>
                    @else
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Status:</strong> Pegawai "{{ $pegawai->nama }}" sudah terdaftar verifikasi wajah.
                            Silakan lanjutkan proses verifikasi.
                        </div>
                    @endif

                    <!-- Step 1: Pilih Pegawai -->
                    <div id="step1" class="step-section">
                        <h6 class="mb-3">Langkah 1: Pilih Pegawai</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <select id="pegawaiSelect" class="form-select">
                                    <option value="">Pilih Pegawai</option>
                                    @if(isset($pegawai))
                                        <option value="{{ $pegawai->kode_pegawai }}" selected>
                                            {{ $pegawai->nama }} ({{ $pegawai->kode_pegawai }})
                                        </option>
                                    @endif
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
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest?v={{ time() }}"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@latest?v={{ time() }}"></script>
<script>
// Version: {{ time() }} - Cache busting
console.log('=== FACE RECOGNITION SCRIPT LOADED ===');
console.log('Timestamp: {{ time() }}');
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

// Extract face encoding from canvas (simplified version)
function extractFaceEncoding() {
    try {
        const ctx = canvas.getContext('2d');
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;
        
        // Simplified face encoding: sample key areas and create smaller feature vector
        const encoding = [];
        const sampleRate = 100; // Sample every 100th pixel (reduced from 10)
        
        // Focus on center area (where face usually is)
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = Math.min(canvas.width, canvas.height) / 4;
        
        for (let angle = 0; angle < 360; angle += 30) { // 12 points around circle
            const x = Math.round(centerX + radius * Math.cos(angle * Math.PI / 180));
            const y = Math.round(centerY + radius * Math.sin(angle * Math.PI / 180));
            
            if (x >= 0 && x < canvas.width && y >= 0 && y < canvas.height) {
                const index = (y * canvas.width + x) * 4;
                // Convert RGB to grayscale
                const gray = 0.299 * data[index] + 0.587 * data[index + 1] + 0.114 * data[index + 2];
                encoding.push(Math.round(gray * 100) / 100); // Round to 2 decimal places
            }
        }
        
        console.log('Face encoding extracted:', {
            length: encoding.length,
            sample: encoding.slice(0, 5),
            method: 'circular_sampling'
        });
        
        return encoding;
    } catch (error) {
        console.error('Error extracting face encoding:', error);
        return [];
    }
}

// Simulate face recognition (replace with actual API call)
function simulateFaceRecognition(imageData) {
    console.log('=== START FACE RECOGNITION ===');
    console.log('Current pegawai:', currentPegawai);
    
    if (!currentPegawai) {
        console.error('ERROR: No pegawai selected!');
        document.getElementById('verificationStatus').className = 'alert alert-danger';
        document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Error: Tidak ada pegawai yang dipilih';
        return;
    }
    
    document.getElementById('verificationStatus').className = 'alert alert-warning';
    document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Memverifikasi wajah...';
    
    // Extract face encoding from canvas (simplified)
    console.log('Extracting face encoding...');
    const faceEncoding = extractFaceEncoding();
    console.log('Face encoding result:', {
        length: faceEncoding.length,
        isEmpty: faceEncoding.length === 0,
        sample: faceEncoding.slice(0, 3)
    });
    
    // Convert canvas to blob for API call
    canvas.toBlob(function(blob) {
        const formData = new FormData();
        formData.append('foto_wajah', blob, 'face_' + Date.now() + '.jpg');
        formData.append('kode_pegawai', currentPegawai);
        formData.append('encoding_wajah', JSON.stringify(faceEncoding)); // Kirim encoding
        
        // Debug log
        console.log('Sending API request:', {
            kode_pegawai: currentPegawai,
            hasPhoto: !!blob,
            hasEncoding: faceEncoding.length > 0,
            encodingLength: faceEncoding.length,
            encodingString: JSON.stringify(faceEncoding).substring(0, 100) + '...'
        });
        
        if (!currentPegawai) {
            console.error('No pegawai selected!');
            document.getElementById('verificationStatus').className = 'alert alert-danger';
            document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Error: Tidak ada pegawai yang dipilih';
            return;
        }
        
        fetch('{{ route("transaksi.presensi.verifikasi-wajah.api.recognize") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log('API response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API response data:', data);
            
            if (data.success) {
                if (data.recognized) {
                    console.log('✅ Face RECOGNIZED:', data.pegawai.nama);
                    console.log('Action:', data.action);
                    console.log('Is New Registration:', data.is_new_registration);
                    
                    if (data.is_new_registration) {
                        // NEW REGISTRATION SUCCESS
                        document.getElementById('verificationStatus').className = 'alert alert-info';
                        document.getElementById('verificationStatus').innerHTML = `
                            <i class="bi bi-person-plus me-1"></i> 
                            <strong>Pendaftaran Wajah Berhasil!</strong><br>
                            <small>Pegawai: ${data.pegawai.nama}<br>
                            Wajah telah terdaftar di sistem</small>
                        `;
                    } else {
                        // VERIFICATION SUCCESS
                        document.getElementById('verificationStatus').className = 'alert alert-success';
                        document.getElementById('verificationStatus').innerHTML = `
                            <i class="bi bi-check-circle me-1"></i> 
                            <strong>Verifikasi Berhasil!</strong><br>
                            <small>Pegawai: ${data.pegawai.nama}<br>
                            Confidence: ${data.confidence}%</small>
                        `;
                    }
                    
                    document.getElementById('saveBtn').style.display = 'block';
                    document.getElementById('retryBtn').style.display = 'none';
                    
                    // Update button text based on action
                    const saveBtn = document.getElementById('saveBtn');
                    if (data.is_new_registration) {
                        saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Konfirmasi Pendaftaran';
                    } else {
                        saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Konfirmasi Presensi';
                    }
                    
                } else {
                    console.log('❌ Face NOT MATCHED');
                    document.getElementById('verificationStatus').className = 'alert alert-warning';
                    document.getElementById('verificationStatus').innerHTML = `
                        <i class="bi bi-x-circle me-1"></i> 
                        <strong>Verifikasi Gagal</strong><br>
                        <small>Wajah tidak cocok dengan data yang terdaftar.<br>
                        Confidence: ${data.confidence}%<br>
                        Silakan coba lagi atau gunakan wajah yang sama</small>
                    `;
                    document.getElementById('saveBtn').style.display = 'none';
                    document.getElementById('retryBtn').style.display = 'block';
                }
            } else {
                console.log('❌ API ERROR:', data.message);
                document.getElementById('verificationStatus').className = 'alert alert-danger';
                document.getElementById('verificationStatus').innerHTML = `
                    <i class="bi bi-exclamation-triangle me-1"></i> 
                    <strong>Error:</strong> ${data.message}
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('verificationStatus').className = 'alert alert-danger';
            document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-x-circle me-1"></i> Terjadi kesalahan saat verifikasi';
            showResult(false);
        });
    });
}

// Show result
function showResult(success, pegawaiData = null, status = null) {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';
    
    if (success && pegawaiData) {
        // SUCCESS: Verification successful
        document.getElementById('pegawaiInfo').innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Verifikasi Berhasil!</strong>
            </div>
            <p><strong>Nama:</strong> ${pegawaiData.nama}</p>
            <p><strong>Kode:</strong> ${pegawaiData.kode_pegawai}</p>
            <p><strong>Email:</strong> ${pegawaiData.email}</p>
            <p><strong>Jabatan:</strong> ${pegawaiData.jabatan}</p>
            <div class="mt-3">
                <button class="btn btn-success" onclick="proceedToAttendance()">
                    <i class="bi bi-arrow-right-circle me-1"></i> Lanjut ke Presensi
                </button>
                <button class="btn btn-secondary ms-2" onclick="retryVerification()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Verifikasi Ulang
                </button>
            </div>
        `;
        document.getElementById('saveBtn').style.display = 'none';
    } else if (status === 'retry') {
        // FAILURE: Face not matched, but data exists
        document.getElementById('pegawaiInfo').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-x-circle me-2"></i>
                <strong>Verifikasi Gagal</strong><br>
                Wajah tidak cocok dengan data yang terdaftar. Silakan coba lagi dengan pencahayaan yang baik dan pastikan wajah terlihat jelas.
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" onclick="retryVerification()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                </button>
                <button class="btn btn-secondary ms-2" onclick="backToCamera()">
                    <i class="bi bi-camera me-1"></i> Foto Ulang
                </button>
            </div>
        `;
        document.getElementById('saveBtn').style.display = 'none';
    } else if (status === 'register') {
        // NO DATA: No face data found
        document.getElementById('pegawaiInfo').innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Belum Terdaftar</strong><br>
                Pegawai ini belum terdaftar verifikasi wajah. Silakan daftarkan wajah terlebih dahulu.
            </div>
            <div class="mt-3">
                <a href="{{ route('transaksi.presensi.verifikasi-wajah.create') }}" class="btn btn-warning">
                    <i class="bi bi-plus-circle me-1"></i> Daftarkan Wajah Sekarang
                </a>
                <button class="btn btn-secondary ms-2" onclick="backToSelection()">
                    <i class="bi bi-arrow-left me-1"></i> Pilih Pegawai Lain
                </button>
            </div>
        `;
        document.getElementById('saveBtn').style.display = 'none';
    } else {
        // DEFAULT FAILURE
        document.getElementById('pegawaiInfo').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-x-circle me-2"></i>
                <strong>Terjadi Kesalahan</strong><br>
                Silakan coba lagi atau hubungi administrator.
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" onclick="retryVerification()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                </button>
            </div>
        `;
        document.getElementById('saveBtn').style.display = 'none';
    }
}

// Helper functions for navigation
function retryVerification() {
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    document.getElementById('verificationStatus').className = 'alert alert-info';
    document.getElementById('verificationStatus').innerHTML = '<i class="bi bi-info-circle me-1"></i> Siap untuk verifikasi';
    document.getElementById('confidenceValue').textContent = '-';
}

function backToCamera() {
    retryVerification();
}

function backToSelection() {
    window.location.href = '{{ route("transaksi.presensi.verifikasi-wajah.create") }}';
}

function proceedToAttendance() {
    // Redirect to attendance page or save attendance
    window.location.href = '{{ route("transaksi.presensi.index") }}';
}

// Save verification (Step 3)
document.getElementById('saveBtn').addEventListener('click', function() {
    console.log('=== STEP 3: SAVE VERIFICATION START ===');
    
    const canvas = document.getElementById('canvas');
    const imageData = canvas.toDataURL('image/jpeg');
    
    // Validate pegawai selection
    if (!currentPegawai) {
        alert('Error: Tidak ada pegawai yang dipilih. Silakan pilih pegawai terlebih dahulu.');
        return;
    }
    
    console.log('STEP 3: Saving verification for pegawai:', currentPegawai);
    
    // Convert to blob and upload
    canvas.toBlob(function(blob) {
        // Extract face encoding for storage
        const faceEncoding = extractFaceEncoding();
        
        const formData = new FormData();
        formData.append('kode_pegawai', currentPegawai);
        formData.append('foto_wajah', blob, 'face_' + Date.now() + '.jpg');
        formData.append('encoding_wajah', JSON.stringify(faceEncoding)); // Simpan encoding
        formData.append('aktif', true);
        formData.append('tanggal_verifikasi', new Date().toISOString().split('T')[0]);
        
        console.log('STEP 3: Sending save request with data:', {
            kode_pegawai: currentPegawai,
            hasPhoto: !!blob,
            hasEncoding: faceEncoding.length > 0,
            encodingLength: faceEncoding.length
        });
        
        // Show loading
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyimpan...';
        
        fetch('{{ route("transaksi.presensi.verifikasi-wajah.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log('STEP 3: Save response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('STEP 3: Save response data:', data);
            
            // Reset button
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-check-circle me-1"></i> Konfirmasi & Simpan';
            
            if (data.success) {
                console.log('✅ STEP 3: SAVE SUCCESS');
                
                // Show success message
                document.getElementById('verificationStatus').className = 'alert alert-success';
                document.getElementById('verificationStatus').innerHTML = `
                    <i class="bi bi-check-circle me-1"></i> 
                    <strong>Data Berhasil Disimpan!</strong><br>
                    <small>Verifikasi wajah telah tersimpan di sistem</small>
                `;
                
                // Redirect to index after 2 seconds
                setTimeout(() => {
                    console.log('STEP 3: Redirecting to index...');
                    window.location.href = '{{ route("transaksi.presensi.verifikasi-wajah.index") }}';
                }, 2000);
                
            } else {
                console.log('❌ STEP 3: SAVE ERROR:', data.message);
                document.getElementById('verificationStatus').className = 'alert alert-danger';
                document.getElementById('verificationStatus').innerHTML = `
                    <i class="bi bi-exclamation-triangle me-1"></i> 
                    <strong>Gagal Menyimpan:</strong><br>
                    <small>${data.message}</small>
                `;
            }
        })
        .catch(error => {
            console.error('❌ STEP 3: SAVE ERROR:', error);
            
            // Reset button
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-check-circle me-1"></i> Konfirmasi & Simpan';
            
            document.getElementById('verificationStatus').className = 'alert alert-danger';
            document.getElementById('verificationStatus').innerHTML = `
                <i class="bi bi-exclamation-triangle me-1"></i> 
                <strong>Terjadi kesalahan saat menyimpan</strong><br>
                <small>${error.message}</small>
            `;
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
    
    // Debug log
    console.log('Pegawai selected:', currentPegawai);
});

// Initialize on load
window.addEventListener('load', function() {
    initializeModel();
    
    // Set current pegawai from session data
    @if(isset($pegawai))
        currentPegawai = '{{ $pegawai->kode_pegawai }}';
        document.getElementById('startCameraBtn').disabled = false;
        console.log('Session pegawai loaded:', currentPegawai);
        
        // Set select dropdown value
        const select = document.getElementById('pegawaiSelect');
        if (select) {
            select.value = currentPegawai;
        }
    @else
        console.log('No session pegawai found');
        
        // Try to get from URL parameter as fallback
        const urlParams = new URLSearchParams(window.location.search);
        const kodePegawai = urlParams.get('kode_pegawai');
        if (kodePegawai) {
            currentPegawai = kodePegawai;
            document.getElementById('startCameraBtn').disabled = false;
            console.log('URL pegawai loaded:', currentPegawai);
            
            // Set select dropdown value
            const select = document.getElementById('pegawaiSelect');
            if (select) {
                select.value = currentPegawai;
            }
        }
    @endif
});

// Cleanup on unload
window.addEventListener('beforeunload', function() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endpush
