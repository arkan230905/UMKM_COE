@extends('layouts.app')

@section('title', 'Tambah Verifikasi Wajah')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-check me-2"></i>
                        Pendaftaran Verifikasi Wajah
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Progress Steps -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="step-item active" id="step1Indicator">
                                    <div class="step-number">1</div>
                                    <div class="step-label">Pilih Pegawai</div>
                                </div>
                                <div class="step-item" id="step2Indicator">
                                    <div class="step-number">2</div>
                                    <div class="step-label">Verifikasi Wajah</div>
                                </div>
                                <div class="step-item" id="step3Indicator">
                                    <div class="step-number">3</div>
                                    <div class="step-label">Konfirmasi</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Pilih Pegawai -->
                    <div id="step1" class="step-content">
                        <h6 class="mb-3">
                            <i class="bi bi-person-badge me-2"></i>
                            Pilih Pegawai
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="pegawaiSelect" class="form-label">Pegawai</label>
                                <select name="pegawai_id" id="pegawaiSelect" class="form-select" required>
                                    <option value="">-- Pilih Pegawai --</option>
                                    @foreach($pegawais as $pegawai)
                                        <option value="{{ $pegawai->nomor_induk_pegawai }}" 
                                                data-nama="{{ $pegawai->nama }}"
                                                data-email="{{ $pegawai->email }}"
                                                data-telp="{{ $pegawai->no_telp }}"
                                                data-alamat="{{ $pegawai->alamat }}"
                                                data-jabatan="{{ $pegawai->jabatan }}"
                                                data-tanggal-masuk="{{ $pegawai->tanggal_masuk }}"
                                                data-status="{{ $pegawai->status_aktif }}">
                                            {{ $pegawai->nama }} ({{ $pegawai->nomor_induk_pegawai }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Data Pegawai Detail -->
                        <div id="pegawaiDetail" class="mt-4" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Data Pegawai</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>NIP:</strong> <span id="detailNIP">-</span></p>
                                            <p><strong>Nama:</strong> <span id="detailNama">-</span></p>
                                            <p><strong>Email:</strong> <span id="detailEmail">-</span></p>
                                            <p><strong>No. Telp:</strong> <span id="detailTelp">-</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Jabatan:</strong> <span id="detailJabatan">-</span></p>
                                            <p><strong>Alamat:</strong> <span id="detailAlamat">-</span></p>
                                            <p><strong>Tanggal Masuk:</strong> <span id="detailTanggalMasuk">-</span></p>
                                            <p><strong>Status:</strong> <span id="detailStatus" class="badge">-</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" id="nextToStep2" class="btn btn-primary" disabled>
                                Lanjut <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Verifikasi Wajah -->
                    <div id="step2" class="step-content" style="display: none;">
                        <h6 class="mb-3">
                            <i class="bi bi-camera-video me-2"></i>
                            Verifikasi Wajah
                        </h6>

                        <div class="text-center">
                            <!-- Camera Container -->
                            <div class="camera-container mb-3">
                                <video id="video" width="640" height="480" autoplay style="display: none;"></video>
                                <canvas id="canvas" width="640" height="480" style="display: none;"></canvas>
                                <div id="cameraPlaceholder" class="camera-placeholder">
                                    <i class="bi bi-camera-video" style="font-size: 4rem; color: #6c757d;"></i>
                                    <p class="mt-2">Kamera belum aktif</p>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div id="verificationProgress" class="progress mb-3" style="display: none;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: 0%" 
                                     id="progressBar">
                                    0%
                                </div>
                            </div>

                            <!-- Status Messages -->
                            <div id="verificationStatus" class="alert alert-info" style="display: none;">
                                <i class="bi bi-info-circle me-1"></i>
                                <span id="statusMessage">Memulai verifikasi...</span>
                            </div>

                            <!-- Success Animation -->
                            <div id="successAnimation" style="display: none;">
                                <div class="success-checkmark">
                                    <div class="check-icon">
                                        <span class="icon-line line-tip"></span>
                                        <span class="icon-line line-long"></span>
                                        <div class="icon-circle"></div>
                                        <div class="icon-fix"></div>
                                    </div>
                                </div>
                                <h5 class="mt-3 text-success">Verifikasi Berhasil!</h5>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" id="backToStep1" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </button>
                            <button type="button" id="startVerification" class="btn btn-primary">
                                <i class="bi bi-play-circle me-1"></i> Mulai Verifikasi
                            </button>
                            <button type="button" id="nextToStep3" class="btn btn-success" disabled>
                                Lanjut <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Konfirmasi -->
                    <div id="step3" class="step-content" style="display: none;">
                        <h6 class="mb-3">
                            <i class="bi bi-check-circle me-2"></i>
                            Konfirmasi Data
                        </h6>

                        <!-- Foto Hasil Verifikasi -->
                        <div class="text-center mb-4">
                            <h6>Foto Verifikasi Wajah</h6>
                            <img id="resultPhoto" src="" alt="Foto Verifikasi" class="img-fluid rounded" style="max-width: 400px;">
                        </div>

                        <!-- Data Pegawai Lengkap -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Data Pegawai</h6>
                                <div id="confirmPegawaiData">
                                    <!-- Will be filled by JavaScript -->
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Pastikan semua data sudah benar sebelum menyimpan verifikasi wajah.
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" id="backToStep2" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </button>
                            <button type="button" id="saveVerification" class="btn btn-success">
                                <i class="bi bi-save me-1"></i> Simpan Verifikasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.step-item:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #dee2e6;
    z-index: -1;
}

.step-item.active:not(:last-child)::after {
    background: #28a745;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #dee2e6;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
}

.step-item.active .step-number {
    background: #28a745;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.step-item.active .step-label {
    color: #28a745;
    font-weight: 600;
}

.camera-container {
    position: relative;
    display: inline-block;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.camera-placeholder {
    width: 640px;
    height: 480px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.success-checkmark {
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #28a745;
}

.check-icon::before {
    top: 3px;
    left: -2px;
    width: 30px;
    transform-origin: 100% 50%;
    border-radius: 100px 0 0 100px;
}

.check-icon::after {
    top: 0;
    left: 30px;
    width: 60px;
    transform-origin: 0 50%;
    border-radius: 0 100px 100px 0;
    animation: rotate-circle 4.25s ease-in;
}

.check-icon::before,
.check-icon::after {
    content: '';
    height: 100px;
    position: absolute;
    background: #28a745;
    transform: rotate(-45deg);
}

.icon-line {
    height: 5px;
    background-color: #28a745;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.icon-circle {
    top: -4px;
    left: -4px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    border: 4px solid #28a745;
    animation: icon-circle 1s;
}

.icon-fix {
    top: 8px;
    width: 5px;
    left: 28px;
    height: 85px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: #28a745;
}

@keyframes rotate-circle {
    0% { transform: rotate(-45deg); }
    5% { transform: rotate(-40deg); }
    10% { transform: rotate(-35deg); }
    15% { transform: rotate(-30deg); }
    20% { transform: rotate(-25deg); }
    25% { transform: rotate(-20deg); }
    30% { transform: rotate(-15deg); }
    35% { transform: rotate(-10deg); }
    40% { transform: rotate(-5deg); }
    45% { transform: rotate(0deg); }
    50% { transform: rotate(5deg); }
    55% { transform: rotate(10deg); }
    60% { transform: rotate(15deg); }
    65% { transform: rotate(20deg); }
    70% { transform: rotate(25deg); }
    75% { transform: rotate(30deg); }
    80% { transform: rotate(35deg); }
    85% { transform: rotate(40deg); }
    90% { transform: rotate(45deg); }
    95% { transform: rotate(50deg); }
    100% { transform: rotate(55deg); }
}

@keyframes icon-line-tip {
    0% { width: 0; left: 1px; top: 19px; }
    54% { width: 0; left: 1px; top: 19px; }
    70% { width: 50px; left: -8px; top: 37px; }
    84% { width: 17px; left: 21px; top: 48px; }
    100% { width: 25px; left: 14px; top: 45px; }
}

@keyframes icon-line-long {
    0% { width: 0; right: 46px; top: 54px; }
    65% { width: 0; right: 46px; top: 54px; }
    84% { width: 55px; right: 0px; top: 35px; }
    100% { width: 47px; right: 8px; top: 38px; }
}

@keyframes icon-circle {
    0% { transform: scale(0); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/face-landmarks-detection@latest"></script>
<script>
let currentPegawai = null;
let capturedImageData = null;
let stream = null;
let model = null;
let verificationInterval = null;

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
        console.log('Face detection model loaded successfully');
    } catch (error) {
        console.error('Error loading face detection model:', error);
    }
}

// Step 1: Pegawai selection
document.getElementById('pegawaiSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value) {
        currentPegawai = this.value;
        
        // Update detail pegawai
        document.getElementById('detailNIP').textContent = this.value;
        document.getElementById('detailNama').textContent = selectedOption.dataset.nama;
        document.getElementById('detailEmail').textContent = selectedOption.dataset.email;
        document.getElementById('detailTelp').textContent = selectedOption.dataset.telp;
        document.getElementById('detailAlamat').textContent = selectedOption.dataset.alamat;
        document.getElementById('detailJabatan').textContent = selectedOption.dataset.jabatan;
        document.getElementById('detailTanggalMasuk').textContent = selectedOption.dataset.tanggalMasuk;
        
        // Update status badge
        const statusBadge = document.getElementById('detailStatus');
        const status = selectedOption.dataset.status;
        statusBadge.textContent = status == '1' ? 'Aktif' : 'Tidak Aktif';
        statusBadge.className = 'badge ' + (status == '1' ? 'bg-success' : 'bg-danger');
        
        // Show detail and enable next button
        document.getElementById('pegawaiDetail').style.display = 'block';
        document.getElementById('nextToStep2').disabled = false;
    } else {
        currentPegawai = null;
        document.getElementById('pegawaiDetail').style.display = 'none';
        document.getElementById('nextToStep2').disabled = true;
    }
});

// Navigation
document.getElementById('nextToStep2').addEventListener('click', function() {
    if (currentPegawai) {
        showStep(2);
        updateStepIndicator(2);
    }
});

document.getElementById('backToStep1').addEventListener('click', function() {
    showStep(1);
    updateStepIndicator(1);
    
    // Stop camera if running
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    
    // Reset verification
    clearInterval(verificationInterval);
    resetVerificationUI();
});

document.getElementById('backToStep2').addEventListener('click', function() {
    showStep(2);
    updateStepIndicator(2);
});

// Step 2: Verifikasi Wajah
document.getElementById('startVerification').addEventListener('click', async function() {
    try {
        // Start camera
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: 640, 
                height: 480,
                facingMode: 'user'
            } 
        });
        
        const video = document.getElementById('video');
        video.srcObject = stream;
        video.style.display = 'block';
        document.getElementById('cameraPlaceholder').style.display = 'none';
        
        // Show progress and status
        document.getElementById('verificationProgress').style.display = 'block';
        document.getElementById('verificationStatus').style.display = 'block';
        document.getElementById('startVerification').disabled = true;
        
        // Start verification process
        startVerificationProcess();
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Tidak dapat mengakses kamera. Pastikan kamera sudah diizinkan.');
    }
});

