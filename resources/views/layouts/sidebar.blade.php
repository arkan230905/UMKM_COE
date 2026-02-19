<div class="sidebar">
    <!-- User Profile Card -->
    <div class="user-profile-card">
        <div class="user-avatar">
            @if(Auth::check() && Auth::user()->profile_photo)
                <img src="{{ asset('storage/profile-photos/' . Auth::user()->profile_photo) }}" 
                     alt="Profile Photo" 
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\"fas fa-user\"></i>';">
            @else
                <i class="fas fa-user"></i>
            @endif
        </div>
        <div class="user-info">
            <h4>{{ Auth::check() ? Auth::user()->name : 'Guest' }}</h4>
            <small>{{ Auth::check() ? ucfirst(Auth::user()->role) : '' }}</small>
        </div>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link-rounded {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Master Data Section -->
            <li class="nav-item">
                <div class="nav-section-header-rounded">
                    <i class="fas fa-database"></i>
                    <span>MASTER DATA</span>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/coa*') ? 'active' : '' }}" href="{{ route('master-data.coa.index') }}">
                    <i class="fas fa-book"></i>
                    <span>COA</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/aset*') ? 'active' : '' }}" href="{{ route('master-data.aset.index') }}">
                    <i class="fas fa-laptop"></i>
                    <span>Aset</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/satuan*') ? 'active' : '' }}" href="{{ route('master-data.satuan.dashboard') }}">
                    <i class="fas fa-balance-scale"></i>
                    <span>Satuan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/kualifikasi-tenaga-kerja*') ? 'active' : '' }}" href="{{ route('master-data.kualifikasi-tenaga-kerja.index') }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Kualifikasi Tenaga Kerja</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/pegawai*') ? 'active' : '' }}" href="{{ route('master-data.pegawai.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Pegawai</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/vendor*') ? 'active' : '' }}" href="{{ route('master-data.vendor.index') }}">
                    <i class="fas fa-truck"></i>
                    <span>Vendor</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/pelanggan*') ? 'active' : '' }}" href="{{ route('master-data.pelanggan.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Pelanggan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/bahan-baku*') ? 'active' : '' }}" href="{{ route('master-data.bahan-baku.index') }}">
                    <i class="fas fa-cubes"></i>
                    <span>Bahan Baku</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/bahan-pendukung*') ? 'active' : '' }}" href="{{ route('master-data.bahan-pendukung.index') }}">
                    <i class="fas fa-flask"></i>
                    <span>Bahan Pendukung</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/produk*') ? 'active' : '' }}" href="{{ route('master-data.produk.index') }}">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/biaya-bahan*') ? 'active' : '' }}" href="{{ route('master-data.biaya-bahan.index') }}">
                    <i class="fas fa-calculator"></i>
                    <span>Biaya Bahan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/btkl*') ? 'active' : '' }}" href="{{ route('master-data.btkl.index') }}">
                    <i class="fas fa-industry"></i>
                    <span>BTKL (Proses Produksi)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/bop*') ? 'active' : '' }}" href="{{ route('master-data.bop.index') }}">
                    <i class="fas fa-chart-pie"></i>
                    <span>BOP (Biaya Overhead Pabrik)</span>
                </a>
            </li>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('master-data/harga-pokok-produksi*') ? 'active' : '' }}" href="{{ route('master-data.harga-pokok-produksi.index') }}">
                    <i class="fas fa-sitemap"></i>
                    <span>Harga Pokok Produksi</span>
                </a>
            </li>
            
            <!-- Transaksi Section -->
            <li class="nav-item">
                <div class="nav-section-header-rounded">
                    <i class="fas fa-exchange-alt"></i>
                    <span>TRANSAKSI</span>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('transaksi/pembelian*') ? 'active' : '' }}" href="{{ route('transaksi.pembelian.index') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pembelian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('transaksi/penjualan*') ? 'active' : '' }}" href="{{ route('transaksi.penjualan.index') }}">
                    <i class="fas fa-store"></i>
                    <span>Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('transaksi/produksi*') ? 'active' : '' }}" href="{{ route('transaksi.produksi.index') }}">
                    <i class="fas fa-industry"></i>
                    <span>Produksi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('transaksi/presensi*') ? 'active' : '' }}" href="{{ route('transaksi.presensi.index') }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Presensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('transaksi/penggajian*') ? 'active' : '' }}" href="{{ route('transaksi.penggajian.index') }}">
                    <i class="fas fa-money-bill"></i>
                    <span>Penggajian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('transaksi/pembayaran-beban*') ? 'active' : '' }}" href="{{ route('transaksi.pembayaran-beban.index') }}">
                    <i class="fas fa-money-check-alt"></i>
                    <span>Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('transaksi/pelunasan-utang*') ? 'active' : '' }}" href="{{ route('transaksi.pelunasan-utang.index') }}">
                    <i class="fas fa-credit-card"></i>
                    <span>Pelunasan Utang</span>
                </a>
            </li>
            
            <!-- Laporan Section -->
            <li class="nav-item">
                <div class="nav-section-header-rounded">
                    <i class="fas fa-chart-bar"></i>
                    <span>LAPORAN</span>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/pembelian*') ? 'active' : '' }}" href="{{ route('laporan.pembelian') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Laporan Pembelian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/stok*') ? 'active' : '' }}" href="{{ route('laporan.stok') }}">
                    <i class="fas fa-boxes"></i>
                    <span>Laporan Stok</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/stock-realtime*') ? 'active' : '' }}" href="{{ route('laporan.stock-realtime') }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Stok Real-Time</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/penjualan*') ? 'active' : '' }}" href="{{ route('laporan.penjualan') }}">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Laporan Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/retur*') ? 'active' : '' }}" href="{{ route('laporan.retur') }}">
                    <i class="fas fa-undo"></i>
                    <span>Laporan Retur</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/penggajian*') ? 'active' : '' }}" href="{{ route('laporan.penggajian') }}">
                    <i class="fas fa-money-bill"></i>
                    <span>Laporan Penggajian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/pembayaran-beban*') ? 'active' : '' }}" href="{{ route('laporan.pembayaran-beban') }}">
                    <i class="fas fa-money-check-alt"></i>
                    <span>Laporan Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/pelunasan-utang*') ? 'active' : '' }}" href="{{ route('laporan.pelunasan-utang') }}">
                    <i class="fas fa-credit-card"></i>
                    <span>Laporan Pelunasan Utang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('laporan/kas-bank*') ? 'active' : '' }}" href="{{ route('laporan.kas-bank') }}">
                    <i class="fas fa-university"></i>
                    <span>Laporan Kas dan Bank</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('akuntansi/jurnal-umum*') ? 'active' : '' }}" href="{{ route('akuntansi.jurnal-umum') }}">
                    <i class="fas fa-book-open"></i>
                    <span>Jurnal Umum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('akuntansi/buku-besar*') ? 'active' : '' }}" href="{{ route('akuntansi.buku-besar') }}">
                    <i class="fas fa-book"></i>
                    <span>Buku Besar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('akuntansi/neraca-saldo*') ? 'active' : '' }}" href="{{ route('akuntansi.neraca-saldo') }}">
                    <i class="fas fa-balance-scale-right"></i>
                    <span>Neraca Saldo</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('akuntansi/neraca') ? 'active' : '' }}" href="{{ route('akuntansi.neraca') }}">
                    <i class="fas fa-balance-scale"></i>
                    <span>Neraca</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('akuntansi/laba-rugi*') ? 'active' : '' }}" href="{{ route('akuntansi.laba-rugi') }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Laba Rugi</span>
                </a>
            </li>
            
            <!-- Pengaturan Section -->
            <li class="nav-item">
                <div class="nav-section-header-rounded">
                    <i class="fas fa-cog"></i>
                    <span>PENGATURAN</span>
                </div>
            </li>
            @if(auth()->user()->role === 'owner')
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('tentang-perusahaan/detail') ? 'active' : '' }}" href="/tentang-perusahaan/detail">
                    <i class="fas fa-building"></i>
                    <span>Tentang Perusahaan</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link-rounded {{ request()->is('profile*') ? 'active' : '' }}" href="{{ route('profil-admin') }}">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0; display: block;">
                    @csrf
                    <button type="submit" class="nav-link-rounded logout-btn" style="width: 100%; border: none; cursor: pointer;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>