@extends('layouts.catalog')

@section('title', 'E-Catalog ' . ($company->nama ?? 'UMKM'))

@push('styles')
<style>
/* RESET & BASE */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* COVER SECTION */
.cover-section {
    height: 100vh;
    min-height: 600px;
    position: relative;
    background: #f5f5f5;
    overflow: hidden;
}

.cover-container {
    height: 100%;
    position: relative;
}

.cover-image {
    position: absolute;
    top: 0;
    right: 0;
    width: 70%;
    height: 100%;
    z-index: 1;
}

.cover-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(100%) contrast(1.2);
}

.default-cover {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    position: relative;
}

.city-silhouette {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 400"><path d="M0,400 L0,300 L100,300 L100,200 L200,200 L200,250 L300,250 L300,150 L400,150 L400,180 L500,180 L500,120 L600,120 L600,160 L700,160 L700,100 L800,100 L800,140 L900,140 L900,80 L1000,80 L1000,200 L1100,200 L1100,300 L1200,300 L1200,400 Z" fill="%23000000" opacity="0.3"/></svg>') no-repeat center bottom;
    background-size: cover;
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
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #333;
    margin: 0;
}

.cover-right {
    flex: 1;
    max-width: 40%;
    margin-left: auto;
    padding-left: 40px;
}

.company-info {
    background: rgba(255, 255, 255, 0.95);
    padding: 30px;
    border-radius: 0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.company-description {
    font-size: 1rem;
    line-height: 1.6;
    color: #666;
    margin-bottom: 25px;
    text-align: justify;
}

.explore-button {
    display: inline-block;
    padding: 12px 25px;
    background: #333;
    color: white;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    font-size: 0.9rem;
}

.explore-button:hover {
    background: #555;
    transform: translateY(-2px);
}

.dorth-text {
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%) rotate(90deg);
    font-size: 2rem;
    font-weight: 300;
    letter-spacing: 8px;
    color: rgba(255, 255, 255, 0.8);
    z-index: 3;
}

/* TEAM SECTION */
.team-section {
    background: #fff;
    padding: 100px 0;
}

.team-header {
    margin-bottom: 80px;
}

.section-title {
    font-size: 3rem;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #333;
    margin-bottom: 20px;
}

.section-line {
    width: 100px;
    height: 3px;
    background: #333;
    margin-bottom: 30px;
}

.team-description {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    line-height: 1.6;
}

.team-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: start;
}

.about-team h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #333;
}

.about-team p {
    font-size: 1rem;
    line-height: 1.6;
    color: #666;
    margin-bottom: 40px;
}

.team-stats {
    display: flex;
    gap: 30px;
}

.stat-item h4 {
    font-size: 2.5rem;
    font-weight: 900;
    color: #333;
    margin-bottom: 5px;
}

.stat-item p {
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.team-member {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
    align-items: flex-start;
}

.member-left {
    flex-direction: row;
}

.member-right {
    flex-direction: row-reverse;
    text-align: right;
}

.member-photo {
    width: 120px;
    height: 120px;
    flex-shrink: 0;
}

.member-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0;
    filter: grayscale(100%);
    transition: filter 0.3s;
}

.team-member:hover .member-photo img {
    filter: grayscale(0%);
}

.member-info {
    flex: 1;
}