function startVerificationProcess() {
    let progress = 0;
    const progressBar = document.getElementById('progressBar');
    const statusMessage = document.getElementById('statusMessage');
    
    verificationInterval = setInterval(async function() {
        progress += Math.random() * 15; // Random progress increment
        if (progress > 100) progress = 100;
        
        progressBar.style.width = progress + '%';
        progressBar.textContent = Math.round(progress) + '%';
        
        // Update status message
        if (progress < 30) {
            statusMessage.textContent = 'Mendeteksi wajah...';
        } else if (progress < 60) {
            statusMessage.textContent = 'Menganalisis fitur wajah...';
        } else if (progress < 90) {
            statusMessage.textContent = 'Memverifikasi identitas...';
        } else {
            statusMessage.textContent = 'Verifikasi hampir selesai...';
        }
        
        // Capture photo when complete
        if (progress >= 100) {
            clearInterval(verificationInterval);
            await captureVerificationPhoto();
        }
    }, 500);
}

async function captureVerificationPhoto() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    
    // Set canvas size to match video dimensions
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    // Draw video frame to canvas
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Get high-quality image data
    capturedImageData = canvas.toDataURL('image/jpeg', 0.95);
    
    console.log('Photo captured with dimensions:', canvas.width + 'x' + canvas.height);
    console.log('Image data size:', capturedImageData.length);
    
    // Stop camera
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    
    // Show success animation
    showSuccessAnimation();
}

