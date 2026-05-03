<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Catalog {{ $company->nama ?? 'UMKM' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* BE112D0 EXACT DESIGN */
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

        /* COVER SECTION - BE112D0 */
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
            text-decoration: none;
        }

        .explore-button:hover {
            background: #555;
            transform: translateY(-2px);
        }

        .dorth-text {
            display: none; /* REMOVED AS PER BE112D0 */
        }

        /* TEAM SECTION - BE112D0 */
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
            filter: none; /* BE112D0: Changed from grayscale(0%) to none */
        }

        .member-info {
            padding: 20px;
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

        /* PRODUCTS SECTION - BE112D0 */
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
            border-radius: 0;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
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
            transition: transform 0.3s; /* BE112D0: Changed from filter 0.3s to transform 0.3s */
        }

        .product-item:hover .product-image img {
            transform: scale(1.05); /* BE112D0: Changed from filter grayscale(0%) to scale(1.05) */
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
            font-size: 1.1rem;
            font-weight: 500;
            color: #007bff;
        }

        /* LOCATION SECTION - BE112D0 */
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
            border-radius: 0;
            font-weight: 500;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
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
</head>
<body>
    <!-- COVER SECTION - EXACT BE112D0 -->
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
                </div>
                <div class="cover-right">
                    <div class="company-info">
                        <p class="company-description">
                            {{ $company->catalog_description ?? 'Perusahaan manufaktur COE yang berfokus pada efisiensi biaya produksi, pengelolaan sumber daya yang optimal, serta pengendalian proses yang terintegrasi untuk menghasilkan produk berkualitas tinggi secara konsisten.' }}
                        </p>
                        <a href="#products" class="explore-button">Explore</a>
                    </div>
                </div>
            </div>
            <div class="dorth-text"></div> <!-- BE112D0: Empty DORTH text -->
        </div>
    </section>

    <!-- TEAM SECTION - EXACT BE112D0 -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">THE TEAM.</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Joko Susilo</h3>
                        <p class="member-position">Direktur Utama</p>
                        <p class="member-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-photo">
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Sari Wulandari</h3>
                        <p class="member-position">Manajer Produksi</p>
                        <p class="member-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCTS SECTION - EXACT BE112D0 -->
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

    <!-- LOCATION SECTION - EXACT BE112D0 -->
    <section class="location-section">
        <div class="container">
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
                        Lihat di Peta
                    </a>
                @endif
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
