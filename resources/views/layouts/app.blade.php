<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #1e1e2f;
            color: #fff;
        }
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background-color: #111129;
            padding: 1rem;
        }
        .sidebar h5 {
            color: #fff;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.2rem;
        }
        .sidebar .nav-link:hover {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
        }
        .sidebar .nav-section {
            margin-top: 1.5rem;
        }
        .content {
            flex-grow: 1;
            padding: 2rem;
        }
        .nav-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
        <div class="nav-title">Menu</div>

            <!-- Dashboard -->
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
            </ul>

            <!-- Master Data -->
            <div class="nav-section">
                <div class="nav-title">Master Data</div>
                <ul class="nav flex-column">
                <li class="nav-item mb-2">
            <a href="{{ route('master-data.pegawai.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-people me-2"></i> Pegawai
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.presensi.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-calendar-check me-2"></i> Presensi
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.produk.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-box-seam me-2"></i> Produk
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.vendor.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-building me-2"></i> Vendor
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.coa.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-journal-text me-2"></i> COA
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.bahan-baku.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-droplet-half me-2"></i> Bahan Baku
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.bom.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-diagram-3 me-2"></i> BOM
            </a>
        </li>
                </ul>
            </div>

            <!-- Transaksi -->
            <div class="nav-section">
                <div class="nav-title">Transaksi</div>
                <ul class="nav flex-column">
                <li class="nav-item mb-2">
            <a href="{{ route('transaksi.pembelian.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-cart-check me-2"></i> Pembelian
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.penjualan.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-cash-coin me-2"></i> Penjualan
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.penggajian.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-wallet2 me-2"></i> Penggajian
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.retur.index') }}" class="nav-link text-white d-flex align-items-center">
                <i class="bi bi-arrow-counterclockwise me-2"></i> Retur
            </a>
        </li>
                </ul>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
