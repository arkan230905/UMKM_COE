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
    transition: filter 0.3s;
}

.team-member:hover .member-photo img {
    filter: none;
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
    transition: transform 0.3s;
}

.product-item:hover .product-image img {
    transform: scale(1.05);
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

/* LOCATION SECTION */
.location-section {
    background: #fff;
    padding: 100px 0;
}

.location-header {
    margin-bottom: 60px;
}

.location-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: start;
}

.location-info h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 25px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 20px;
}

.contact-icon {
    width: 40px;
    height: 40px;
    background: #333;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.9rem;
}

.contact-text {
    flex: 1;
}

.contact-text strong {
    display: block;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #999;
    margin-bottom: 3px;
}

.contact-text span,
.contact-text a {
    font-size: 1rem;
    color: #333;
    text-decoration: none;
    line-height: 1.5;
}

.contact-text a:hover {
    color: #555;
    text-decoration: underline;
}

.location-map {
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.location-map iframe {
    display: block;
    width: 100%;
    height: 380px;
    border: 0;
}

.no-map-placeholder {
    width: 100%;
    height: 380px;
    background: #f5f5f5;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #aaa;
}

.no-map-placeholder i {
    font-size: 3rem;
    margin-bottom: 15px;
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

    .location-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }

    .location-map iframe,
    .no-map-placeholder {
        height: 260px;
    }
}
</style>

@endpush

