@extends('layouts.catalog')

@section('title', 'Kelola Catalog')

@section('content')
<!-- Edit Mode Indicator -->
<div class="edit-mode-indicator">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-edit me-2"></i>
                <span><strong>Mode Edit Catalog</strong> - Edit langsung setiap elemen</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('catalog') }}" target="_blank" class="btn btn-success btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>Lihat Publik
                </a>
                <a href="{{ route('kelola-catalog.photos') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-images me-1"></i>Kelola Foto
                </a>
                <a href="{{ route('kelola-catalog.settings') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-cog me-1"></i>Pengaturan
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- ================= HERO SLIDER ================= -->
<section class="hero-slider editable-section" data-section="hero">
    <div class="edit-fab" onclick="openPhotoModal()">
        <i class="fas fa-images"></i>
    </div>
    
    <div class="slider-container-full">
        <div class="slider-wrapper">
            @forelse($catalogPhotos as $index => $photo)
            <div class="slide {{ $index == 0 ? 'active' : '' }}">
                <img src="{{ asset('storage/'.$photo->foto) }}" alt="{{ $photo->judul ?: 'Foto Catalog' }}">
                @if($photo->judul || $photo->deskripsi)
                <div class="slide-overlay">
                    <div class="slide-content">
                        @if($photo->judul)
                        <h2>{{ $photo->judul }}</h2>
                        @endif
                        @if($photo->deskripsi)
                        <p>{{ $photo->deskripsi }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @empty
            <!-- Fallback to product photos if no catalog photos -->
            @forelse($produks->take(3) as $index => $produk)
            <div class="slide {{ $index == 0 ? 'active' : '' }}">
                @if($produk->foto)
                    <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama_produk }}">
                @else
                    <img src="/images/no-image.png" alt="{{ $produk->nama_produk }}">
                @endif
            </div>
            @empty
            <!-- Default fallback images -->
            <div class="slide active">
                <img src="/images/umkm-default-1.jpg" alt="UMKM Produk">
            </div>
            <div class="slide">
                <img src="/images/umkm-default-2.jpg" alt="UMKM Produk">
            </div>
            <div class="slide">
                <img src="/images/umkm-default-3.jpg" alt="UMKM Produk">
            </div>
            @endforelse
            @endforelse
        </div>
        
        <!-- Slider Controls -->
        <button class="slider-btn prev-btn" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-btn next-btn" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <!-- Slider Indicators -->
        <div class="slider-indicators">
            @php
                $totalSlides = $catalogPhotos->count() > 0 ? $catalogPhotos->count() : max($produks->count(), 3);
            @endphp
            @for($i = 0; $i < $totalSlides; $i++)
            <span class="indicator {{ $i == 0 ? 'active' : '' }}" onclick="goToSlide({{ $i }})"></span>
            @endfor
        </div>
    </div>
</section>

<!-- ================= TENTANG PERUSAHAAN ================= -->
@if($company)
<section class="section-white editable-section" data-section="company">
    <div class="edit-fab" onclick="editCompanyInfo()">
        <i class="fas fa-building"></i>
    </div>
    
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12">
                <div class="row g-4 align-items-center">
                    <div class="col-md-6">
                        @if($company->foto)
                            <img src="{{ asset('storage/'.$company->foto) }}" alt="Logo {{ $company->nama }}" class="img-fluid rounded-3 shadow">
                        @else
                            <img src="/images/company-default.jpg" alt="Logo {{ $company->nama }}" class="img-fluid rounded-3 shadow">
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="desa-content">
                            <h1 class="section-title mb-4">Tentang {{ $company->nama }}</h1>
                            <div class="editable-content" contenteditable="true" id="companyDescription">
                                @if($company->catalog_description)
                                    {!! nl2br(e($company->catalog_description)) !!}
                                @else
                                    <p>{{ $company->nama }} adalah sebuah UMKM yang bergerak di bidang {{ $company->jenis_usaha ?? 'produksi makanan' }}, terletak di {{ $company->alamat }}. Perusahaan ini berkomitmen untuk menyediakan produk berkualitas tinggi dengan bahan baku pilihan dan proses produksi yang higienis.</p>
                                    <p>Dengan pengalaman dalam industri {{ $company->jenis_usaha ?? 'makanan' }}, {{ $company->nama }} terus berinovasi untuk menghadirkan produk terbaik bagi konsumen. Kami menjunjung tinggi nilai-nilai kualitas, kebersihan, dan kepuasan pelanggan dalam setiap produk yang kami hasilkan.</p>
                                @endif
                                <p><strong>Kontak:</strong><br>
                                <i class="fas fa-envelope me-1"></i>{{ $company->email }}<br>
                                <i class="fas fa-phone me-1"></i>{{ $company->telepon }}<br>
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $company->alamat }}</p>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-primary btn-sm" onclick="saveCompanyDescription()">
                                    <i class="fas fa-save me-1"></i>Simpan Deskripsi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= PETA LOKASI ================= -->
