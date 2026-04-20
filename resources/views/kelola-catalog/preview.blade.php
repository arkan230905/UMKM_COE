@extends('layouts.catalog')

@section('title', 'Preview & Edit Catalog')

@section('content')
<!-- Edit Mode Indicator -->
<div class="edit-mode-indicator">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-edit me-2"></i>
                <span><strong>Mode Edit Catalog</strong> - Hover pada elemen untuk edit</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('catalog') }}" target="_blank" class="btn btn-success btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>Lihat Publik
                </a>
                <a href="{{ route('kelola-catalog.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
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
                            @if($company->catalog_description)
                            <p class="desa-description">
                                {!! nl2br(e($company->catalog_description)) !!}
                            </p>
                            @else
                            <p class="desa-description">
                                {{ $company->nama }} adalah sebuah UMKM yang bergerak di bidang {{ $company->jenis_usaha ?? 'produksi makanan' }}, 
                                terletak di {{ $company->alamat }}. Perusahaan ini berkomitmen untuk menyediakan produk berkualitas tinggi 
                                dengan bahan baku pilihan dan proses produksi yang higienis.
                            </p>
                            <p class="desa-description">
                                Dengan pengalaman dalam industri {{ $company->jenis_usaha ?? 'makanan' }}, {{ $company->nama }} 
                                terus berinovasi untuk menghadirkan produk terbaik bagi konsumen. Kami menjunjung tinggi nilai-nilai 
                                kualitas, kebersihan, dan kepuasan pelanggan dalam setiap produk yang kami hasilkan.
                            </p>
                            @endif
                            <p class="desa-description">
                                <strong>Kontak:</strong><br>
                                <i class="fas fa-envelope me-1"></i>{{ $company->email }}<br>
                                <i class="fas fa-phone me-1"></i>{{ $company->telepon }}<br>
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $company->alamat }}
                            </p>
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
    <div class="edit-fab" onclick="window.location.href='{{ route('kelola-catalog.index') }}'">
        <i class="fas fa-box"></i>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title mb-4 text-center">PRODUK {{ strtoupper($company->nama) }}</h2>
                <div class="produk-box">
                    <div class="row g-4">
                        @forelse($produks as $produk)
                        @if($produk->show_in_catalog)
                        <div class="col-md-4">
                            <div class="card-produk">
                                @if($produk->foto)
                                    <img src="{{ asset('storage/'.$produk->foto) }}">
                                @else
                                    <img src="/images/no-image.png">
                                @endif

                                <div class="card-body text-center">
                                    <h5>{{ $produk->nama_produk }}</h5>
                                    <p class="deskripsi">
                                        {{ $produk->deskripsi_catalog ? Str::limit($produk->deskripsi_catalog, 100) : ($produk->deskripsi ? Str::limit($produk->deskripsi, 100) : 'Tidak ada deskripsi') }}
                                    </p>
                                    <p class="price">
                                        Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <div class="col-12 text-center">
                            <p class="text-muted">Belum ada produk tersedia untuk ditampilkan di catalog</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= TOMBOL BELI ================= -->
