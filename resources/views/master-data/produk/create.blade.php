@extends('layouts.app')

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
                    <small style="color: #ffffff;">Barcode akan dibuat otomatis saat produk disimpan dengan format EAN-13.</small>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" 
                              class="form-control">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label">Foto Produk</label>
                    <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)">
                    <small style="color: #000000;">Format: JPG, JPEG, PNG. Maksimal 10MB.</small>
                    
                    <div id="preview-container" class="mt-3" style="display: none;">
                        <p style="color: #ffffff;" class="small mb-2">Preview foto:</p>
                        <div class="preview-image-wrapper">
                            <img id="preview-image" src="" alt="Preview" class="preview-img">
                            <button type="button" class="btn-remove-preview" onclick="removePreview()" title="Hapus foto">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="margin_percent" class="form-label">Presentase Keuntungan (%)</label>
                    <input type="number" step="0.01" name="margin_percent" 
                           class="form-control" value="{{ old('margin_percent', 30) }}">
                    <small style="color: #ffffff;">Harga jual dihitung otomatis dari Harga Pokok Produksi × (1 + Margin%).</small>
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
    const form = document.getElementById('produkForm');
    
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat fa-spin me-1"></i> Menyimpan...';
            }
        });
    }
    
    const marginInput = document.querySelector('input[name="margin_percent"]');
    
    // Contoh fungsi untuk menghitung harga jual
    function hitungHargaJual() {
        // Logika perhitungan harga jual bisa ditambahkan di sini
        // Misalnya: harga_jual = harga_bom * (1 + (margin_percent / 100))
    }
    
    // Panggil fungsi saat nilai margin berubah
    if (marginInput) {
        marginInput.addEventListener('change', hitungHargaJual);
        marginInput.addEventListener('keyup', hitungHargaJual);
    }
});
</script>
@endpush

<style>
    .form-control, .form-select, .form-control:focus, .form-select:focus {
        background-color: #1e1e2f !important;
        border-color: #2d2d3a !important;
        color: #ffffff !important;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25) !important;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    option {
        background-color: #1e1e2f;
        color: #ffffff;
    }
    
    .card {
        background-color: #222232;
        border: 1px solid #2d2d3a;
    }
    
    .text-muted {
        color: #8a8a9a !important;
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
