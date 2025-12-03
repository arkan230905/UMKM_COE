@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Produk</h1>

    <form action="{{ route('master-data.produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data" id="editProdukForm">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="{{ $produk->nama_produk }}" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3">{{ $produk->deskripsi }}</textarea>
        </div>
        <div class="mb-3">
            <label for="foto" class="form-label">Foto Produk</label>
            @if($produk->foto)
                <div class="mb-3">
                    <p style="color: #ffffff;" class="small mb-2">Foto saat ini:</p>
                    <div class="current-image-wrapper">
                        <img src="{{ Storage::url($produk->foto) }}" alt="Foto Produk" class="current-img">
                    </div>
                </div>
            @endif
            <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)">
            <small style="color: #ffffff;">Format: JPG, JPEG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah foto.</small>
            
            <div id="preview-container" class="mt-3" style="display: none;">
                <p style="color: #ffffff;" class="small mb-2">Preview foto baru:</p>
                <div class="preview-image-wrapper">
                    <img id="preview-image" src="" alt="Preview" class="preview-img">
                    <button type="button" class="btn-remove-preview" onclick="removePreview()" title="Hapus foto baru">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Presentase Keuntungan (%)</label>
            <input type="number" step="0.01" name="margin_percent" class="form-control" value="{{ old('margin_percent', $produk->margin_percent) }}">
            <small style="color: #ffffff;">Harga jual dihitung otomatis dari Harga BOM × (1 + Margin%).</small>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

@push('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    
    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2048000) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
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
</script>
@endpush

@push('styles')
<style>
    /* Current Image Styling */
    .current-image-wrapper {
        display: inline-block;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border: 2px solid #dee2e6;
    }
    
    .current-img {
        max-height: 250px;
        max-width: 250px;
        width: auto;
        height: auto;
        object-fit: cover;
        display: block;
    }
    
    /* Preview Image Styling */
    .preview-image-wrapper {
        position: relative;
        display: inline-block;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        border: 2px solid #0d6efd;
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
@endpush
@endsection
