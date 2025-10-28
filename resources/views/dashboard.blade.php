@extends('layouts.app')

@section('content')
<div class="d-flex" style="min-height: 100vh;">

    <!-- ========== SIDEBAR ========== -->
    <div class="sidebar p-3">
        <h4 class="text-white fw-bold mb-4">UMKM COE</h4>

        <!-- DASHBOARD -->
        <ul class="nav flex-column mb-4">
            <li class="nav-item mb-1">
                <a href="{{ route('dashboard') }}" 
                   class="nav-link text-white rounded d-flex align-items-center {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
        </ul>

        <!-- MASTER DATA -->
        <span class="text-white small fw-semibold">MASTER DATA</span>
        <ul class="nav flex-column mb-4 mt-2">
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.pegawai.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill me-2"></i> Pegawai</span>
                    <span class="badge bg-primary">{{ $totalPegawai ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.presensi.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-check me-2"></i> Presensi</span>
                    <span class="badge bg-success">{{ $totalPresensi ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.produk.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-box-seam me-2"></i> Produk</span>
                    <span class="badge bg-warning text-dark">{{ $totalProduk ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.vendor.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-truck me-2"></i> Vendor</span>
                    <span class="badge bg-info text-dark">{{ $totalVendor ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.bahan-baku.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-basket3 me-2"></i> Bahan Baku</span>
                    <span class="badge bg-secondary">{{ $totalBahanBaku ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.satuan.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-upc-scan me-2"></i> Satuan</span>
                    <span class="badge bg-dark">{{ $totalSatuan ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.coa.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-check me-2"></i> COA</span>
                    <span class="badge bg-danger">{{ $totalCOA ?? 0 }}</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.bop.index') }}" class="nav-link text-white">
                    <i class="bi bi-currency-dollar me-2"></i> BOP
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="{{ route('master-data.bom.index') }}" class="nav-link text-white">
                    <i class="bi bi-diagram-3 me-2"></i> BOM
                </a>
            </li>
        </ul>

        <!-- TRANSAKSI -->
        <span class="text-white small fw-semibold">TRANSAKSI</span>
        <ul class="nav flex-column mb-4 mt-2">
            <li><a href="{{ route('transaksi.produksi.index') }}" class="nav-link text-white"><i class="bi bi-diagram-3 me-2"></i> Produksi</a></li>
            <li><a href="{{ route('transaksi.pembelian.index') }}" class="nav-link text-white"><i class="bi bi-cart me-2"></i> Pembelian</a></li>
            <li><a href="{{ route('transaksi.penjualan.index') }}" class="nav-link text-white"><i class="bi bi-currency-dollar me-2"></i> Penjualan</a></li>
            <li><a href="{{ route('transaksi.retur.index') }}" class="nav-link text-white"><i class="bi bi-arrow-counterclockwise me-2"></i> Retur</a></li>
            <li><a href="{{ route('transaksi.penggajian.index') }}" class="nav-link text-white"><i class="bi bi-wallet2 me-2"></i> Penggajian</a></li>
        </ul>

        <!-- LAPORAN -->
        <span class="text-white small fw-semibold">LAPORAN</span>
        <ul class="nav flex-column mt-2">
            <li><a href="{{ route('laporan.stok') }}" class="nav-link text-white"><i class="bi bi-box-seam me-2"></i> Laporan Stok</a></li>
            <li><a href="{{ route('laporan.penjualan') }}" class="nav-link text-white"><i class="bi bi-file-bar-graph me-2"></i> Laporan Penjualan</a></li>
            <li><a href="{{ route('laporan.pembelian') }}" class="nav-link text-white"><i class="bi bi-file-text me-2"></i> Laporan Pembelian</a></li>
            <li><a href="{{ route('akuntansi.jurnal-umum') }}" class="nav-link text-white"><i class="bi bi-journal-text me-2"></i> Jurnal Umum</a></li>
            <li><a href="{{ route('akuntansi.buku-besar') }}" class="nav-link text-white"><i class="bi bi-journal-richtext me-2"></i> Buku Besar</a></li>
            <li><a href="{{ route('akuntansi.neraca-saldo') }}" class="nav-link text-white"><i class="bi bi-ui-checks-grid me-2"></i> Neraca Saldo</a></li>
            <li><a href="{{ route('akuntansi.laba-rugi') }}" class="nav-link text-white"><i class="bi bi-graph-up me-2"></i> Laba Rugi</a></li>
        </ul>
    </div>

    <!-- ========== DASHBOARD CONTENT ========== -->
    <div class="content flex-grow-1 p-4">
        <div class="row g-3">
            <x-dashboard-card title="Pegawai" :count="$totalPegawai" bg="primary" icon="bi-people-fill"/>
            <x-dashboard-card title="Presensi" :count="$totalPresensi" bg="success" icon="bi-calendar-check"/>
            <x-dashboard-card title="Produk" :count="$totalProduk" bg="warning" icon="bi-box-seam"/>
            <x-dashboard-card title="Vendor" :count="$totalVendor" bg="info" icon="bi-truck"/>
            <x-dashboard-card title="Bahan Baku" :count="$totalBahanBaku" bg="secondary" icon="bi-basket3"/>
            <x-dashboard-card title="Satuan" :count="$totalSatuan" bg="dark" icon="bi-upc-scan"/>
            <x-dashboard-card title="COA" :count="$totalCOA" bg="danger" icon="bi-list-check"/>
            <x-dashboard-card title="Pembelian" :count="$totalPembelian" bg="primary" icon="bi-cart"/>
            <x-dashboard-card title="Penjualan" :count="$totalPenjualan" bg="success" icon="bi-currency-dollar"/>
            <x-dashboard-card title="Retur" :count="$totalRetur" bg="warning" icon="bi-arrow-counterclockwise"/>
            <x-dashboard-card title="Penggajian" :count="$totalPenggajian" bg="info" icon="bi-wallet2"/>
        </div>
    </div>
</div>

<style>
/* --- SIDEBAR --- */
.sidebar {
    width: 250px;
    background-color: #222232;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    box-shadow: 3px 0 10px rgba(0, 0, 0, 0.3);
}

/* --- MAIN CONTENT --- */
.content {
    margin-left: 250px;
    background: #f7f8fc;
    min-height: 100vh;
}

/* --- LINK STYLE --- */
.nav-link {
    font-size: 0.9rem;
    color: #ccc !important;
    border-radius: 8px;
    transition: 0.2s;
    padding: 8px 12px;
}
.nav-link:hover,
.nav-link.active {
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff !important;
}
.nav-item .badge {
    font-size: 0.7rem;
}
</style>
@endsection
