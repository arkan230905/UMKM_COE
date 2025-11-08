<div class="sidebar">
    <div class="sidebar-brand">
        <h4 class="text-white">UMKM COE</h4>
        <small class="text-white-50">Aplikasi Manajemen UMKM</small>
    </div>
    
    <div class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Master -->
            <li class="nav-item mt-2">
                <div class="text-uppercase text-white-50 small px-3 mb-1">Master</div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/coa*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.coa.index')); ?>">
                    <i class="fas fa-fw fa-book"></i>
                    <span>COA</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/aset*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.aset.index')); ?>">
                    <i class="fas fa-fw fa-laptop"></i>
                    <span>Aset</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/satuan*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.satuan.index')); ?>">
                    <i class="fas fa-fw fa-ruler"></i>
                    <span>Satuan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/jabatan*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.jabatan.index')); ?>">
                    <i class="fas fa-fw fa-user-tie"></i>
                    <span>Jabatan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/pegawai*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.pegawai.index')); ?>">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Pegawai</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/presensi*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.presensi.index')); ?>">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Presensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/vendor*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.vendor.index')); ?>">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Vendor</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/bahan-baku*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.bahan-baku.index')); ?>">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Bahan Baku</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/produk*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.produk.index')); ?>">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Produk</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/bop*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.bop.index')); ?>">
                    <i class="fas fa-fw fa-calculator"></i>
                    <span>BOP</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('master-data/bom*') ? 'active' : ''); ?>" href="<?php echo e(route('master-data.bom.index')); ?>">
                    <i class="fas fa-fw fa-list-alt"></i>
                    <span>BOM</span>
                </a>
            </li>
            
            <!-- Transaksi -->
            <li class="nav-item mt-2">
                <div class="text-uppercase text-white-50 small px-3 mb-1">Transaksi</div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('transaksi/pembelian*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.pembelian.index')); ?>">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Pembelian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('transaksi/produksi*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.produksi.index')); ?>">
                    <i class="fas fa-fw fa-industry"></i>
                    <span>Produksi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('transaksi/penjualan*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.penjualan.index')); ?>">
                    <i class="fas fa-fw fa-cash-register"></i>
                    <span>Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('transaksi/retur*') ? 'active' : ''); ?>" href="<?php echo e(route('transaksi.retur.index')); ?>">
                    <i class="fas fa-fw fa-undo"></i>
                    <span>Retur</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span>Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-hand-holding-usd"></i>
                    <span>Pelunasan Utang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-money-bill"></i>
                    <span>Penggajian</span>
                </a>
            </li>
            
            <!-- Laporan -->
            <li class="nav-item mt-2">
                <div class="text-uppercase text-white-50 small px-3 mb-1">Laporan</div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('laporan/stok*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.stok')); ?>">
                    <i class="fas fa-fw fa-boxes"></i>
                    <span>Laporan Stok</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->is('laporan/penjualan*') ? 'active' : ''); ?>" href="<?php echo e(route('laporan.penjualan')); ?>">
                    <i class="fas fa-fw fa-file-invoice"></i>
                    <span>Laporan Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-undo"></i>
                    <span>Laporan Retur</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-money-bill"></i>
                    <span>Laporan Penggajian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span>Laporan Pembayaran Beban</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-hand-holding-usd"></i>
                    <span>Laporan Pelunasan Utang</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-book"></i>
                    <span>Jurnal Umum</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-book-open"></i>
                    <span>Buku Besar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-balance-scale"></i>
                    <span>Neraca Saldo</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-fw fa-chart-line"></i>
                    <span>Laba Rugi</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- User Profile Section Removed -->
</div>
<?php /**PATH C:\UMKM_COE\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>