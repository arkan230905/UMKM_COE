<<<<<<< HEAD
<!-- resources/views/layouts/sidebar.blade.php -->

<nav class="sidebar bg-gray-900 text-white min-h-screen p-4">
    <h2 class="text-lg font-bold mb-4">Menu</h2>

    <!-- Master Data -->
    <div class="mb-6">
        <h3 class="text-sm font-semibold uppercase mb-2 text-gray-400">MASTER DATA</h3>
        <ul class="space-y-1">
            <li><a href="{{ route('master-data.pegawai.index') }}" class="block hover:text-blue-400">Pegawai</a></li>
            <li><a href="{{ route('master-data.presensi.index') }}" class="block hover:text-blue-400">Presensi</a></li>
            <li><a href="{{ route('master-data.produk.index') }}" class="block hover:text-blue-400">Produk</a></li>
            <li><a href="{{ route('master-data.vendor.index') }}" class="block hover:text-blue-400">Vendor</a></li>
            <li><a href="{{ route('master-data.coa.index') }}" class="block hover:text-blue-400">COA</a></li>
            <li><a href="{{ route('master-data.bahan-baku.index') }}" class="block hover:text-blue-400">Bahan Baku</a></li>
            <li><a href="{{ route('master-data.bom.index') }}" class="block hover:text-blue-400">BOM</a></li>
        </ul>
    </div>

    <!-- Transaksi -->
    <div class="mb-6">
        <h3 class="text-sm font-semibold uppercase mb-2 text-gray-400">TRANSAKSI</h3>
        <ul class="space-y-1">
            <li><a href="{{ route('transaksi.pembelian.index') }}" class="block hover:text-blue-400">Pembelian</a></li>
            <li><a href="{{ route('transaksi.penjualan.index') }}" class="block hover:text-blue-400">Penjualan</a></li>
            <li><a href="{{ route('transaksi.retur.index') }}" class="block hover:text-blue-400">Retur</a></li>
        </ul>
    </div>

    <!-- Laporan -->
    <div class="mb-6">
        <h3 class="text-sm font-semibold uppercase mb-2 text-gray-400">LAPORAN</h3>
        <ul class="space-y-1">
            <li><a href="{{ url('/laporan/penjualan') }}" class="block hover:text-blue-400">Laporan Penjualan</a></li>
            <li><a href="{{ url('/laporan/pembelian') }}" class="block hover:text-blue-400">Laporan Pembelian</a></li>
            <li><a href="{{ url('/laporan/stok') }}" class="block hover:text-blue-400">Laporan Stok</a></li>
        </ul>
    </div>
</nav>
=======
<div class="sidebar bg-dark text-white vh-100 p-3">
    <h4 class="text-center mb-4">Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="{{ route('dashboard') }}" class="nav-link text-white">Dashboard</a>
        </li>

        {{-- Master Data --}}
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.pegawai.index') }}" class="nav-link text-white">Pegawai</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.presensi.index') }}" class="nav-link text-white">Presensi</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.produk.index') }}" class="nav-link text-white">Produk</a>
        </li>
        {{-- ✅ Tambah menu baru Satuan --}}
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.satuan.index') }}" class="nav-link text-white">Satuan</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.vendor.index') }}" class="nav-link text-white">Vendor</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.coa.index') }}" class="nav-link text-white">COA</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.bahan-baku.index') }}" class="nav-link text-white">Bahan Baku</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.bom.index') }}" class="nav-link text-white">BOM</a>
        </li>

        {{-- ✅ Tambah menu baru: BOP --}}
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.bop.index') }}" class="nav-link text-white">BOP</a>
        </li>

        {{-- Transaksi --}}
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.pembelian.index') }}" class="nav-link text-white">Pembelian</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.penjualan.index') }}" class="nav-link text-white">Penjualan</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.penggajian.index') }}" class="nav-link text-white">Penggajian</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.retur.index') }}" class="nav-link text-white">Retur</a>
        </li>
    </ul
>>>>>>> 68de30b (pembuatan bop dan satuan)
