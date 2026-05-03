@extends('layouts.catalog')

@section('title', 'E-Catalog ' . ($company->nama ?? 'UMKM'))

@push('styles')
<style>
/* Catalog Styles - Based on commit be112d0 key changes */
.catalog-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Cover Section */
.cover-section {
    height: 100vh;
    min-height: 600px;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
}

.cover-content {
    max-width: 800px;
    padding: 40px;
}

.company-name {
    font-size: 4rem;
    font-weight: 900;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: -2px;
}

.company-tagline {
    font-size: 2rem;
    font-weight: 300;
    margin-bottom: 30px;
    opacity: 0.9;
}

.company-description {
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 40px;
    opacity: 0.8;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.explore-button {
    display: inline-block;
    padding: 15px 40px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.explore-button:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Team Section */
.team-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.section-title {
    font-size: 3rem;
    font-weight: 800;
    text-align: center;
    margin-bottom: 60px;
    color: #333;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.team-member {
    background: white;
    border-radius: 15px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.team-member:hover {
    transform: translateY(-5px);
}

.member-photo {
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #f0f0f0;
}

.member-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: filter 0.3s ease;
}

.team-member:hover .member-photo img {
    filter: none; /* Changed from grayscale(0%) to none as per commit be112d0 */
}

.member-name {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.member-position {
    font-size: 1rem;
    color: #666;
    margin-bottom: 15px;
}

.member-description {
    font-size: 0.9rem;
    line-height: 1.6;
    color: #777;
}

/* Products Section */
.products-section {
    padding: 80px 0;
    background: white;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.product-item {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.product-item:hover {
    transform: translateY(-5px);
}

.product-image {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease; /* Changed from filter 0.3s to transform 0.3s as per commit be112d0 */
}

.product-item:hover .product-image img {
    transform: scale(1.05); /* Changed from filter grayscale(0%) to scale(1.05) as per commit be112d0 */
}

.product-info {
    padding: 25px;
}

.product-name {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.product-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #007bff;
}

/* Location Section */
.location-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.location-content {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.location-name {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.location-address {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #666;
    margin-bottom: 10px;
}

.location-contact {
    font-size: 1rem;
    color: #666;
    margin-bottom: 30px;
}

.maps-link {
    display: inline-block;
    padding: 12px 30px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.maps-link:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .company-name {
        font-size: 2.5rem;
    }
    
    .company-tagline {
        font-size: 1.5rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .team-grid,
    .products-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .cover-content {
        padding: 20px;
    }
}
</style>
@endpush

@section('content')
<div class="catalog-container">
    <!-- COVER SECTION -->
    <section class="cover-section">
        <div class="cover-content">
            <h1 class="company-name">{{ $company->nama ?? 'UMKM' }}</h1>
            <p class="company-tagline">BRANDING PRODUCT.</p>
            <p class="company-description">
                {{ $company->catalog_description ?? 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi, pengelolaan sumber daya yang optimal, serta pengendalian proses yang terintegrasi untuk menghasilkan produk berkualitas tinggi secara konsisten.' }}
            </p>
            <a href="#products" class="explore-button">Explore</a>
        </div>
        <!-- DORTH text removed as per commit be112d0 -->
    </section>

    <!-- TEAM SECTION -->
    <section class="team-section">
        <div class="catalog-container">
            <h2 class="section-title">THE TEAM.</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member">
                    </div>
                    <h3 class="member-name">Joko Susilo</h3>
                    <p class="member-position">Direktur Utama</p>
                    <p class="member-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
                <div class="team-member">
                    <div class="member-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member">
                    </div>
                    <h3 class="member-name">Sari Wulandari</h3>
                    <p class="member-position">Manajer Produksi</p>
                    <p class="member-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="products-section" id="products">
        <div class="catalog-container">
            <h2 class="section-title">PRODUCT MATERIAL.</h2>
            <div class="products-grid">
                @forelse($produks as $produk)
                    <div class="product-item">
                        <div class="product-image">
                            @if($produk->foto_path)
                                <img src="{{ asset('storage/' . $produk->foto_path) }}" alt="{{ $produk->nama_produk }}">
                            @else
                                <img src="{{ asset('images/default-avatar.png') }}" alt="{{ $produk->nama_produk }}">
                            @endif
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">{{ $produk->nama_produk }}</h3>
                            <p class="product-price">Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p>No products available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- LOCATION SECTION -->
    <section class="location-section">
        <div class="catalog-container">
            <h2 class="section-title">LOKASI KAMI.</h2>
            <div class="location-content">
                <h3 class="location-name">{{ $company->nama ?? 'UMKM' }}</h3>
                <p class="location-address">{{ $company->alamat ?? 'Alamat tidak tersedia' }}</p>
                <p class="location-contact">
                    {{ $company->telepon ?? 'Telepon tidak tersedia' }}<br>
                    {{ $company->email ?? 'Email tidak tersedia' }}
                </p>
                @if($company->maps_link)
                    <a href="{{ $company->maps_link }}" target="_blank" class="maps-link">
                        <i class="fas fa-map-marker-alt"></i> Lihat di Peta
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
