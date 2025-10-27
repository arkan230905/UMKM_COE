<!DOCTYPE html>
<<<<<<< HEAD
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
=======
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .nav-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            opacity: 0.7;
        }
        .content {
            flex-grow: 1;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="nav-title">Menu</div>
>>>>>>> 73ecd34c0ff44e1b46e8fcae2de615861d360f74

        <title>{{ config('app.name', 'Laravel') }}</title>

<<<<<<< HEAD
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                display: flex;
            }
            .sidebar {
                width: 200px;
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                background-color: #2c3e50;
                overflow-y: auto;
            }
            main {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
            .nav-link {
                color: white !important;
                padding: 10px 15px;
            }
            .nav-link:hover {
                background-color: #34495e;
                border-radius: 5px;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        @include('layouts.sidebar')
        
        <div style="margin-left: 200px; width: calc(100% - 200px);">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
=======
            <!-- Master Data -->
            <div class="nav-section">
                <div class="nav-title">Master Data</div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.pegawai.index') }}"><i class="bi bi-people me-2"></i> Pegawai</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.presensi.index') }}"><i class="bi bi-calendar-check me-2"></i> Presensi</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.produk.index') }}"><i class="bi bi-box-seam me-2"></i> Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.vendor.index') }}"><i class="bi bi-building me-2"></i> Vendor</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.coa.index') }}"><i class="bi bi-journal-text me-2"></i> COA</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.bahan-baku.index') }}"><i class="bi bi-droplet-half me-2"></i> Bahan Baku</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.bom.index') }}"><i class="bi bi-diagram-3 me-2"></i> BOM</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('master-data.bop.index') }}"><i class="bi bi-gear me-2"></i> BOP</a></li>
                </ul>
            </div>

            <!-- Transaksi -->
            <div class="nav-section">
                <div class="nav-title">Transaksi</div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="{{ route('transaksi.pembelian.index') }}"><i class="bi bi-cart-check me-2"></i> Pembelian</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('transaksi.penjualan.index') }}"><i class="bi bi-cash-coin me-2"></i> Penjualan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('transaksi.penggajian.index') }}"><i class="bi bi-wallet2 me-2"></i> Penggajian</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('transaksi.retur.index') }}"><i class="bi bi-arrow-counterclockwise me-2"></i> Retur</a></li>
                </ul>
            </div>

            <!-- Laporan -->
            <div class="nav-section">
                <div class="nav-title">Laporan</div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="{{ route('laporan.penjualan') }}">Laporan Penjualan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('laporan.pembelian') }}">Laporan Pembelian</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('laporan.stok') }}">Laporan Stok</a></li>
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
>>>>>>> 73ecd34c0ff44e1b46e8fcae2de615861d360f74