@section('content')
@php
    $coverSection    = ($sections && $sections->isNotEmpty()) ? $sections->firstWhere('section_type', 'cover')    : null;
    $teamSection     = ($sections && $sections->isNotEmpty()) ? $sections->firstWhere('section_type', 'team')     : null;
    $productsSection = ($sections && $sections->isNotEmpty()) ? $sections->firstWhere('section_type', 'products') : null;
    $locationSection = ($sections && $sections->isNotEmpty()) ? $sections->firstWhere('section_type', 'location') : null;

    $locationData = [
        'title'     => 'LOKASI KAMI.',
        'name'      => $company->nama ?? '',
        'address'   => $company->alamat ?? '',
        'phone'     => $company->telepon ?? '',
        'email'     => $company->email ?? '',
        'maps_link' => $company->maps_link ?? '',
    ];
    if ($locationSection && $locationSection->content) $locationData = array_merge($locationData, $locationSection->content);

    $coverData = [
        'company_name'        => $company->nama ?? 'NAMA PERUSAHAAN',
        'company_tagline'     => 'BRANDING PRODUCT.',
        'company_description' => 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi, pengelolaan sumber daya yang optimal, serta pengendalian proses yang terintegrasi untuk menghasilkan produk berkualitas tinggi secara konsisten.',
        'explore_text'        => 'Explore',
    ];
    $teamData = [
        'title'       => 'THE TEAM.',
        'description' => 'Didukung oleh fullstack developer yang kompeten dan pembimbing berpengalaman, tim ini menghadirkan solusi digital terintegrasi dengan pendekatan strategis, presisi teknis, dan standar kualitas tinggi.',
        'members'     => [
            ['name'=>'Joko Susilo',    'position'=>'Direktur Utama',   'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'photo'=>''],
            ['name'=>'Sari Wulandari', 'position'=>'Manajer Produksi', 'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'photo'=>''],
        ],
    ];
    if ($coverSection && $coverSection->content) $coverData = array_merge($coverData, $coverSection->content);
    if ($teamSection  && $teamSection->content)  $teamData  = array_merge($teamData,  $teamSection->content);
@endphp

<!-- COVER SECTION -->
<section class="cover-section">
    <div class="cover-container">
        <div class="cover-image">
            @if($company && $company->foto)
                <img src="{{ asset('storage/'.$company->foto) }}" alt="{{ $company->nama }}">
            @else
                <div class="default-cover"><div class="city-silhouette"></div></div>
            @endif
        </div>
        <div class="cover-content">
            <div class="cover-left">
                <h1 class="company-name">{{ $coverData['company_name'] }}</h1>
                <h2 class="company-tagline">{{ $coverData['company_tagline'] }}</h2>
            </div>
            <div class="cover-right">
                <div class="company-info">
                    <p class="company-description">{{ $coverData['company_description'] }}</p>
                    <div class="explore-button">{{ $coverData['explore_text'] }}</div>
                </div>
            </div>
            <div class="dorth-text"></div>
        </div>
    </div>
</section>

<!-- TEAM SECTION -->
<section class="team-section">
    <div class="container">
        <div class="team-header">
            <h2 class="section-title">{{ $teamData['title'] }}</h2>
            <div class="section-line"></div>
            <p class="team-description">{{ $teamData['description'] }}</p>
        </div>
        <div class="team-content">
            <div class="team-right" style="grid-column: 1 / -1;">
                @foreach($teamData['members'] as $index => $member)
                <div class="team-member {{ $index % 2 == 0 ? 'member-left' : 'member-right' }}">
                    <div class="member-photo">
                        @if(!empty($member['photo']))
                            <img src="{{ $member['photo'] }}" alt="{{ $member['name'] }}">
                        @else
                            <img src="https://via.placeholder.com/150x150/333333/ffffff?text={{ urlencode(substr($member['name'],0,3)) }}" alt="{{ $member['name'] }}">
                        @endif
                    </div>
                    <div class="member-info">
                        <h4>{{ $member['name'] }}</h4>
                        <h5>{{ $member['position'] }}</h5>
                        <p>{{ $member['description'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTS SECTION -->
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
                        <div class="no-image"><i class="fas fa-image"></i></div>
                    @endif
                </div>
                <div class="product-info">
                    <h4>{{ $produk->nama_produk }}</h4>
                    <p>{{ Str::limit($produk->deskripsi ?: '', 80) }}</p>
                </div>
            </div>
            @empty
            <div class="no-products"><p>Belum ada produk tersedia</p></div>
            @endforelse
        </div>
    </div>
</section>

<!-- LOCATION SECTION -->
@if(!empty($locationData['name']) || !empty($locationData['address']) || !empty($locationData['phone']) || !empty($locationData['email']) || !empty($locationData['maps_link']))
<section class="location-section">
    <div class="container">
        <div class="location-header">
            <h2 class="section-title">{{ $locationData['title'] }}</h2>
            <div class="section-line"></div>
        </div>
        <div class="location-content">
            <div class="location-info">
                @if(!empty($locationData['name']))
                <h3>{{ $locationData['name'] }}</h3>
                @endif

                @if(!empty($locationData['address']))
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-text">
                        <strong>Alamat</strong>
                        <span>{{ $locationData['address'] }}</span>
                    </div>
                </div>
                @endif

                @if(!empty($locationData['phone']))
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-phone"></i></div>
                    <div class="contact-text">
                        <strong>Telepon</strong>
                        <a href="tel:{{ $locationData['phone'] }}">{{ $locationData['phone'] }}</a>
                    </div>
                </div>
                @endif

                @if(!empty($locationData['email']))
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-text">
                        <strong>Email</strong>
                        <a href="mailto:{{ $locationData['email'] }}">{{ $locationData['email'] }}</a>
                    </div>
                </div>
                @endif

                @if(!empty($locationData['maps_link']))
                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-map"></i></div>
                    <div class="contact-text">
                        <strong>Google Maps</strong>
                        <a href="{{ $locationData['maps_link'] }}" target="_blank" rel="noopener noreferrer">Lihat di Google Maps</a>
                    </div>
                </div>
                @endif
            </div>

            <div class="location-map">
                @if(!empty($locationData['maps_link']) && str_contains($locationData['maps_link'], 'maps/embed'))
                    <iframe
                        src="{{ $locationData['maps_link'] }}"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                @else
                    <div class="no-map-placeholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>Peta belum tersedia</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

<!-- TOMBOL BELI -->
<section class="cta-section">
    <div class="container">
        <div class="text-center">
            @if(auth()->check())
                @if(auth()->user()->role === 'pelanggan')
                    <button class="btn-beli" onclick="window.location.href='{{ route('pelanggan.dashboard') }}'">
                        Masuk ke Dashboard
                    </button>
                @else
                    <p class="text-white mb-3">Anda sudah login sebagai {{ auth()->user()->role }}. Silakan logout untuk login sebagai pelanggan.</p>
                    <a href="{{ route('logout') }}" class="btn btn-danger btn-lg" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @endif
            @else
                <button class="btn-beli" onclick="window.location.href='{{ route('pelanggan.login') }}'">
                    klik disini untuk membeli
                </button>
            @endif
        </div>
    </div>
</section>

@endsection