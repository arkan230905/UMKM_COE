<div class="sidebar bg-dark text-white vh-100 p-3">
    <h4 class="text-center mb-4">Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="{{ route('dashboard') }}" class="nav-link text-white">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.pegawai.index') }}" class="nav-link text-white">Pegawai</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.presensi.index') }}" class="nav-link text-white">Presensi</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('master-data.produk.index') }}" class="nav-link text-white">Produk</a>
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
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.pembelian.index') }}" class="nav-link text-white">Pembelian</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.penjualan.index') }}" class="nav-link text-white">Penjualan</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ route('transaksi.retur.index') }}" class="nav-link text-white">Retur</a>
        </li>
    </ul>
</div>
