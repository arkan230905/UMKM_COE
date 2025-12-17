<div class="sidebar">
    <div class="sidebar-brand">
        <h4 class="text-white">UMKM COE</h4>
        <small class="text-white-50">Aplikasi Manajemen UMKM</small>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Master -->
            <li class="nav-item mt-2">
                <div class="text-uppercase text-white-50 small px-3 mb-1">Master</div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/coa*') ? 'active' : '' }}" href="{{ route('master-data.coa.index') }}">
                    <i class="fas fa-fw fa-book"></i>
                    <span>COA</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/aset*') ? 'active' : '' }}" href="{{ route('master-data.aset.index') }}">
                    <i class="fas fa-fw fa-laptop"></i>
                    <span>Aset</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/satuan*') ? 'active' : '' }}" href="{{ route('master-data.satuan.index') }}">
                    <i class="fas fa-fw fa-ruler"></i>
                    <span>Satuan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/jabatan*') ? 'active' : '' }}" href="{{ route('master-data.jabatan.index') }}">
                    <i class="fas fa-fw fa-user-tie"></i>
                    <span>Klasifikasi Tenaga Kerja</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/pegawai*') ? 'active' : '' }}" href="{{ route('master-data.pegawai.index') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Pegawai</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/pelanggan*') ? 'active' : '' }}" href="{{ route('master-data.pelanggan.index') }}">
                    <i class="fas fa-fw fa-user-friends"></i>
                    <span>Pelanggan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/presensi*') ? 'active' : '' }}" href="{{ route('master-data.presensi.index') }}">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Presensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/vendor*') ? 'active' : '' }}" href="{{ route('master-data.vendor.index') }}">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Vendor</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/bahan-baku*') ? 'active' : '' }}" href="{{ route('master-data.bahan-baku.index') }}">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Bahan Baku</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/produk*') ? 'active' : '' }}" href="{{ route('master-data.produk.index') }}">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/bop*') ? 'active' : '' }}" href="{{ route('master-data.bop.index') }}">
                    <i class="fas fa-fw fa-calculator"></i>
                    <span>BOP</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('master-data/bom*') ? 'active' : '' }}" href="{{ route('master-data.bom.index') }}">
                    <i class="fas fa-fw fa-list-alt"></i>
                    <span>BOM</span>
                </a>
            </li>
            
            <!-- Transaksi -->
            <li class="nav-item mt-2">
                <div class="text-uppercase text-white-50 small px-3 mb-1">Transaksi</div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transaksi/pembelian*') ? 'active' : '' }}" href="{{ route('transaksi.pembelian.index') }}">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Pembelian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transaksi/produksi*') ? 'active' : '' }}" href="{{ route('transaksi.produksi.index') }}">
                    <i class="fas fa-fw fa-industry"></i>
                    <span>Produksi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transaksi/penjualan*') ? 'active' : '' }}" href="{{ route('transaksi.penjualan.index') }}">
                    <i class="fas fa-fw fa-cash-register"></i>
                    <span>Penjualan</span>
                </a>
            </li>
            {{-- Menu Retur lama dinonaktifkan karena retur per transaksi --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transaksi/pembayaran-beban*') ? 'active' : '' }}" href="{{ route('transaksi.pembayaran-beban.index') }}">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span>Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transaksi/pelunasan-utang*') ? 'active' : '' }}" href="{{ route('transaksi.pelunasan-utang.index') }}">
                    <i class="fas fa-fw fa-hand-holding-usd"></i>
                    <span>Pelunasan Utang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transaksi/penggajian*') ? 'active' : '' }}" href="{{ route('transaksi.penggajian.index') }}">
                    <i class="fas fa-fw fa-money-bill"></i>
                    <span>Penggajian</span>
                </a>
            </li>
            
            <!-- Laporan -->
            <li class="nav-item mt-2">
                <div class="text-uppercase text-white-50 small px-3 mb-1">Laporan</div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/pembelian*') ? 'active' : '' }}" href="{{ route('laporan.pembelian') }}">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Laporan Pembelian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/stok*') ? 'active' : '' }}" href="{{ route('laporan.stok') }}">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Laporan Stok</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/penjualan*') ? 'active' : '' }}" href="{{ route('laporan.penjualan') }}">
                    <i class="fas fa-fw fa-file-invoice"></i>
                    <span>Laporan Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/retur*') ? 'active' : '' }}" href="{{ route('laporan.retur') }}">
                    <i class="fas fa-fw fa-undo"></i>
                    <span>Laporan Retur</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/penggajian*') ? 'active' : '' }}" href="{{ route('laporan.penggajian') }}">
                    <i class="fas fa-fw fa-money-bill"></i>
                    <span>Laporan Penggajian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/pembayaran-beban*') ? 'active' : '' }}" href="{{ route('laporan.pembayaran-beban') }}">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span>Laporan Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/pelunasan-utang*') ? 'active' : '' }}" href="{{ route('laporan.pelunasan-utang') }}">
                    <i class="fas fa-fw fa-hand-holding-usd"></i>
                    <span>Laporan Pelunasan Utang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laporan/kas-bank*') ? 'active' : '' }}" href="{{ route('laporan.kas-bank') }}">
                    <i class="fas fa-fw fa-wallet"></i>
                    <span>Laporan Kas dan Bank</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('akuntansi/jurnal-umum*') ? 'active' : '' }}" href="{{ route('akuntansi.jurnal-umum') }}">
                    <i class="fas fa-fw fa-book"></i>
                    <span>Jurnal Umum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('akuntansi/buku-besar*') ? 'active' : '' }}" href="{{ route('akuntansi.buku-besar') }}">
                    <i class="fas fa-fw fa-book-open"></i>
                    <span>Buku Besar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('akuntansi/neraca-saldo*') ? 'active' : '' }}" href="{{ route('akuntansi.neraca-saldo') }}">
                    <i class="fas fa-fw fa-balance-scale"></i>
                    <span>Neraca Saldo</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('akuntansi/laba-rugi*') ? 'active' : '' }}" href="{{ route('akuntansi.laba-rugi') }}">
                    <i class="fas fa-fw fa-chart-line"></i>
                    <span>Laba Rugi</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- User Profile Section Removed -->
</div>