@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #1e1e2f; min-height: 100vh;">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <h1 class="text-white">Dashboard</h1>
        <small class="text-white">Selamat datang, {{ Auth::user()->name }}!</small>
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

    <!-- Grid Cards: Transaksi -->
    <h4 class="text-white mt-5 mb-3 px-2">Transaksi</h4>
    <div class="row g-4 px-2">
        @php
            $transaksiData = [
                ['title'=>'Pembelian','count'=>$totalPembelian,'icon'=>'bi-cart4','route'=>'transaksi.pembelian.index'],
                ['title'=>'Penjualan','count'=>$totalPenjualan,'icon'=>'bi-currency-dollar','route'=>'transaksi.penjualan.index'],
                ['title'=>'Retur','count'=>round($totalRetur),'icon'=>'bi-arrow-counterclockwise','route'=>'transaksi.retur.index'],
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
h1,h4,h6 { color: #fff; }
</style>
@endsection
