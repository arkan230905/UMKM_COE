@extends('layouts.catalog')

@section('title', 'E-Catalog ' . ($company->nama ?? 'UMKM'))

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold">{{ $company->nama ?? 'UMKM' }}</h1>
                <p class="lead">BRANDING PRODUCT.</p>
                <p class="text-muted">
                    {{ $company->catalog_description ?? 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi.' }}
                </p>
                <a href="#products" class="btn btn-primary btn-lg">Explore Products</a>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">THE TEAM.</h2>
            <div class="row">
                <div class="col-md-6 text-center mb-4">
                    <div class="team-member">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; transition: filter 0.3s;">
                        <h4>Joko Susilo</h4>
                        <p class="text-muted">Direktur Utama</p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                    </div>
                </div>
                <div class="col-md-6 text-center mb-4">
                    <div class="team-member">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; transition: filter 0.3s;">
                        <h4>Sari Wulandari</h4>
                        <p class="text-muted">Manajer Produksi</p>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5" id="products">
        <div class="container">
            <h2 class="text-center mb-5">PRODUCT MATERIAL.</h2>
            <div class="row">
                @forelse($produks as $produk)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 product-card" style="transition: transform 0.3s;">
                            <div class="product-image" style="height: 200px; overflow: hidden;">
                                @if($produk->foto_path)
                                    <img src="{{ asset('storage/' . $produk->foto_path) }}" alt="{{ $produk->nama_produk }}" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                                @else
                                    <img src="{{ asset('images/default-avatar.png') }}" alt="{{ $produk->nama_produk }}" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                                @endif
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $produk->nama_produk }}</h5>
                                <p class="card-text">
                                    <strong class="text-primary">Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">No products available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">LOKASI KAMI.</h2>
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h3>{{ $company->nama ?? 'UMKM' }}</h3>
                    <p class="mb-2">{{ $company->alamat ?? 'Alamat tidak tersedia' }}</p>
                    <p class="mb-3">
                        {{ $company->telepon ?? 'Telepon tidak tersedia' }}<br>
                        {{ $company->email ?? 'Email tidak tersedia' }}
                    </p>
                    @if($company->maps_link)
                        <a href="{{ $company->maps_link }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-map-marker-alt"></i> Lihat di Peta
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Apply be112d0 key changes */
.team-member img:hover {
    filter: none; /* Changed from grayscale(0%) to none as per commit be112d0 */
}

.product-card:hover .product-image img {
    transform: scale(1.05); /* Changed from filter grayscale(0%) to scale(1.05) as per commit be112d0 */
}

.product-card:hover {
    transform: translateY(-5px);
}

/* No DORTH text - removed as per commit be112d0 */
</style>
@endsection
