@extends('layouts.catalog')

@section('title', 'E-Catalog UMKM Desa')

@section('content')

<!-- ================= HERO SLIDER ================= -->
<section class="hero-slider">
    <div class="slider-container-full">
        <div class="slider-wrapper">
            <div class="slide active">
                <img src="/images/karangpakuanumkm.jpg" alt="UMKM Karangpakuan">
            </div>
            <div class="slide">
                <img src="/images/karangpakuanwaduk.jpg" alt="Waduk Karangpakuan">
            </div>
            <div class="slide">
                <img src="/images/karangpakuancamp.jpg" alt="Camp Karangpakuan">
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
</section>

<!-- ================= TENTANG DESA ================= -->
<section class="section-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-12">
                <div class="row g-4 align-items-center">
                    <div class="col-md-6">
                        <img src="/images/fotobersamadesa.jpg" alt="Bersama Desa Karangpakuan" class="img-fluid rounded-3 shadow">
                    </div>
                    <div class="col-md-6">
                        <div class="desa-content">
                            <h1 class="section-title mb-4">Tentang Desa Karangpakuan</h1>
                            <p class="desa-description">
                                Desa Karangpakuan adalah sebuah desa yang kaya akan potensi alam dan budaya, 
                                terletak di kawasan Sumedang, Jawa Barat. Desa ini menawarkan keindahan alam yang memukau 
                                dengan latar belakang pegunungan yang hijau dan udara yang segar, menciptakan suasana yang tenang 
                                dan nyaman untuk dikunjungi.
                            </p>
                            <p class="desa-description">
                                Sebagai bagian dari Kabupaten Sumedang di Jawa Barat, Desa Karangpakuan memiliki 
                                karakteristik desa yang khas dengan masyarakat yang ramah dan menjunjung tinggi nilai-nilai gotong royong. 
                                Potensi wisata alam menjadi daya tarik utama dengan adanya curug yang memukau, waduk yang indah, 
                                dan area perkemahan yang asri dan terawat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= PETA LOKASI ================= -->
<section class="section-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title mb-4 text-center">Lokasi Desa Karangpakuan</h2>
                <div class="map-container">
                    <div class="map-wrapper">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.1234567890123!2d107.9234567890123!3d-6.8234567890123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68f123456789ab%3A0x123456789abcdef0!2sDesa%20Karangpakuan%2C%20Darmaraja%2C%20Kabupaten%20Sumedang%2C%20Jawa%20Barat!5e0!3m2!1sen!2sid!4v1234567890123"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 15px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="map-info mt-3">
                        <p class="text-center mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <strong>Desa Karangpakuan</strong>, Kecamatan Darmaraja, Kab. Sumedang, Jawa Barat. Kode Pos: 45372
                        </p>
                        <p class="text-center mt-2">
                            <a href="https://maps.google.com/?q=Desa+Karangpakuan+Darmaraja+Sumedang+Jawa+Barat" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-2"></i>Buka di Google Maps
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= PRODUK UMKM ================= -->
<section class="section-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="section-title mb-4 text-center">PRODUK UMKM</h2>
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
                                        Rp {{ number_format($produk->harga_jual,0,',','.') }}
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
