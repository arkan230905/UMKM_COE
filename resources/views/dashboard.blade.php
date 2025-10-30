@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #1e1e2f; min-height: 100vh;">

    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <h1 class="text-white">Dashboard</h1>

        <div class="dropdown">
            <button class="btn btn-dark dropdown-toggle d-flex align-items-center" 
                    type="button" id="dropdownProfile" data-bs-toggle="dropdown" 
                    aria-expanded="false" style="border-radius: 10px; background-color: #2c2c3e; border: none;">
                <i class="bi bi-person-circle fs-5 me-2"></i>
                <span>{{ Auth::user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownProfile" style="background-color: #2c2c3e;">
                <li class="dropdown-header text-white">
                    <strong>{{ Auth::user()->name }}</strong><br>
                    <small>{{ Auth::user()->email }}</small>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a href="{{ route('tentang-perusahaan') }}" class="dropdown-item text-white">
                        <i class="bi bi-building me-2"></i> Tentang Perusahaan
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <h4 class="text-white mb-3 px-2">Master Data</h4>
    <div class="row g-4 px-2">
        @php
            $masterData = [
                ['title'=>'Pegawai','count'=>$totalPegawai,'icon'=>'bi-people','route'=>'master-data.pegawai.index'],
                ['title'=>'Presensi','count'=>$totalPresensi,'icon'=>'bi-clock','route'=>'master-data.presensi.index'],
                ['title'=>'Produk','count'=>$totalProduk,'icon'=>'bi-box-seam','route'=>'master-data.produk.index'],
                ['title'=>'Vendor','count'=>$totalVendor,'icon'=>'bi-shop','route'=>'master-data.vendor.index'],
                ['title'=>'Bahan Baku','count'=>$totalBahanBaku,'icon'=>'bi-droplet','route'=>'master-data.bahan-baku.index'],
                ['title'=>'BOM','count'=>$totalBOM,'icon'=>'bi-gear','route'=>'master-data.bom.index'],
            ];
        @endphp

        @foreach($masterData as $data)
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route($data['route']) }}" class="text-decoration-none">
                <div class="card shadow hover-card text-white text-center" style="background-color: #2c2c3e; border-radius: 15px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="bi {{ $data['icon'] }} fs-2 mb-2 opacity-75"></i>
                        <h6 class="card-title">{{ $data['title'] }}</h6>
                        <h3 class="card-text">{{ $data['count'] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <h4 class="text-white mt-5 mb-3 px-2">Transaksi</h4>
    <div class="row g-4 px-2">
        @php
            $transaksiData = [
                ['title'=>'Pembelian','count'=>$totalPembelian,'icon'=>'bi-cart4','route'=>'transaksi.pembelian.index'],
                ['title'=>'Penjualan','count'=>$totalPenjualan,'icon'=>'bi-currency-dollar','route'=>'transaksi.penjualan.index'],
                ['title'=>'Retur','count'=>$totalRetur,'icon'=>'bi-arrow-counterclockwise','route'=>'transaksi.retur.index'],
            ];
        @endphp
        @foreach($transaksiData as $data)
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route($data['route']) }}" class="text-decoration-none">
                <div class="card shadow hover-card text-white text-center" style="background-color: #2c2c3e; border-radius: 15px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="bi {{ $data['icon'] }} fs-2 mb-2 opacity-75"></i>
                        <h6 class="card-title">{{ $data['title'] }}</h6>
                        <h3 class="card-text">{{ $data['count'] }}</h3>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <h4 class="text-white mt-5 mb-3 px-2">Laporan</h4>
    <div class="row g-4 px-2">
        @php
            $laporanData = [
                ['title'=>'Laporan Penjualan','icon'=>'bi-graph-up','route'=>'laporan.penjualan'],
                ['title'=>'Laporan Pembelian','icon'=>'bi-receipt','route'=>'laporan.pembelian'],
                ['title'=>'Laporan Stok','icon'=>'bi-clipboard-data','route'=>'laporan.stok'],
            ];
        @endphp
        @foreach($laporanData as $data)
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route($data['route']) }}" class="text-decoration-none">
                <div class="card shadow hover-card text-white text-center" style="background-color: #2c2c3e; border-radius: 15px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="bi {{ $data['icon'] }} fs-2 mb-2 opacity-75"></i>
                        <h6 class="card-title">{{ $data['title'] }}</h6>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

</div>

<style>
.hover-card:hover { transform: translateY(-5px); transition: 0.3s; cursor: pointer; box-shadow: 0 12px 25px rgba(0,0,0,0.5); }
.card-title { letter-spacing: 0.5px; margin-bottom: 0; }
.card-text { font-weight: bold; margin-top: 5px; }
.dropdown-menu { border: none; border-radius: 10px; padding: 0.5rem; }
.dropdown-item:hover { background-color: #3a3a50 !important; }
h1,h4,h6 { color: #fff; }
 </style>
 @endsection
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalPegawai }}</p>
=======
<div class="container-fluid py-4" style="background-color: #1e1e2f; min-height: 100vh;">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <h1 class="text-white">Dashboard</h1>

        <!-- Dropdown Profile -->
        <div class="dropdown">
            <button class="btn btn-dark dropdown-toggle d-flex align-items-center" 
                    type="button" 
                    id="dropdownProfile" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    style="border-radius: 10px; background-color: #2c2c3e; border: none;">
                <i class="bi bi-person-circle fs-5 me-2"></i>
                <span>{{ Auth::user()->name }}</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownProfile" style="background-color: #2c2c3e;">
                <li class="dropdown-header text-white">
                    <strong>{{ Auth::user()->name }}</strong><br>
                    <small>{{ Auth::user()->email }}</small>
                </li>
                <li><hr class="dropdown-divider"></li>

                <!-- 🏢 Tentang Perusahaan -->
                <li>
