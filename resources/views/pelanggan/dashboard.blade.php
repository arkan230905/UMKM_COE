@extends('layouts.pelanggan')

@section('content')
<style>
.hero-section {
    background: linear-gradient(135deg, #f8f4e6 0%, #e8dcc0 100%);
    border-radius: 20px;
    padding: 3rem 2rem;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.search-container {
    position: relative;
    max-width: 600px;
    margin: 0 auto;
}

.search-input {
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 50px;
    padding: 1rem 3rem 1rem 1.5rem;
    font-size: 1.1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.search-icon {
    position: absolute;
    right: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    color: #d4a574;
    font-size: 1.2rem;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 2rem;
    position: relative;
    text-align: center;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #d4a574 0%, #b8935f 100%);
    border-radius: 2px;
}

.product-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
    background: white;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

.product-image-container {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-image-placeholder {
    height: 200px;
    width: 100%;
    background: linear-gradient(135deg, #d4a574 0%, #b8935f 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.favorite-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}

.favorite-btn:hover {
    background: white;
    transform: scale(1.1);
}

.favorite-btn.active {
    background: #ff4757;
    color: white;
}

.product-body {
    padding: 1.5rem;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #d4a574;
    margin-bottom: 1rem;
}

.add-to-cart-btn {
    background: linear-gradient(135deg, #d4a574 0%, #b8935f 100%);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    width: 100%;
}

.add-to-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(212, 165, 116, 0.3);
}

.contact-section {
    background: linear-gradient(135deg, #f8f4e6 0%, #e8dcc0 100%);
    border-radius: 20px;
    padding: 3rem;
    margin-top: 4rem;
    text-align: center;
    color: #2d3748;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.whatsapp-btn {
    background: #25d366;
    border: none;
    border-radius: 50px;
    padding: 1rem 2rem;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.whatsapp-btn:hover {
    background: #128c7e;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
}
</style>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-dark mb-4">
                    Selamat Datang di UMKM COE
                </h1>
                <p class="lead text-muted mb-4">
                    Temukan produk berkualitas terbaik dengan harga terjangkau. 
                    Belanja sekarang dan nikmati pengalaman berbelanja yang menyenangkan!
                </p>
                <div class="d-flex gap-3">
                    <a href="#products" class="btn btn-dark btn-lg rounded-pill">
                        <i class="fas fa-shopping-bag me-2"></i> Mulai Belanja
                    </a>
                    <a href="{{ route('pelanggan.cart') }}" class="btn btn-outline-dark btn-lg rounded-pill">
                        <i class="fas fa-shopping-cart me-2"></i> Keranjang
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="search-container">
                    <form action="{{ route('pelanggan.dashboard') }}" method="GET">
                        <input type="text" 
                               name="q" 
                               value="{{ $search ?? request('q') }}" 
                               class="form-control search-input" 
                               placeholder="Cari produk favoritmu...">
                        <i class="fas fa-search search-icon"></i>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Favorites Section -->
    @if(($favoriteProduks ?? collect())->count())
    <section class="mb-5">
        <h2 class="section-title">❤️ Favorit Saya</h2>
        <div class="row g-4">
            @foreach($favoriteProduks as $favP)
            <div class="col-md-4 col-lg-3">
                <div class="product-card">
                    <div class="product-image-container">
                        @if($favP->foto)
                        <img src="{{ asset('storage/' . $favP->foto) }}" class="product-image" alt="{{ $favP->nama_produk }}">
                        @else
                        <div class="product-image-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        @endif
                        <form action="{{ route('pelanggan.favorites.toggle') }}" method="POST">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $favP->id }}">
                            <button type="submit" class="favorite-btn active">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                    </div>
                    <div class="product-body">
                        <h3 class="product-title">{{ $favP->nama_produk }}</h3>
                        <div class="product-price">Rp {{ number_format($favP->harga_jual, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- All Products Section -->
    <section class="mb-5" id="products">
        <h2 class="section-title">🛍️ Semua Produk</h2>
        
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row g-4">
            @forelse($produks as $produk)
            <div class="col-md-4 col-lg-3">
                <div class="product-card">
                    <div class="product-image-container">
                        @if($produk->foto)
                        <img src="{{ asset('storage/' . $produk->foto) }}" class="product-image" alt="{{ $produk->nama_produk }}">
                        @else
                        <div class="product-image-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                        @endif
                        <form action="{{ route('pelanggan.favorites.toggle') }}" method="POST">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            @php
                                $isFav = in_array($produk->id, $favoriteIds ?? []);
                            @endphp
                            <button type="submit" class="favorite-btn {{ $isFav ? 'active' : '' }}">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                    </div>
                    <div class="product-body">
                        <h3 class="product-title">{{ $produk->nama_produk }}</h3>
                        <div class="product-price">Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</div>
                        @if($produk->stok_tersedia > 0)
                        <form action="{{ route('pelanggan.cart.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="add-to-cart-btn">
                                <i class="fas fa-shopping-cart me-2"></i> Tambah ke Keranjang
                            </button>
                        </form>
                        @else
                        <button class="add-to-cart-btn" disabled>
                            <i class="fas fa-times me-2"></i> Stok Habis
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Belum ada produk tersedia</h4>
            </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $produks->links() }}
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <h2 class="display-5 fw-bold mb-3">Butuh Bantuan?</h2>
            <p class="lead mb-4">
                Ada pertanyaan tentang produk atau pesanan? Hubungi kami melalui WhatsApp.
            </p>
            @php
                $wa = $whatsappNumber ?? '';
                $wa = preg_replace('/[^0-9]/', '', $wa);
                $waLink = $wa ? 'https://wa.me/'.$wa : null;
            @endphp
            @if($waLink)
            <a href="{{ $waLink }}" target="_blank" class="whatsapp-btn">
                <i class="fab fa-whatsapp me-2"></i> Chat via WhatsApp
            </a>
            @endif
        </div>
    </section>
</div>
@endsection
