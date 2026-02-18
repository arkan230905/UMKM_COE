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
            <div class="col-lg-6">
                <img src="/images/fotobersamadesa.jpg" alt="Bersama Desa Karangpakuan" class="img-fluid rounded-3 shadow">
            </div>
            <div class="col-lg-6">
                <div class="desa-content">
                    <h2 class="section-title mb-4">Tentang Desa Karangpakuan</h2>
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
                    <p class="desa-description">
                        UMKM di Desa Karangpakuan terus berkembang dengan berbagai produk unggulan 
                        yang mencerminkan kekayaan lokal. Mulai dari produk pertanian segar, olahan pangan tradisional, 
                        hingga kerajinan tangan yang bernilai seni tinggi, semuanya diproduksi dengan kualitas terbaik 
                        oleh masyarakat desa yang berpengalaman.
                    </p>
                    <p class="desa-description">
                        Keberlanjutan pembangunan desa dan pemberdayaan masyarakat lokal menjadi prioritas utama, 
                        dengan tetap menjaga kelestarian alam dan budaya asli. Desa Karangpakuan siap menjadi 
                        destinasi wisata dan sentra UMKM unggulan di wilayah Sumedang, Jawa Barat.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= PRODUK UMKM ================= -->
<section id="produk-umkm" class="section-soft">
    <div class="container">
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
    font-weight: 700;
    color: #3a3a3a;
}

/* Tentang Desa Styles */
.desa-content {
    padding-left: 7rem;
}

.desa-description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #555;
    margin-bottom: 1.5rem;
    text-align: justify;
}

.desa-content .section-title {
    color: #3a3a3a;
    font-size: 2rem;
    font-weight: 800;
}

/* Gambar lebih besar */
.section-white img {
    max-height: 500px;
    width: 100%;
    object-fit: cover;
}

/* Responsive untuk Tentang Desa */
@media (max-width: 991px) {
    .desa-content {
        padding-left: 1rem;
        margin-top: 2rem;
    }
    
    .desa-description {
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    .desa-content .section-title {
        font-size: 1.5rem;
        text-align: center;
    }
    
    .section-white img {
        max-height: 400px;
    }
}

@media (max-width: 768px) {
    .desa-content {
        padding-left: 0;
        margin-top: 2rem;
    }
    
    .desa-description {
        font-size: 0.9rem;
        line-height: 1.6;
    }
    
    .desa-content .section-title {
        font-size: 1.5rem;
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
