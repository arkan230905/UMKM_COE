@extends('layouts.catalog')

@section('title', 'E-Catalog UMKM Desa')

@section('content')

<!-- ================= HERO DESA ================= -->
<div class="hero-desa">
    <div class="overlay"></div>
    <div class="container position-relative text-center text-white">
        <h1 class="fw-bold display-5">UMKM Desa Karangpakuan</h1>
        <p class="lead">Pusat Produk UMKM & Wisata Desa</p>

        <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">
            <a href="#wisata-desa" class="btn btn-outline-light px-4">Wisata Desa</a>
            <a href="#produk-umkm" class="btn btn-warning px-4">Produk UMKM</a>
        </div>
    </div>
</div>

<!-- ================= WISATA DESA ================= -->
<section id="wisata-desa" class="section-white">
    <div class="container text-center">
        <h2 class="section-title mb-4">Wisata Desa</h2>

        <!-- Image Slider -->
        <div class="slider-container">
            <div class="slider-wrapper">
                <div class="slide active">
                    <img src="/images/karangpakuanumkm.jpg" alt="UMKM Karangpakuan">
                    <h5 class="mt-3">UMKM Karangpakuan</h5>
                </div>
                <div class="slide">
                    <img src="/images/karangpakuanwaduk.jpg" alt="Waduk Karangpakuan">
                    <h5 class="mt-3">Waduk Karangpakuan</h5>
                </div>
                <div class="slide">
                    <img src="/images/karangpakuancamp.jpg" alt="Camp Karangpakuan">
                    <h5 class="mt-3">Camp Karangpakuan</h5>
                </div>
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
                <span class="indicator active" onclick="goToSlide(0)"></span>
                <span class="indicator" onclick="goToSlide(1)"></span>
                <span class="indicator" onclick="goToSlide(2)"></span>
            </div>
        </div>
    </div>
</section>

<!-- ================= PRODUK UMKM ================= -->
<section id="produk-umkm" class="section-soft">
    <div class="container">
        <h2 class="section-title text-center mb-5">Produk Unggulan UMKM</h2>

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
                        <p class="price">
                            Rp {{ number_format($produk->harga_jual,0,',','.') }}
                        </p>

                        <button class="btn btn-warning w-100"
                            onclick="orderProduct({{ $produk->id }})"
                            @if($produk->stok <= 0) disabled @endif>
                            {{ $produk->stok > 0 ? 'Pesan Sekarang' : 'Stok Habis' }}
                        </button>
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
</section>

<!-- ================= CALL TO ACTION ================= -->
<section class="section-soft">
    <div class="container text-center">
        <h2 class="section-title mb-4">Ingin Menikmati Wisata dan Produk UMKM?</h2>
        <p class="mb-4">Daftar sekarang untuk memesan tiket wisata dan produk unggulan desa</p>
        
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/pelanggan/login" class="btn btn-warning btn-lg px-5">
                <i class="fas fa-shopping-cart me-2"></i>
                Beli Tiket Wisata dan Produk UMKM Disini
            </a>
            <a href="/pelanggan/register" class="btn btn-outline-warning btn-lg px-5">
                <i class="fas fa-user-plus me-2"></i>
                Daftar Akun Baru
            </a>
        </div>
    </div>
</section>

<!-- ================= STYLE ================= -->
<style>
.hero-desa {
    background: url('/images/hero-desa.jpg') center/cover no-repeat;
    padding: 130px 0;
    position: relative;
}
.hero-desa .overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.55);
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
    font-weight: 700;
    color: #3a3a3a;
}

/* Slider Styles */
.slider-container {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.slider-wrapper {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.slide {
    min-width: 100%;
    position: relative;
}

.slide img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 20px;
}

.slide h5 {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    margin: 0;
    font-weight: 600;
}

.slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.9);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #3a3a3a;
    transition: all 0.3s;
    z-index: 10;
}

.slider-btn:hover {
    background: rgba(255,255,255,1);
    transform: translateY(-50%) scale(1.1);
}

.prev-btn {
    left: 20px;
}

.next-btn {
    right: 20px;
}

.slider-indicators {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}

.indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s;
}

.indicator.active {
    background: #ffc107;
    width: 30px;
    border-radius: 6px;
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
.step {
    background: #f7f4ef;
    padding: 20px;
    border-radius: 15px;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .slider-container {
        margin: 0 20px;
    }
    
    .slide img {
        height: 250px;
    }
    
    .slider-btn {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
    
    .prev-btn {
        left: 10px;
    }
    
    .next-btn {
        right: 10px;
    }
    
    .slide h5 {
        font-size: 14px;
        padding: 8px 16px;
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
});
</script>

@endsection