.member-info h4 {
    font-size: 1.3rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.member-info h5 {
    font-size: 1rem;
    font-weight: 500;
    color: #666;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.member-info p {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #666;
}

/* PRODUCTS SECTION */
.products-section {
    background: #f8f9fa;
    padding: 100px 0;
}

.products-header {
    text-align: center;
    margin-bottom: 80px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.product-item {
    background: white;
    border-radius: 0;
    overflow: hidden;
    transition: transform 0.3s;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.product-item:hover {
    transform: translateY(-5px);
}

.product-image {
    height: 200px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(100%);
    transition: filter 0.3s;
}

.product-item:hover .product-image img {
    filter: grayscale(0%);
}

.product-image .no-image {
    color: #ccc;
    font-size: 2rem;
}

.product-info {
    padding: 25px;
}

.product-info h4 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.product-info p {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #666;
    margin: 0;
}

/* CTA SECTION */
.cta-section {
    background: #333;
    padding: 60px 0;
}

.btn-beli {
    background: #fff;
    color: #333;
    border: none;
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-beli:hover {
    background: #f0f0f0;
    transform: translateY(-2px);
}

.no-products {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .cover-content {
        flex-direction: column;
        padding: 40px 20px;
        text-align: center;
    }
    
    .cover-left, .cover-right {
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    
    .cover-right {
        margin-top: 40px;
    }
    
    .cover-image {
        width: 100%;
        opacity: 0.3;
    }
    
    .company-name {
        font-size: 2.5rem;
    }
    
    .company-tagline {
        font-size: 1.8rem;
    }
    
    .dorth-text {
        display: none;
    }
    
    .team-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .team-member {
        flex-direction: column !important;
        text-align: center !important;
    }
    
    .member-photo {
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
}
</style>
@endpush

@section('content')

@if($company && $sections && $sections->isNotEmpty())
    @php
        $coverSection = $sections->firstWhere('section_type', 'cover');
        $teamSection = $sections->firstWhere('section_type', 'team');
        $productsSection = $sections->firstWhere('section_type', 'products');
        $locationSection = $sections->firstWhere('section_type', 'location');
    @endphp

    <!-- COVER SECTION -->
    @if($coverSection)
    <section class="cover-section">
        <div class="cover-container">
            <div class="cover-image">
                @if($company->foto)
                    <img src="{{ asset('storage/'.$company->foto) }}" alt="{{ $company->nama }}">
                @else
                    <div class="default-cover">
                        <div class="city-silhouette"></div>
                    </div>
                @endif
            </div>
            <div class="cover-content">
                <div class="cover-left">
                    <h1 class="company-name">{{ $coverSection->content['company_name'] ?? $company->nama }}</h1>
                    <h2 class="company-tagline">{{ $coverSection->content['company_tagline'] ?? 'BRANDING PRODUCT.' }}</h2>
                </div>
                <div class="cover-right">
                    <div class="company-info">
                        <p class="company-description">{{ $coverSection->content['company_description'] ?? $company->catalog_description }}</p>
                        <div class="explore-button">
                            {{ $coverSection->content['explore_text'] ?? 'Explore' }}
                        </div>
                    </div>
                </div>
                <div class="dorth-text">DORTH</div>
            </div>
        </div>
    </section>
    @endif

    <!-- TEAM SECTION -->
    @if($teamSection && isset($teamSection->content['members']))
    <section class="team-section">
        <div class="container">
            <div class="team-header">
                <h2 class="section-title">{{ $teamSection->title ?? 'THE TEAM.' }}</h2>
                <div class="section-line"></div>
                @if(isset($teamSection->content['description']))
                <p class="team-description">{{ $teamSection->content['description'] }}</p>
                @endif
            </div>
            
            <div class="team-content">
                <div class="team-left">
                    <div class="about-team">
                        <h3>About Team</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem.</p>
                        
                        <div class="team-stats">
                            <div class="stat-item">
                                <h4>{{ count($teamSection->content['members']) }}</h4>
                                <p>Team Members</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="team-right">
                    @foreach($teamSection->content['members'] as $index => $member)
                    <div class="team-member {{ $index % 2 == 0 ? 'member-left' : 'member-right' }}">
                        <div class="member-photo">
                            <img src="{{ $member['photo'] ?? 'https://via.placeholder.com/150x150/333333/ffffff?text=Team' }}" alt="{{ $member['name'] ?? 'Team Member' }}">
                        </div>
                        <div class="member-info">
                            <h4>{{ $member['name'] ?? 'Nama Anggota' }}</h4>
                            <h5>{{ $member['position'] ?? 'Jabatan' }}</h5>
                            <p>{{ $member['description'] ?? 'Deskripsi anggota tim...' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- PRODUCTS SECTION -->
    @if($productsSection)
    <section class="products-section">
        <div class="container">
            <div class="products-header">
                <h2 class="section-title">{{ $productsSection->title ?? 'PRODUCT MATERIAL.' }}</h2>
                <div class="section-line"></div>
            </div>
            
            <div class="products-grid">
                @forelse($produks->take(8) as $produk)
                <div class="product-item">
                    <div class="product-image">
                        @if($produk->foto)
                            <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama_produk }}">
                        @else
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </div>
                    <div class="product-info">
                        <h4>{{ $produk->nama_produk }}</h4>
                        <p>{{ Str::limit($produk->deskripsi ?: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor.', 80) }}</p>
                    </div>
                </div>
                @empty
                <div class="no-products">
                    <p>Belum ada produk tersedia</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>
    @endif

@else
    <!-- DEFAULT FALLBACK -->
    <section class="cover-section">
        <div class="cover-container">
            <div class="cover-image">
                @if($company && $company->foto)
                    <img src="{{ asset('storage/'.$company->foto) }}" alt="{{ $company->nama }}">
                @else
                    <div class="default-cover">
                        <div class="city-silhouette"></div>
                    </div>
                @endif
            </div>
            <div class="cover-content">
                <div class="cover-left">
                    <h1 class="company-name">{{ $company->nama ?? 'NAMA PERUSAHAAN' }}</h1>
                    <h2 class="company-tagline">BRANDING PRODUCT.</h2>
                </div>
                <div class="cover-right">
                    <div class="company-info">
                        <p class="company-description">{{ $company->catalog_description ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem.' }}</p>
                        <div class="explore-button">Explore</div>
                    </div>
                </div>
                <div class="dorth-text">DORTH</div>
            </div>
        </div>
    </section>

    <!-- DEFAULT TEAM SECTION -->
    <section class="team-section">
        <div class="container">
            <div class="team-header">
                <h2 class="section-title">THE TEAM.</h2>
                <div class="section-line"></div>
                <p class="team-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor.</p>
            </div>
            
            <div class="team-content">
                <div class="team-left">
                    <div class="about-team">
                        <h3>About Team</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                        
                        <div class="team-stats">
                            <div class="stat-item">
                                <h4>2</h4>
                                <p>Team Members</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="team-right">
                    <div class="team-member member-left">
                        <div class="member-photo">
                            <img src="https://via.placeholder.com/150x150/333333/ffffff?text=CEO" alt="CEO">
                        </div>
                        <div class="member-info">
                            <h4>Joko Susilo</h4>
                            <h5>Direktur Utama</h5>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                        </div>
                    </div>
                    
                    <div class="team-member member-right">
                        <div class="member-photo">
                            <img src="https://via.placeholder.com/150x150/666666/ffffff?text=MGR" alt="Manager">
                        </div>
                        <div class="member-info">
                            <h4>Sari Wulandari</h4>
                            <h5>Manajer Produksi</h5>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DEFAULT PRODUCTS SECTION -->
    <section class="products-section">
        <div class="container">
            <div class="products-header">
                <h2 class="section-title">PRODUCT MATERIAL.</h2>
                <div class="section-line"></div>
            </div>
            
            <div class="products-grid">
                @forelse($produks->take(8) as $produk)
                <div class="product-item">
                    <div class="product-image">
                        @if($produk->foto)
                            <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama_produk }}">
                        @else
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </div>
                    <div class="product-info">
                        <h4>{{ $produk->nama_produk }}</h4>
                        <p>{{ Str::limit($produk->deskripsi ?: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 80) }}</p>
                    </div>
                </div>
                @empty
                <div class="no-products">
                    <p>Belum ada produk tersedia</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>
@endif

<!-- TOMBOL BELI -->
<section class="cta-section">
    <div class="container">
        <div class="text-center">
            <button class="btn-beli" onclick="window.location.href='/pelanggan/login'">
                klik disini untuk membeli
            </button>
        </div>
    </div>
</section>

@endsection