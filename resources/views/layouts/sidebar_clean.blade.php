<div class="sidebar bg-dark text-white vh-100 p-3">
    <h4 class="text-center mb-4">Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="{{ route('dashboard') }}" class="nav-link text-white">Dashboard</a>
        </li>

        {{-- Master Data --}}
        <li class="nav-item mb-2">
            <h6 class="text-uppercase text-muted mt-3 mb-2" style="font-size: 0.75rem;">Master Data</h6>
        </li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.coa.index') }}" class="nav-link text-white">COA</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.aset.index') }}" class="nav-link text-white">Aset</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.jabatan.index') }}" class="nav-link text-white">Jabatan</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.pegawai.index') }}" class="nav-link text-white">Pegawai</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.presensi.index') }}" class="nav-link text-white">Presensi</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.vendor.index') }}" class="nav-link text-white">Vendor</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.satuan.index') }}" class="nav-link text-white">Satuan</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.produk.index') }}" class="nav-link text-white">Produk</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.bahan-baku.index') }}" class="nav-link text-white">Bahan Baku</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.bop.index') }}" class="nav-link text-white">BOP</a></li>
        <li class="nav-item mb-2"><a href="{{ route('master-data.bom.index') }}" class="nav-link text-white">Harga Pokok Produksi</a></li>

        {{-- Transaksi --}}
        <li class="nav-item mb-2">
            <h6 class="text-uppercase text-muted mt-4 mb-2" style="font-size: 0.75rem;">Transaksi</h6>
        </li>
        <li class="nav-item mb-2"><a href="{{ route('transaksi.pembelian.index') }}" class="nav-link text-white">Pembelian</a></li>
        <li class="nav-item mb-2"><a href="{{ route('transaksi.produksi.index') }}" class="nav-link text-white">Produksi</a></li>
        <li class="nav-item mb-2"><a href="{{ route('transaksi.penjualan.index') }}" class="nav-link text-white">Penjualan</a></li>
        {{-- Menu Retur lama dinonaktifkan karena retur per transaksi --}}
        <li class="nav-item mb-2"><a href="{{ route('transaksi.pembayaran-beban.index') }}" class="nav-link text-white">Pembayaran Beban</a></li>
        <li class="nav-item mb-2"><a href="{{ route('transaksi.ap-settlement.index') }}" class="nav-link text-white">Pelunasan Utang</a></li>
        <li class="nav-item mb-2"><a href="{{ route('transaksi.penggajian.index') }}" class="nav-link text-white">Penggajian</a></li>
    </ul>
</div>