@if($company)
<section class="section-white editable-section" data-section="maps">
    <div class="edit-fab" onclick="editMaps()">
        <i class="fas fa-map-marker-alt"></i>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title mb-4 text-center">Lokasi {{ $company->nama }}</h2>
                <div class="map-container">
                    <div class="map-wrapper">
                        @if($company->maps_link)
                        <!-- Use custom maps link if provided -->
                        <iframe 
                            src="{{ $company->maps_link }}"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        @elseif($company->latitude && $company->longitude)
                        <!-- Use coordinates if available -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!2d{{ $company->longitude }}!3d{{ $company->latitude }}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sen!2sid!4v1234567890123"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        @else
                        <!-- Fallback to address search -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!2d107.9234567890123!3d-6.8234567890123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1sen!2sid!4v1234567890123"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        @endif
                    </div>
                    <div class="map-info mt-3">
                        <p class="text-center mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <strong>{{ $company->nama }}</strong>, {{ $company->alamat }}
                        </p>
                        <p class="text-center mt-2">
                            @if($company->maps_link)
                            <a href="{{ $company->maps_link }}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-2"></i>Buka di Google Maps
                            </a>
                            @else
                            <a href="https://maps.google.com/?q={{ urlencode($company->alamat) }}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-2"></i>Buka di Google Maps
                            </a>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= PRODUK UMKM ================= -->
