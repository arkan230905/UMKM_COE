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
