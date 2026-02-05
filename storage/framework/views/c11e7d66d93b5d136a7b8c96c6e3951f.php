<div class="sidebar">
    <!-- User Profile Card -->
    <div class="user-profile-card">
        <div class="user-avatar">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::check() && Auth::user()->profile_photo): ?>
                <img src="<?php echo e(asset('storage/profile-photos/' . Auth::user()->profile_photo)); ?>" 
                     alt="Profile Photo" 
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;"
                     onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\"fas fa-user\"></i>';">
            <?php else: ?>
                <i class="fas fa-user"></i>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="user-info">
            <h4><?php echo e(Auth::check() ? Auth::user()->name : 'Guest'); ?></h4>
            <small><?php echo e(Auth::check() ? ucfirst(Auth::user()->role) : ''); ?></small>
        </div>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="<?php echo e(route('dashboard')); ?>" class="nav-link-rounded <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">
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
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/coa*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.coa.index')); ?>">
                    <i class="fas fa-book"></i>
                    <span>COA</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/aset*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.aset.index')); ?>">
                    <i class="fas fa-laptop"></i>
                    <span>Aset</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/satuan*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.satuan.index')); ?>">
                    <i class="fas fa-balance-scale"></i>
                    <span>Satuan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/kualifikasi-tenaga-kerja*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.kualifikasi-tenaga-kerja.index')); ?>">
                    <i class="fas fa-user-tie"></i>
                    <span>Kualifikasi Tenaga Kerja</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/pegawai*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.pegawai.index')); ?>">
                    <i class="fas fa-users"></i>
                    <span>Pegawai</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/vendor*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.vendor.index')); ?>">
                    <i class="fas fa-truck"></i>
                    <span>Vendor</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/bahan-baku*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.bahan-baku.index')); ?>">
                    <i class="fas fa-cubes"></i>
                    <span>Bahan Baku</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/bahan-pendukung*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.bahan-pendukung.index')); ?>">
                    <i class="fas fa-flask"></i>
                    <span>Bahan Pendukung</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/produk*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.produk.index')); ?>">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/biaya-bahan*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.biaya-bahan.index')); ?>">
                    <i class="fas fa-calculator"></i>
                    <span>Biaya Bahan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/btkl*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.btkl.index')); ?>">
                    <i class="fas fa-industry"></i>
                    <span>BTKL (Proses Produksi)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/bop*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.bop.index')); ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>BOP (Biaya Overhead Pabrik)</span>
                </a>
            </li>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('master-data/bom*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.bom.index')); ?>">
                    <i class="fas fa-sitemap"></i>
                    <span>BOM (Bill of Materials)</span>
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
                <a class="nav-link-rounded <?php echo e(request()->is('transaksi/pembelian*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.pembelian.index')); ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pembelian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('transaksi/penjualan*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.penjualan.index')); ?>">
                    <i class="fas fa-store"></i>
                    <span>Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('transaksi/produksi*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.produksi.index')); ?>">
                    <i class="fas fa-industry"></i>
                    <span>Produksi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('transaksi/presensi*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.presensi.index')); ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Presensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('transaksi/penggajian*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.penggajian.index')); ?>">
                    <i class="fas fa-money-bill"></i>
                    <span>Penggajian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('transaksi/pembayaran-beban*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.pembayaran-beban.index')); ?>">
                    <i class="fas fa-money-check-alt"></i>
                    <span>Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('transaksi/pelunasan-utang*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.pelunasan-utang.index')); ?>">
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
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/pembelian*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.pembelian')); ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Laporan Pembelian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/stok*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.stok')); ?>">
                    <i class="fas fa-boxes"></i>
                    <span>Laporan Stok</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/penjualan*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.penjualan')); ?>">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Laporan Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/retur*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.retur')); ?>">
                    <i class="fas fa-undo"></i>
                    <span>Laporan Retur</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/penggajian*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.penggajian')); ?>">
                    <i class="fas fa-money-bill"></i>
                    <span>Laporan Penggajian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/pembayaran-beban*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.pembayaran-beban')); ?>">
                    <i class="fas fa-money-check-alt"></i>
                    <span>Laporan Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/pelunasan-utang*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.pelunasan-utang')); ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Laporan Pelunasan Utang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('laporan/kas-bank*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.kas-bank')); ?>">
                    <i class="fas fa-university"></i>
                    <span>Laporan Kas dan Bank</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('akuntansi/jurnal-umum*') ? 'active' : ''); ?>" href="<?php echo e(route('akuntansi.jurnal-umum')); ?>">
                    <i class="fas fa-book-open"></i>
                    <span>Jurnal Umum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('akuntansi/buku-besar*') ? 'active' : ''); ?>" href="<?php echo e(route('akuntansi.buku-besar')); ?>">
                    <i class="fas fa-book"></i>
                    <span>Buku Besar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('akuntansi/neraca-saldo*') ? 'active' : ''); ?>" href="<?php echo e(route('akuntansi.neraca-saldo')); ?>">
                    <i class="fas fa-balance-scale-right"></i>
                    <span>Neraca Saldo</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('akuntansi/neraca') ? 'active' : ''); ?>" href="<?php echo e(route('akuntansi.neraca')); ?>">
                    <i class="fas fa-balance-scale"></i>
                    <span>Neraca</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('akuntansi/laba-rugi*') ? 'active' : ''); ?>" href="<?php echo e(route('akuntansi.laba-rugi')); ?>">
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'owner'): ?>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('tentang-perusahaan/detail') ? 'active' : ''); ?>" href="/tentang-perusahaan/detail">
                    <i class="fas fa-building"></i>
                    <span>Tentang Perusahaan</span>
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <li class="nav-item">
                <a class="nav-link-rounded <?php echo e(request()->is('profile*') ? 'active' : ''); ?>" href="<?php echo e(route('profil-admin')); ?>">
                    <i class="fas fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin: 0; padding: 0; display: block;">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="nav-link-rounded logout-btn" style="width: 100%; border: none; cursor: pointer;">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div><?php /**PATH C:\UMKM_COE\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>