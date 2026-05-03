@extends('layouts.catalog')

@section('title', 'E-Catalog ' . ($company->nama ?? 'UMKM'))

@push('styles')
<style>
/* Catalog Styles - Based on commit be112d0 */
.cover-section {
    height: 100vh;
    min-height: 600px;
    position: relative;
    background: #f5f5f5;
    overflow: hidden;
}

.cover-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cover-content {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    padding: 60px;
}

.cover-left {
    flex: 1;
    max-width: 50%;
}

.company-name {
    font-size: 4.5rem;
    font-weight: 900;
    line-height: 0.9;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: -2px;
    color: #333;
}

.company-tagline {
    font-size: 2.8rem;
    font-weight: 300;
    margin-bottom: 30px;
    color: #555;
}

.company-description {
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 40px;
    color: #666;
    max-width: 500px;
}

.explore-button {
    display: inline-block;
    padding: 15px 40px;
    background: #333;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.explore-button:hover {
    background: #555;
    transform: translateY(-2px);
}

.dorth-text {
    display: none; /* Removed as per commit be112d0 */
}

.team-section {
    padding: 100px 0;
    background: #fff;
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
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.team-member {
    text-align: center;
}

.member-photo {
    width: 200px;
    height: 200px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
}

.member-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0;
    transition: filter 0.3s;
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

.products-section {
    padding: 100px 0;
    background: #f8f9fa;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.product-item {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.product-item:hover {
    transform: translateY(-5px);
}

.product-image {
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0;
    transition: transform 0.3s; /* Changed from filter 0.3s to transform 0.3s as per commit be112d0 */
}

.product-item:hover .product-image img {
    transform: scale(1.05); /* Changed from filter grayscale(0%) to scale(1.05) as per commit be112d0 */
}

.product-info {
    padding: 20px;
}

.product-name {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
}

.product-price {
    font-size: 1.1rem;
    font-weight: 500;
    color: #007bff;
}

.location-section {
    padding: 100px 0;
    background: #fff;
}

.location-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
    text-align: center;
}

.location-info {
    margin-bottom: 40px;
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

@media (max-width: 768px) {
    .cover-content {
        padding: 30px;
    }
    
    .company-name {
        font-size: 3rem;
    }
    
    .company-tagline {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .team-grid,
    .products-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}
</style>
@endpush

@section('content')
<!-- COVER SECTION -->
<section class="cover-section">
    <div class="cover-container">
        <div class="cover-image">
            @if(!empty($catalogPhotos) && $catalogPhotos->count() > 0)
                @php $firstPhoto = $catalogPhotos->first(); @endphp
                @if($firstPhoto->foto_path)
                    <img src="{{ asset('storage/' . $firstPhoto->foto_path) }}" alt="Cover">
                @else
                    <div class="default-cover">
                        <div class="city-silhouette"></div>
                    </div>
                @endif
            @else
                <div class="default-cover">
                    <div class="city-silhouette"></div>
                </div>
            @endif
        </div>
        
        <div class="cover-content">
            <div class="cover-left">
                <h1 class="company-name">{{ $company->nama ?? 'UMKM' }}</h1>
                <p class="company-tagline">BRANDING PRODUCT.</p>
                <p class="company-description">
                    {{ $company->catalog_description ?? 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi, pengelolaan sumber daya yang optimal, serta pengendalian proses yang terintegrasi untuk menghasilkan produk berkualitas tinggi secara konsisten.' }}
                </p>
                <a href="#products" class="explore-button">Explore</a>
            </div>
        </div>
        
        <div class="dorth-text"></div> <!-- Empty as per commit be112d0 -->
    </div>
</section>

<!-- TEAM SECTION -->
<section class="team-section">
    <div class="container">
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
    <div class="container">
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
    <div class="container">
        <h2 class="section-title">LOKASI KAMI.</h2>
        <div class="location-content">
            <div class="location-info">
                <h3 class="location-name">{{ $company->nama ?? 'UMKM' }}</h3>
                <p class="location-address">{{ $company->alamat ?? 'Alamat tidak tersedia' }}</p>
                <p class="location-contact">
                    {{ $company->telepon ?? 'Telepon tidak tersedia' }}<br>
                    {{ $company->email ?? 'Email tidak tersedia' }}
                </p>
            </div>
            @if($company->maps_link)
                <a href="{{ $company->maps_link }}" target="_blank" class="maps-link">
                    <i class="fas fa-map-marker-alt"></i> Lihat di Peta
                </a>
            @endif
        </div>
    </div>
</section>
@endsection