@if($company)
<section class="section-white editable-section" data-section="products">
    <div class="edit-fab" onclick="toggleProductEditMode()">
        <i class="fas fa-box"></i>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title mb-4 text-center">PRODUK {{ strtoupper($company->nama) }}</h2>
                
                <!-- Product Edit Mode Controls -->
                <div id="productEditControls" class="d-none mb-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-success" onclick="selectAllProducts()">
                                        <i class="fas fa-check-square me-1"></i>Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" onclick="showSelectedProducts()">
                                        <i class="fas fa-eye me-1"></i>Tampilkan
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="hideSelectedProducts()">
                                        <i class="fas fa-eye-slash me-1"></i>Sembunyikan
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('produk.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i>Tambah Produk
                                    </a>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleProductEditMode()">
                                        <i class="fas fa-times me-1"></i>Tutup Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="produk-box">
                    <div class="row g-4">
                        @forelse($produks as $produk)
                        @if($produk->show_in_catalog)
                        <div class="col-md-4">
                            <div class="card-produk">
                                <!-- Product Edit Controls -->
                                <div class="product-edit-controls d-none">
                                    <div class="form-check">
                                        <input class="form-check-input product-checkbox" 
                                               type="checkbox" 
                                               value="{{ $produk->id }}"
                                               id="select_{{ $produk->id }}">
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editProduct({{ $produk->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProduct({{ $produk->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                @if($produk->foto)
                                    <img src="{{ asset('storage/'.$produk->foto) }}">
                                @else
                                    <img src="/images/no-image.png">
                                @endif

                                <div class="card-body text-center">
                                    <h5 class="editable-title" contenteditable="true" data-field="nama_produk" data-id="{{ $produk->id }}">{{ $produk->nama_produk }}</h5>
                                    <p class="deskripsi editable-description" contenteditable="true" data-field="deskripsi_catalog" data-id="{{ $produk->id }}">
                                        {{ $produk->deskripsi_catalog ? Str::limit($produk->deskripsi_catalog, 100) : ($produk->deskripsi ? Str::limit($produk->deskripsi, 100) : 'Tidak ada deskripsi') }}
                                    </p>
                                    <p class="price editable-price" contenteditable="true" data-field="harga_jual" data-id="{{ $produk->id }}">
                                        Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                                    </p>
                                    <div class="product-actions mt-2">
                                        <button class="btn btn-sm btn-outline-primary" onclick="saveProductChanges({{ $produk->id }})">
                                            <i class="fas fa-save me-1"></i>Simpan
                                        </button>
                                        <div class="form-check form-switch d-inline-block ms-2">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="catalog_{{ $produk->id }}"
                                                   {{ $produk->show_in_catalog ? 'checked' : '' }}
                                                   onchange="toggleCatalogVisibility({{ $produk->id }}, this.checked)">
                                            <label class="form-check-label" for="catalog_{{ $produk->id }}">
                                                <small>Tampilkan</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <div class="col-12 text-center">
                            <p class="text-muted">Belum ada produk tersedia untuk ditampilkan di catalog</p>
                            <a href="{{ route('produk.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i>Tambah Produk Pertama
                            </a>
                                </a>
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($produks->hasPages())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-center">
                                {{ $produks->links() }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for bulk actions -->
<form id="bulkActionForm" method="POST" action="{{ route('kelola-catalog.bulk-visibility') }}">
    @csrf
    <input type="hidden" name="action" id="bulkAction">
    <input type="hidden" name="product_ids" id="bulkProductIds">
</form>

<!-- Hidden form for catalog description update -->
<form id="catalogDescForm" method="POST" action="">
    @csrf
    @method('PATCH')
    <input type="hidden" name="deskripsi_catalog" id="catalogDescValue">
</form>

<style>
.card.border-success {
    border-left: 5px solid #28a745 !important;
}

.card.border-secondary {
    border-left: 5px solid #6c757d !important;
    opacity: 0.8;
}

.product-checkbox {
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.badge {
    font-size: 0.75em;
}
</style>

<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkButtons();
});

// Update bulk buttons state
function updateBulkButtons() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const showBtn = document.getElementById('bulkShowBtn');
    const hideBtn = document.getElementById('bulkHideBtn');
    
    if (checkedBoxes.length > 0) {
        showBtn.disabled = false;
        hideBtn.disabled = false;
    } else {
        showBtn.disabled = true;
        hideBtn.disabled = true;
    }
}

// Listen to checkbox changes
document.querySelectorAll('.product-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkButtons);
});

// Bulk action
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const productIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (productIds.length === 0) {
        alert('Pilih minimal satu produk terlebih dahulu.');
        return;
    }
    
    document.getElementById('bulkAction').value = action;
    document.getElementById('bulkProductIds').value = JSON.stringify(productIds);
    document.getElementById('bulkActionForm').submit();
}

// Toggle catalog visibility
function toggleCatalogVisibility(productId, isVisible) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/kelola-catalog/${productId}/toggle-visibility`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ show_in_catalog: isVisible })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update card appearance
            const card = document.getElementById(`product_${productId}`).closest('.card');
            const statusText = card.querySelector('.card-footer small');
            
            if (isVisible) {
                card.classList.remove('border-secondary');
                card.classList.add('border-success');
                statusText.innerHTML = '<i class="fas fa-eye"></i> Ditampilkan';
            } else {
                card.classList.remove('border-success');
                card.classList.add('border-secondary');
                statusText.innerHTML = '<i class="fas fa-eye-slash"></i> Disembunyikan';
            }
            
            // Show success message
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan', 'error');
    });
}

// Update catalog description
function updateCatalogDescription(productId, description) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/kelola-catalog/${productId}/update-catalog-info`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ deskripsi_catalog: description })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Deskripsi catalog berhasil diperbarui', 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan', 'error');
    });
}

// Toast notification
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();
    
    setTimeout(() => {
        toastContainer.remove();
    }, 5000);
}
</script>
@endsection
