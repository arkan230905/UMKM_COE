<div class="sidebar">
    <div class="logo">
        <h2>UMKM COE</h2>
    </div>
    <ul class="menu">
        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>

        <li class="menu-section">MASTER DATA</li>
        <li><a href="{{ url('/master-data/pegawai') }}">Pegawai</a></li>
        <li><a href="{{ url('/master-data/presensi') }}">Presensi</a></li>
        <li><a href="{{ url('/master-data/produk') }}">Produk</a></li>
        <li><a href="{{ url('/master-data/vendor') }}">Vendor</a></li>
        <li><a href="{{ url('/master-data/bahan-baku') }}">Bahan Baku</a></li>
        <li><a href="{{ url('/master-data/satuan') }}">Satuan</a></li>
        <li><a href="{{ url('/master-data/coa') }}">COA</a></li>

        <li class="menu-section">TRANSAKSI</li>
        <li><a href="{{ url('/transaksi/pembelian') }}">Pembelian</a></li>
        <li><a href="{{ url('/transaksi/penjualan') }}">Penjualan</a></li>
        <li><a href="{{ url('/transaksi/retur') }}">Retur</a></li>
        <li><a href="{{ url('/transaksi/penggajian') }}">Penggajian</a></li>
    </ul>
</div>