<<<<<<< HEAD
                    <a href="{{ route('tentang.perusahaan') }}" class="dropdown-item text-white">
=======
                    <a href="{{ route('tentang-perusahaan') }}" class="dropdown-item text-white">
>>>>>>> 68de30b (pembuatan bop dan satuan)
                        <i class="bi bi-building me-2"></i> Tentang Perusahaan
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <!-- 🚪 Logout -->
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <!-- Grid Cards: Master Data -->
    <h4 class="text-white mb-3 px-2">Master Data</h4>
    <div class="row g-4 px-2">
        @php
            $masterData = [
                ['title'=>'Pegawai','count'=>$totalPegawai,'icon'=>'bi-people','route'=>'master-data.pegawai.index'],
                ['title'=>'Presensi','count'=>$totalPresensi,'icon'=>'bi-clock','route'=>'master-data.presensi.index'],
                ['title'=>'Produk','count'=>$totalProduk,'icon'=>'bi-box-seam','route'=>'master-data.produk.index'],
                ['title'=>'Vendor','count'=>$totalVendor,'icon'=>'bi-shop','route'=>'master-data.vendor.index'],
                ['title'=>'Bahan Baku','count'=>$totalBahanBaku,'icon'=>'bi-droplet','route'=>'master-data.bahan-baku.index'],
                ['title'=>'BOM','count'=>$totalBOM,'icon'=>'bi-gear','route'=>'master-data.bom.index'],
            ];
        @endphp

        @foreach($masterData as $data)
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route($data['route']) }}" class="text-decoration-none">
                <div class="card shadow hover-card text-white text-center" 
                     style="background-color: #2c2c3e; border-radius: 15px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="bi {{ $data['icon'] }} fs-2 mb-2 opacity-75"></i>
                        <h6 class="card-title">{{ $data['title'] }}</h6>
                        <h3 class="card-text">{{ $data['count'] }}</h3>
>>>>>>> 73ecd34c0ff44e1b46e8fcae2de615861d360f74
                    </div>
                </div>
            </div>

<<<<<<< HEAD
            <!-- Total Produk -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Produk</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalProduk }}</p>
=======
    <!-- Grid Cards: Transaksi -->
    <h4 class="text-white mt-5 mb-3 px-2">Transaksi</h4>
    <div class="row g-4 px-2">
        @php
            $transaksiData = [
                ['title'=>'Pembelian','count'=>$totalPembelian,'icon'=>'bi-cart4','route'=>'transaksi.pembelian.index'],
                ['title'=>'Penjualan','count'=>$totalPenjualan,'icon'=>'bi-currency-dollar','route'=>'transaksi.penjualan.index'],
                ['title'=>'Retur','count'=>$totalRetur,'icon'=>'bi-arrow-counterclockwise','route'=>'transaksi.retur.index'],
            ];
        @endphp

        @foreach($transaksiData as $data)
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route($data['route']) }}" class="text-decoration-none">
                <div class="card shadow hover-card text-white text-center" 
                     style="background-color: #2c2c3e; border-radius: 15px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="bi {{ $data['icon'] }} fs-2 mb-2 opacity-75"></i>
                        <h6 class="card-title">{{ $data['title'] }}</h6>
                        <h3 class="card-text">{{ $data['count'] }}</h3>
>>>>>>> 73ecd34c0ff44e1b46e8fcae2de615861d360f74
                    </div>
                </div>
            </div>

<<<<<<< HEAD
            <!-- Total Vendor -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Vendor</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalVendor }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Aset -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Aset</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalAset }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Bahan Baku -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
    </div>

    <!-- Grid Cards: Laporan -->
    <h4 class="text-white mt-5 mb-3 px-2">Laporan</h4>
    <div class="row g-4 px-2">
        @php
            $laporanData = [
                ['title'=>'Laporan Penjualan','icon'=>'bi-graph-up','route'=>'laporan.penjualan'],
                ['title'=>'Laporan Pembelian','icon'=>'bi-receipt','route'=>'laporan.pembelian'],
                ['title'=>'Laporan Stok','icon'=>'bi-clipboard-data','route'=>'laporan.stok'],
            ];
        @endphp

        @foreach($laporanData as $data)
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route($data['route']) }}" class="text-decoration-none">
                <div class="card shadow hover-card text-white text-center" 
                     style="background-color: #2c2c3e; border-radius: 15px; min-height: 120px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <i class="bi {{ $data['icon'] }} fs-2 mb-2 opacity-75"></i>
                        <h6 class="card-title">{{ $data['title'] }}</h6>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

</div>

<style>
.hover-card:hover {
    transform: translateY(-5px);
    transition: 0.3s;
    cursor: pointer;
    box-shadow: 0 12px 25px rgba(0,0,0,0.5);
}
.card-title {
    letter-spacing: 0.5px;
    margin-bottom: 0;
}
.card-text {
    font-weight: bold;
    margin-top: 5px;
}
.dropdown-menu {
    border: none;
    border-radius: 10px;
    padding: 0.5rem;
}
.dropdown-item:hover {
    background-color: #3a3a50 !important;
}
h1,h4,h6 { color: #fff; }
</style>
@endsection
