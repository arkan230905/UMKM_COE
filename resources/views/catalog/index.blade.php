@extends('layouts.catalog')

@section('title', 'E-Catalog UMKM Desa')

@section('content')

<!-- ================= HERO SLIDER ================= -->
<section class="hero-slider">
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
<section class="section-white">
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
                            <p class="desa-description">
                                <strong>Kontak:</strong><br>
                                <i class="fas fa-envelope me-1"></i>{{ $company->email }}<br>
                                <i class="fas fa-phone me-1"></i>{{ $company->telepon }}<br>
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $company->alamat }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@else
<!-- Default section jika tidak ada company yang login -->
<section class="section-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12 text-center">
                <h2 class="section-title mb-4">Selamat Datang di Katalog UMKM</h2>
                <p class="desa-description">
                    Silakan login untuk melihat katalog produk dari UMKM pilihan Anda.
                </p>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= PETA LOKASI ================= -->
@if($company)
<section class="section-white">
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
<section class="section-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title mb-4 text-center">PRODUK {{ strtoupper($company->nama) }}</h2>
                <div class="produk-box">
                    <div class="row g-4">
                        @forelse($produks as $produk)
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
                                        {{ $produk->deskripsi ? Str::limit($produk->deskripsi, 100) : 'Tidak ada deskripsi' }}
                                    </p>
                                    <p class="price">
                                        Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center">
                            <p class="text-muted">Belum ada produk tersedia</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@else
<!-- Section jika tidak ada company yang login -->
<section class="section-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h3 class="section-title mb-4">Produk Tidak Tersedia</h3>
                <p class="desa-description">
                    Silakan login terlebih dahulu untuk melihat produk dari UMKM pilihan Anda.
                </p>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ================= TOMBOL BELI ================= -->
<section class="section-white">
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



<!-- ================= STYLE ================= -->
<style>
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

/* Tentang Desa Styles */
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

/* Gambar lebih besar */
.section-white img {
    max-height: 450px;
    width: 100%;
    object-fit: cover;
}

/* Map Styles */
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

.map-info .btn-outline-primary {
    color: #007bff;
    border-color: #007bff;
    transition: all 0.3s ease;
}

.map-info .btn-outline-primary:hover {
    background-color: #007bff;
    color: white;
}

/* Responsive untuk Map */
@media (max-width: 768px) {
    .map-wrapper iframe {
        height: 300px;
    }
}

/* Produk Box Styles */
.produk-box {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-top: 1rem;
}

.produk-box .section-title {
    color: #3a3a3a;
    font-weight: 600;
    margin-bottom: 2rem;
}

/* Responsive untuk Produk Box */
@media (max-width: 768px) {
    .produk-box {
        padding: 1.5rem;
        margin-top: 0.5rem;
    }
}

/* Tombol Beli Styles */
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

/* Responsive untuk Tentang Desa */
@media (max-width: 901px) {
    .desa-content {
        padding-left: 1.5rem;
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

@media (max-width: 768px) {
    .desa-content {
        padding-left: 1rem;
        padding-right: 0;
        margin-top: 1rem;
    }
    
    .desa-description {
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .desa-content .section-title {
        font-size: 1.6rem;
        text-align: center;
    }
    
    .section-white img {
        max-height: 350px;
    }
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
.step {
    background: #f7f4ef;
    padding: 20px;
    border-radius: 15px;
    font-weight: 600;
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
}

@media (max-width: 480px) {
    .slider-container-full {
        height: 50vh;
        min-height: 350px;
    }
}
</style>

<!-- ================= SCRIPT ================= -->
<script>
function orderProduct(id) {
    window.location.href = '/pelanggan/login?redirect=catalog&product=' + id;
}

// Slider functionality
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const indicators = document.querySelectorAll('.indicator');
const totalSlides = slides.length;

function showSlide(index) {
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
    currentSlide += direction;
    
    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }
    
    showSlide(currentSlide);
}

function goToSlide(index) {
    currentSlide = index;
    showSlide(currentSlide);
}

// Auto-slide functionality
function startAutoSlide() {
    setInterval(() => {
        changeSlide(1);
    }, 4000); // Change slide every 4 seconds
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
</script>

@endsection
