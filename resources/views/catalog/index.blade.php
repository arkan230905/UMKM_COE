@extends('layouts.catalog')

@section('title', 'Katalog Produk - UMKM COE')

@section('content')
<div class="container-fluid">
    <!-- Hero Section -->
    <div class="hero-section text-center text-white py-5">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Katalog Produk</h1>
            <p class="lead mb-4">Temukan produk berkualitas terbaik untuk kebutuhan Anda</p>
            
            <!-- Search Bar -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('catalog') }}" class="search-form">
                        <div class="input-group input-group-lg">
                            <input type="text" 
                                   name="search" 
                                   class="form-control border-0 shadow-sm" 
                                   placeholder="Cari produk..." 
                                   value="{{ request('search') }}">
                            <button class="btn btn-warning border-0 shadow-sm" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tags -->
    <div class="container mt-4">
        <div class="filter-section">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <button class="btn filter-btn active">Semua</button>
                <button class="btn filter-btn">Elektronik</button>
                <button class="btn filter-btn">Fashion</button>
                <button class="btn filter-btn">Makanan</button>
                <button class="btn filter-btn">Minuman</button>
                <button class="btn filter-btn">Kesehatan</button>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="container mt-5">
        <div class="row g-4">
            @forelse($produks as $produk)
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card">
                        <!-- Product Image -->
                        <div class="product-image-wrapper">
                            @if($produk->foto)
                                <img src="{{ asset('storage/' . $produk->foto) }}" 
                                     class="product-image" 
                                     alt="{{ $produk->nama_produk }}">
                            @else
                                <div class="product-image-placeholder">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                            
                            <!-- Quick Actions -->
                            <div class="product-actions">
                                <button class="btn-action btn-action-light" title="Quick View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-action btn-action-light" title="Add to Favorites">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="product-info">
                            <!-- Category Badge -->
                            <div class="product-category">
                                <span class="badge bg-secondary">Elektronik</span>
                            </div>

                            <!-- Product Name -->
                            <h6 class="product-name">{{ $produk->nama_produk }}</h6>

                            <!-- Price -->
                            <div class="product-price">
                                <span class="current-price">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</span>
                            </div>

                            <!-- Stock Status -->
                            <div class="stock-status">
                                @if($produk->stok > 10)
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>Stok Tersedia
                                    </span>
                                @elseif($produk->stok > 0)
                                    <span class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Stok Terbatas
                                    </span>
                                @else
                                    <span class="text-danger">
                                        <i class="fas fa-times-circle me-1"></i>Stok Habis
                                    </span>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="product-buttons">
                                <button class="btn btn-outline-secondary btn-sm w-100 mb-2" 
                                        onclick="viewDetails({{ $produk->id }})">
                                    <i class="fas fa-info-circle me-1"></i>Detail
                                </button>
                                <button class="btn btn-warning w-100" 
                                        onclick="orderProduct({{ $produk->id }}, '{{ $produk->nama_produk }}')"
                                        @if($produk->stok <= 0) disabled @endif>
                                    <i class="fas fa-shopping-cart me-1"></i>
                                    @if($produk->stok > 0)
                                        Pesan
                                    @else
                                        Stok Habis
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Belum Ada Produk</h4>
                        <p class="text-muted">Maaf, belum ada produk yang tersedia saat ini.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Newsletter Section -->
    <div class="newsletter-section bg-light py-5 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-3">Dapatkan Penawaran Terbaik</h3>
                    <p class="text-muted">Daftar newsletter kami dan dapatkan diskon eksklusif</p>
                </div>
                <div class="col-md-6">
                    <form class="d-flex gap-2">
                        <input type="email" class="form-control" placeholder="Email Anda">
                        <button class="btn btn-primary">Berlangganan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
}

.search-form .input-group {
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.search-form .form-control {
    border-radius: 50px;
    padding: 12px 20px;
    border: none;
}

.search-form .btn {
    border-radius: 50px;
    padding: 12px 25px;
}

/* Filter Section */
.filter-section {
    margin-bottom: 2rem;
}

.filter-btn {
    border-radius: 25px;
    padding: 8px 20px;
    border: 2px solid #e9ecef;
    background: white;
    color: #6c757d;
    font-weight: 500;
    transition: all 0.3s;
}

.filter-btn:hover {
    border-color: #ffc107;
    color: #ffc107;
}

.filter-btn.active {
    background: #ffc107;
    border-color: #ffc107;
    color: white;
}

/* Product Cards */
.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
    height: 100%;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.product-image-wrapper {
    position: relative;
    height: 250px;
    overflow: hidden;
    background: #f8f9fa;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.product-card:hover .product-image {
    transform: scale(1.1);
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.product-actions {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    transition: opacity 0.3s;
}

.product-card:hover .product-actions {
    opacity: 1;
}

.btn-action {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.btn-action-light {
    background: rgba(255,255,255,0.9);
    color: #333;
}

.btn-action:hover {
    transform: scale(1.1);
}

.product-info {
    padding: 20px;
}

.product-category {
    margin-bottom: 10px;
}

.product-name {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 10px;
    line-height: 1.4;
    height: 2.8em;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-price {
    margin-bottom: 10px;
}

.current-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffc107;
}

.stock-status {
    margin-bottom: 15px;
    font-size: 0.875rem;
}

.product-buttons {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Newsletter Section */
.newsletter-section {
    border-radius: 20px;
    margin-top: 4rem !important;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-section {
        padding: 3rem 0;
    }
    
    .display-4 {
        font-size: 2rem;
    }
    
    .product-image-wrapper {
        height: 200px;
    }
    
    .filter-section {
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>

<!-- JavaScript -->
<script>
function orderProduct(productId, productName) {
    // Redirect to customer login page with product info
    window.location.href = '/pelanggan/login?redirect=catalog&product=' + productId;
}

function viewDetails(productId) {
    // For now, just show an alert. Can be expanded to show modal
    alert('Detail produk akan segera tersedia!');
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Here you can add filtering logic
            const filter = this.textContent.trim();
            console.log('Filter by:', filter);
        });
    });
});
</script>
@endsection