function showSuccessAnimation() {
    // Hide progress and status
    document.getElementById('verificationProgress').style.display = 'none';
    document.getElementById('verificationStatus').style.display = 'none';
    document.getElementById('video').style.display = 'none';
    
    // Show success animation
    document.getElementById('successAnimation').style.display = 'block';
    
    // Enable next button after animation
    setTimeout(function() {
        document.getElementById('nextToStep3').disabled = false;
    }, 2000);
}

function resetVerificationUI() {
    document.getElementById('verificationProgress').style.display = 'none';
    document.getElementById('verificationStatus').style.display = 'none';
    document.getElementById('successAnimation').style.display = 'none';
    document.getElementById('video').style.display = 'none';
    document.getElementById('cameraPlaceholder').style.display = 'flex';
    document.getElementById('startVerification').disabled = false;
    document.getElementById('nextToStep3').disabled = true;
    
    // Reset progress bar
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';
}

document.getElementById('nextToStep3').addEventListener('click', function() {
    if (capturedImageData && currentPegawai) {
        showStep(3);
        updateStepIndicator(3);
        showConfirmationData();
    }
});

// Step 3: Confirmation
function showConfirmationData() {
    // Show captured photo
    document.getElementById('resultPhoto').src = capturedImageData;
    
    // Get pegawai data
    const select = document.getElementById('pegawaiSelect');
    const selectedOption = select.options[select.selectedIndex];
    
    // Extract NIP from option text
    const optionText = selectedOption.text;
    const nipMatch = optionText.match(/\(([^)]+)\)/);
    const nip = nipMatch ? nipMatch[1] : currentPegawai;
    const name = optionText.replace(/\s*\([^)]*\)/, '');
    
    // Display pegawai data
    document.getElementById('confirmPegawaiData').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>NIP:</strong> ${nip}</p>
                <p><strong>Nama:</strong> ${name}</p>
                <p><strong>Email:</strong> ${selectedOption.dataset.email}</p>
                <p><strong>No. Telp:</strong> ${selectedOption.dataset.telp}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Jabatan:</strong> ${selectedOption.dataset.jabatan}</p>
                <p><strong>Alamat:</strong> ${selectedOption.dataset.alamat}</p>
                <p><strong>Tanggal Masuk:</strong> ${selectedOption.dataset.tanggalMasuk}</p>
                <p><strong>Status:</strong> <span class="badge ${selectedOption.dataset.status == '1' ? 'bg-success' : 'bg-danger'}">${selectedOption.dataset.status == '1' ? 'Aktif' : 'Tidak Aktif'}</span></p>
            </div>
        </div>
    `;
}

document.getElementById('saveVerification').addEventListener('click', function() {
    if (capturedImageData && currentPegawai) {
        // Show loading state
        const saveBtn = document.getElementById('saveVerification');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menyimpan...';
        saveBtn.disabled = true;
        
        console.log('=== DEBUG SAVE VERIFICATION ===');
        console.log('Current Pegawai:', currentPegawai);
        console.log('Captured Image Data Length:', capturedImageData.length);
        console.log('CSRF Token:', '{{ csrf_token() }}');
        
        // Convert data URL to blob
        fetch(capturedImageData)
            .then(res => {
                console.log('Fetch response status:', res.status);
                return res.blob();
            })
            .then(blob => {
                console.log('Blob created, size:', blob.size);
                console.log('Blob type:', blob.type);
                
                const formData = new FormData();
                formData.append('nomor_induk_pegawai', currentPegawai);
                formData.append('foto_wajah', blob, 'face_' + Date.now() + '.jpg');
                formData.append('aktif', 1); // Send as integer 1 instead of boolean true
                formData.append('tanggal_verifikasi', new Date().toISOString().split('T')[0]);
                
                console.log('FormData created, entries:');
                for (let [key, value] of formData.entries()) {
                    console.log(`- ${key}:`, value);
                }
                
                const url = '{{ route("transaksi.presensi.verifikasi-wajah.store") }}';
                console.log('Sending to URL:', url);
                
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Fetch response status:', response.status);
                    console.log('Fetch response headers:', response.headers);
                    
                    // Check if response is actually JSON
                    const contentType = response.headers.get('content-type');
                    console.log('Response content type:', contentType);
                    
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If not JSON, get text and show error
                        return response.text().then(text => {
                            console.log('Response text:', text);
                            throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                        });
                    }
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        alert('Verifikasi wajah berhasil disimpan!');
                        window.location.href = '{{ route("transaksi.presensi.verifikasi-wajah.index") }}';
                    } else {
                        console.error('Server returned error:', data);
                        alert('Gagal menyimpan verifikasi: ' + data.message);
                        saveBtn.innerHTML = originalText;
                        saveBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    console.error('Fetch error stack:', error.stack);
                    alert('Terjadi kesalahan saat menyimpan verifikasi: ' + error.message);
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                });
            })
            .catch(error => {
                console.error('Error converting image:', error);
                console.error('Image conversion stack:', error.stack);
                alert('Terjadi kesalahan saat memproses foto: ' + error.message);
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
    } else {
        console.log('No captured image or pegawai selected');
        alert('Silakan lengkapi proses verifikasi terlebih dahulu');
    }
});

// Step navigation functions
function showStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(step => {
        step.style.display = 'none';
    });
    
    // Show current step
    document.getElementById('step' + stepNumber).style.display = 'block';
}

function updateStepIndicator(stepNumber) {
    document.querySelectorAll('.step-item').forEach((item, index) => {
        if (index < stepNumber) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
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
@endpush