<section class="section-white editable-section" data-section="action">
    <div class="edit-fab" onclick="editBuyButton()">
        <i class="fas fa-shopping-cart"></i>
    </div>
    
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <button class="btn-beli" onclick="window.location.href='/pelanggan/login'">
                    klik disini untuk membeli
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Edit Company Info Modal -->
<div class="modal fade" id="editCompanyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Informasi Perusahaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCompanyForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Perusahaan</label>
                            <input type="text" name="nama" class="form-control" value="{{ $company->nama ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $company->email ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control" value="{{ $company->telepon ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo Perusahaan</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah logo</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" required>{{ $company->alamat ?? '' }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi Catalog</label>
                            <textarea name="catalog_description" class="form-control" rows="4" placeholder="Deskripsi singkat tentang perusahaan">{{ $company->catalog_description ?? '' }}</textarea>
                            <small class="text-muted">Deskripsi ini akan ditampilkan di halaman catalog</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Maps Modal -->
<div class="modal fade" id="editMapsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Peta Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editMapsForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Link Google Maps</label>
                        <input type="url" name="maps_link" class="form-control" value="{{ $company->maps_link ?? '' }}" 
                               placeholder="https://maps.google.com/?q=alamat">
                        <small class="text-muted">Link embed Google Maps untuk lokasi perusahaan</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control" 
                                   value="{{ $company->latitude ?? '' }}" placeholder="-6.823456">
                            <small class="text-muted">Koordinat latitude (opsional)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control" 
                                   value="{{ $company->longitude ?? '' }}" placeholder="107.923456">
                            <small class="text-muted">Koordinat longitude (opsional)</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Cara mendapatkan link:</strong><br>
                        1. Buka Google Maps<br>
                        2. Cari lokasi perusahaan<br>
                        3. Klik "Bagikan" -> "Embed peta"<br>
                        4. Salin URL src dari iframe
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Photo Modal -->
<div class="modal fade" id="addPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Foto Catalog</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPhotoForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Foto</label>
                        <input type="text" name="judul" class="form-control" placeholder="Judul foto (opsional)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto *</label>
                        <input type="file" name="foto" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG, GIF (Maks: 4MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi foto (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah Foto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Edit Mode Indicator */
.edit-mode-indicator {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Editable Sections */
.editable-section {
    position: relative;
    transition: all 0.3s ease;
}

.editable-section:hover {
    outline: 2px dashed #667eea;
    outline-offset: -2px;
}

/* FAB Buttons */
.edit-fab {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
    z-index: 100;
}

.editable-section:hover .edit-fab {
    opacity: 1;
    transform: scale(1);
}

.edit-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.5);
}

.edit-fab:active {
    transform: scale(0.95);
}

/* Catalog Styles (same as original catalog) */
.hero-slider {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.slider-container-full {
    position: relative;
    width: 100%;
    height: 70vh;
    min-height: 500px;
    overflow: hidden;
}

.slider-wrapper {
    display: flex;
    height: 100%;
    transition: transform 0.5s ease-in-out;
}

.slide {
    min-width: 100%;
    height: 100%;
    position: relative;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.slide-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
    padding: 2rem;
}

.slide-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.slide-content p {
    font-size: 1.2rem;
    margin: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.9);
    border: none;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #3a3a3a;
    transition: all 0.3s;
    z-index: 10;
}

.slider-btn:hover {
    background: rgba(255,255,255,1);
    transform: translateY(-50%) scale(1.1);
}

.prev-btn {
    left: 30px;
}

.next-btn {
    right: 30px;
}

.slider-indicators {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 15px;
    z-index: 10;
}

.indicator {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s;
}

.indicator.active {
    background: #ffc107;
    width: 40px;
    border-radius: 8px;
}

.section-soft {
    background: #f7f4ef;
    padding: 80px 0;
}
.section-white {
    background: #ffffff;
    padding: 80px 0;
}
.section-title {
    font-weight: 500;
    color: #3a3a3a;
}

.desa-content {
    padding-left: 2rem;
    padding-right: 0;
}

.desa-description {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #555;
    margin-bottom: 0.5rem;
    text-align: justify;
}

.desa-content .section-title {
    color: #3a3a3a;
    font-size: 2.5rem;
    font-weight: 800;
}

.section-white img {
    max-height: 450px;
    width: 100%;
    object-fit: cover;
}

.map-container {
    margin-top: 2rem;
}

.map-wrapper {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.map-wrapper iframe {
    width: 100%;
    height: 450px;
    border: none;
}

.map-info {
    text-align: center;
    color: #555;
}

.produk-box {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-top: 1rem;
}

.card-produk {
    background: #fff;
    border-radius: 20px;
    padding: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}
.card-produk img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 15px;
}
.price {
    font-weight: bold;
    color: #d39e00;
}

.deskripsi {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.4;
    margin-bottom: 1rem;
    min-height: 2.8rem;
}

.btn-beli {
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: #3a3a3a;
    border: none;
    padding: 15px 40px;
    font-size: 1.2rem;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-beli:hover {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 152, 0, 0.4);
}

.btn-beli:active {
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .slider-container-full {
        height: 60vh;
        min-height: 400px;
    }
    
    .slider-btn {
        width: 45px;
        height: 45px;
        font-size: 16px;
    }
    
    .prev-btn {
        left: 15px;
    }
    
    .next-btn {
        right: 15px;
    }
    
    .slider-indicators {
        bottom: 20px;
        gap: 10px;
    }
    
    .indicator {
        width: 10px;
        height: 10px;
    }
    
    .indicator.active {
        width: 25px;
    }
    
    .desa-content {
        padding-left: 1rem;
        padding-right: 0;
        margin-top: 1rem;
    }
    
    .desa-description {
        font-size: 1rem;
        line-height: 1.6;
    }
    
    .desa-content .section-title {
        font-size: 1.8rem;
        text-align: center;
    }
    
    .section-white img {
        max-height: 400px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Slider functionality
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const indicators = document.querySelectorAll('.indicator');
const totalSlides = slides.length;

function showSlide(index) {
    if (slides.length === 0) return;
    
    // Hide all slides
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(indicator => indicator.classList.remove('active'));
    
    // Show current slide
    slides[index].classList.add('active');
    indicators[index].classList.add('active');
    
    // Move slider wrapper
    const sliderWrapper = document.querySelector('.slider-wrapper');
    sliderWrapper.style.transform = `translateX(-${index * 100}%)`;
}

function changeSlide(direction) {
    if (totalSlides === 0) return;
    
    currentSlide += direction;
    
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    
    showSlide(currentSlide);
}

function goToSlide(index) {
    if (totalSlides === 0) return;
    currentSlide = index;
    showSlide(currentSlide);
}

// Auto-slide functionality
function startAutoSlide() {
    if (totalSlides === 0) return;
    
    setInterval(() => {
        changeSlide(1);
    }, 4000);
}

// Initialize slider
document.addEventListener('DOMContentLoaded', function() {
    if (slides.length > 0) {
        showSlide(0);
        startAutoSlide();
        
        // Pause auto-slide on hover
        const sliderContainer = document.querySelector('.slider-container');
        let autoSlideInterval;
        
        sliderContainer.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });
        
        sliderContainer.addEventListener('mouseleave', () => {
            startAutoSlide();
        });
    }
});

// Edit functions
function editCompanyInfo() {
    const modal = new bootstrap.Modal(document.getElementById('editCompanyModal'));
    modal.show();
}

function editMaps() {
    const modal = new bootstrap.Modal(document.getElementById('editMapsModal'));
    modal.show();
}

function openPhotoModal() {
    const modal = new bootstrap.Modal(document.getElementById('addPhotoModal'));
    modal.show();
}

function managePhotos() {
    window.location.href = '/kelola-catalog/photos';
}

function editBuyButton() {
    alert('Fitur edit tombol beli akan segera tersedia');
}

// Form submissions
document.getElementById('editCompanyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/kelola-catalog/settings/update', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editCompanyModal')).hide();
            location.reload();
        } else {
            alert('Gagal menyimpan: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});

document.getElementById('editMapsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        maps_link: formData.get('maps_link'),
        latitude: formData.get('latitude'),
        longitude: formData.get('longitude')
    };
    
    fetch('/kelola-catalog/settings/catalog', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editMapsModal')).hide();
            location.reload();
        } else {
            alert('Gagal menyimpan: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});

document.getElementById('addPhotoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/kelola-catalog/photos', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addPhotoModal')).hide();
            location.reload();
        } else {
            alert('Gagal mengunggah: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});
</script>
@endsection
