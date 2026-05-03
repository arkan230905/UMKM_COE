<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Catalog {{ $company->nama ?? 'UMKM' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Catalog Styles - Based on commit be112d0 key changes */
        .catalog-hero {
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 20px;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 600px;
        }
        
        .team-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .team-member {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .member-photo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            transition: filter 0.3s ease;
        }
        
        .team-member:hover .member-photo img {
            filter: none; /* Changed from grayscale(0%) to none as per commit be112d0 */
        }
        
        .products-section {
            padding: 80px 0;
            background: white;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease; /* Changed from filter 0.3s to transform 0.3s as per commit be112d0 */
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05); /* Changed from filter grayscale(0%) to scale(1.05) as per commit be112d0 */
        }
        
        .location-section {
            padding: 80px 0;
            background: #f8f9fa;
            text-align: center;
        }
        
        /* No DORTH text - removed as per commit be112d0 */
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="catalog-hero">
        <div class="hero-content">
            <h1>{{ $company->nama ?? 'UMKM' }}</h1>
            <p>BRANDING PRODUCT.</p>
            <p>{{ $company->catalog_description ?? 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi.' }}</p>
            <a href="#products" class="btn btn-light btn-lg">Explore Products</a>
        </div>
        <!-- DORTH text removed as per commit be112d0 -->
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2 class="text-center mb-5">THE TEAM.</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member">
                    </div>
                    <h4>Joko Susilo</h4>
                    <p class="text-muted">Direktur Utama</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
                <div class="team-member">
                    <div class="member-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member">
                    </div>
                    <h4>Sari Wulandari</h4>
                    <p class="text-muted">Manajer Produksi</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <h2 class="text-center mb-5">PRODUCT MATERIAL.</h2>
            <div class="products-grid">
                @forelse($produks as $produk)
                    <div class="product-card">
                        <div class="product-image">
                            @if($produk->foto_path)
                                <img src="{{ asset('storage/' . $produk->foto_path) }}" alt="{{ $produk->nama_produk }}">
                            @else
                                <img src="{{ asset('images/default-avatar.png') }}" alt="{{ $produk->nama_produk }}">
                            @endif
                        </div>
                        <div class="p-3">
                            <h5>{{ $produk->nama_produk }}</h5>
                            <p class="text-primary fw-bold">Rp {{ number_format($produk->harga_jual ?? 0, 0, ',', '.') }}</p>
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
    <section class="location-section">
        <div class="container">
            <h2 class="mb-4">LOKASI KAMI.</h2>
            <h3>{{ $company->nama ?? 'UMKM' }}</h3>
            <p class="mb-2">{{ $company->alamat ?? 'Alamat tidak tersedia' }}</p>
            <p class="mb-3">
                {{ $company->telepon ?? 'Telepon tidak tersedia' }}<br>
                {{ $company->email ?? 'Email tidak tersedia' }}
            </p>
            @if($company->maps_link)
                <a href="{{ $company->maps_link }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-map-marker-alt"></i> Lihat di Peta
                </a>
            @endif
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
