@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tambah Produk</h1>
        <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('master-data.produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="hpp" id="hpp" value="0">
                <input type="hidden" name="margin_percent" id="margin_percent" value="0">
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" name="nama_produk" id="nama_produk" 
                           class="form-control" value="{{ old('nama_produk') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Barcode</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" class="form-control" value="Auto-generate (EAN-13)" disabled readonly>
                    </div>
                    <small class="form-text text-muted">Barcode akan dibuat otomatis saat produk disimpan dengan format EAN-13.</small>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" 
                              class="form-control">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label">Foto Produk</label>
                    <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)">
                    <small class="form-text text-muted">Format: JPG, JPEG, PNG. Maksimal 10MB.</small>
                    
                    <div id="preview-container" class="mt-3" style="display: none;">
                        <p class="small mb-2 text-muted">Preview foto:</p>
                        <div class="preview-image-wrapper">
                            <img id="preview-image" src="" alt="Preview" class="preview-img">
                            <button type="button" class="btn-remove-preview" onclick="removePreview()" title="Hapus foto">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="harga_jual" class="form-label">Harga Jual</label>
                    <input type="text" name="harga_jual" id="harga_jual" 
                           class="form-control" value="0" readonly>
                    <small class="form-text text-muted">HPP belum tersedia, harga jual akan otomatis muncul setelah HPP dihitung dan bisa diubah di bagian edit.</small>
                    <small class="form-text text-muted">Presentase keuntungan: <span id="profit_percentage">0</span>%</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    
    if (file) {
        // Validasi ukuran file (max 10MB)
        if (file.size > 10485760) {
            alert('Ukuran file terlalu besar! Maksimal 10MB.');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Validasi tipe file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
            event.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
    }
}

function removePreview() {
    const fileInput = document.getElementById('foto');
    const previewContainer = document.getElementById('preview-container');
    
    fileInput.value = '';
    previewContainer.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="store"]');
    
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat fa-spin me-1"></i> Menyimpan...';
            }
        });
    }
    
    // For new products, harga_jual is readonly and always 0
    // No need for input formatting or event listeners
    const profitPercentageSpan = document.getElementById('profit_percentage');
    if (profitPercentageSpan) {
        profitPercentageSpan.textContent = '0';
    }
});
</script>
@endpush

<style>
    /* Form text color improvements */
    .form-control {
        color: #212529 !important;
        background-color: #ffffff !important;
        border: 1px solid #ced4da;
    }
    
    .form-control:focus {
        color: #212529 !important;
        background-color: #ffffff !important;
        border-color: #8B7355 !important;
        box-shadow: 0 0 0 0.2rem rgba(139, 115, 85, 0.25) !important;
    }
    
    .form-label {
        color: #212529 !important;
        font-weight: 600;
    }
    
    .form-text {
        color: #6c757d !important;
    }
    
    .container {
        color: #212529 !important;
    }
    
    h1 {
        color: #212529 !important;
    }
    
    .card {
        background-color: #ffffff !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .text-muted {
        color: #6c757d !important;
    }
    
    option {
        background-color: #ffffff;
        color: #212529;
    }
    
    /* Preview Image Styling */
    .preview-image-wrapper {
        position: relative;
        display: inline-block;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .preview-img {
        max-height: 250px;
        max-width: 250px;
        width: auto;
        height: auto;
        object-fit: cover;
        display: block;
        border-radius: 8px;
    }
    
    .btn-remove-preview {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .btn-remove-preview:hover {
        background: rgba(220, 53, 69, 1);
        transform: scale(1.1);
    }
    
    .btn-remove-preview i {
        font-size: 14px;
    }
</style>
@endsection
