@extends('layouts.app')

@section('title', 'Kelola Foto Catalog')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-images me-2"></i>Kelola Foto Catalog
                    </h1>
                    <p class="text-muted mb-0">Kelola foto yang ditampilkan di slider catalog</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('kelola-catalog.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                    <a href="{{ route('catalog') }}" target="_blank" class="btn btn-success">
                        <i class="fas fa-external-link-alt me-1"></i>Lihat Catalog
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Upload Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-upload me-2"></i>Upload Foto Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('kelola-catalog.photos.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="foto" class="form-label">Pilih Foto <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('foto') is-invalid @enderror" 
                                       id="foto" name="foto" accept="image/*" required>
                                <div class="form-text">
                                    Format: JPG, PNG, GIF. Maksimal 2MB (sesuai server limit).
                                    <br><small class="text-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Foto besar akan otomatis dikompres untuk menghemat ruang penyimpanan.
                                    </small>
                                    <br><small class="text-muted">
                                        Server upload limit: <?php echo ini_get('upload_max_filesize'); ?> | 
                                        Post limit: <?php echo ini_get('post_max_size'); ?>
                                    </small>
                                </div>
                                @error('foto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary" id="uploadBtn">
                                    <i class="fas fa-upload me-1"></i>Upload Foto
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Foto Gallery -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2"></i>Foto Catalog Saat Ini
                    </h5>
                    <span class="badge bg-primary">{{ $catalogPhotos->count() }} Foto</span>
                </div>
                <div class="card-body">
                    @if($catalogPhotos->count() > 0)
                        <div class="row" id="photoGallery">
                            @foreach($catalogPhotos as $photo)
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4" data-photo-id="{{ $photo->id }}">
                                <div class="card h-100 shadow-sm">
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/'.$photo->foto) }}" 
                                             alt="Foto Catalog" 
                                             class="card-img-top" 
                                             style="height: 250px; object-fit: cover; cursor: pointer;"
                                             onclick="showImageModal('{{ asset('storage/'.$photo->foto) }}', 'Foto Catalog')">
                                        
                                        <!-- Action Button -->
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="deletePhoto({{ $photo->id }})"
                                                    title="Hapus Foto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Urutan Badge -->
                                        <div class="position-absolute bottom-0 start-0 p-2">
                                            <span class="badge bg-dark">
                                                #{{ $photo->urutan }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ $photo->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Info -->
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tips:</strong> Foto akan ditampilkan di slider catalog sesuai urutan upload. 
                            Klik foto untuk melihat ukuran penuh.
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-images fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum Ada Foto Catalog</h5>
                            <p class="text-muted">Upload foto pertama Anda untuk memulai membuat catalog yang menarik!</p>
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('foto').click()">
                                <i class="fas fa-upload me-1"></i>Upload Foto Pertama
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Preview Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imageModalImg" src="" alt="" class="img-fluid" style="max-height: 500px;">
            </div>
        </div>
    </div>
</div>

<style>
.card-img-top {
    transition: transform 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.position-absolute .btn {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.card:hover .position-absolute .btn {
    opacity: 1;
}

.badge {
    font-size: 0.75em;
}

#uploadForm {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.5rem;
    padding: 1rem;
}
</style>

<script>
// Upload form handling
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('foto');
    const submitBtn = document.getElementById('uploadBtn');
    const originalText = submitBtn.innerHTML;
    
    console.log('Form submit triggered');
    console.log('File input:', fileInput);
    console.log('Files:', fileInput.files);
    console.log('Files length:', fileInput.files ? fileInput.files.length : 'null');
    
    // Check if file is selected
    if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        console.log('No file selected - preventing submit');
        alert('Silakan pilih file foto terlebih dahulu.');
        return false;
    }
    
    const selectedFile = fileInput.files[0];
    console.log('Selected file:', selectedFile);
    console.log('File name:', selectedFile.name);
    console.log('File size:', selectedFile.size);
    console.log('File type:', selectedFile.type);
    
    // Check file size (2MB = 2 * 1024 * 1024 bytes)
    const maxSize = 2 * 1024 * 1024;
    if (selectedFile.size > maxSize) {
        e.preventDefault();
        alert('Ukuran file terlalu besar. Maksimal 2MB (sesuai server limit).\n\nTips: Compress foto Anda terlebih dahulu atau gunakan foto dengan resolusi lebih kecil.');
        return false;
    }
    
    // Check file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(selectedFile.type)) {
        e.preventDefault();
        alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
        return false;
    }
    
    console.log('File validation passed - submitting form');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengupload...';
    
    // Let the form submit normally, but provide visual feedback
    setTimeout(() => {
        if (!submitBtn.disabled) return; // Form already processed
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }, 30000); // Reset after 30 seconds for large files
});

// Add file input change listener for debugging
document.getElementById('foto').addEventListener('change', function(e) {
    console.log('File input changed');
    console.log('Files:', e.target.files);
    if (e.target.files && e.target.files.length > 0) {
        const file = e.target.files[0];
        console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);
    }
});

// Delete photo function
function deletePhoto(photoId) {
    if (confirm('Apakah Anda yakin ingin menghapus foto ini? Tindakan ini tidak dapat dibatalkan.')) {
        const photoCard = document.querySelector(`[data-photo-id="${photoId}"]`);
        const deleteBtn = photoCard.querySelector('.btn-danger');
        const originalContent = deleteBtn.innerHTML;
        
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;
        
        fetch(`/kelola-catalog/photos/${photoId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove photo card with animation
                photoCard.style.transition = 'all 0.3s ease';
                photoCard.style.opacity = '0';
                photoCard.style.transform = 'scale(0.8)';
                
                setTimeout(() => {
                    photoCard.remove();
                    
                    // Update badge count
                    const badge = document.querySelector('.badge.bg-primary');
                    const currentCount = parseInt(badge.textContent.split(' ')[0]);
                    badge.textContent = `${currentCount - 1} Foto`;
                    
                    // Check if no photos left
                    const remainingPhotos = document.querySelectorAll('[data-photo-id]');
                    if (remainingPhotos.length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                }, 300);
                
                showToast('Foto berhasil dihapus', 'success');
            } else {
                showToast('Gagal menghapus foto: ' + data.message, 'error');
                deleteBtn.innerHTML = originalContent;
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghapus foto', 'error');
            deleteBtn.innerHTML = originalContent;
            deleteBtn.disabled = false;
        });
    }
}

// Show image modal
function showImageModal(imageSrc, altText) {
    document.getElementById('imageModalImg').src = imageSrc;
    document.getElementById('imageModalImg').alt = altText;
    document.getElementById('imageModalTitle').textContent = altText;
    
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialize and show toast
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}
</script>
@endsection