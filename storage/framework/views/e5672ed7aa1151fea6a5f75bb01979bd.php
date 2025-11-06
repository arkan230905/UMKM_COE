<div class="sidebar">
    <div class="d-flex align-items-center mb-3">
        <i class="bi bi-gem fs-4 me-2"></i>
        <span class="fw-semibold">UMKM COE</span>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="<?php echo e(route('dashboard')); ?>" class="nav-link">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item mt-3">
            <div class="text-uppercase text-muted small mb-2">Master Data</div>
        </li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.coa.index')); ?>" class="nav-link"><i class="bi bi-list-check me-2"></i> COA</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.aset.index')); ?>" class="nav-link"><i class="bi bi-box2-heart me-2"></i> Aset</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.jabatan.index')); ?>" class="nav-link"><i class="bi bi-person-badge me-2"></i> Jabatan</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.pegawai.index')); ?>" class="nav-link"><i class="bi bi-people-fill me-2"></i> Pegawai</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.presensi.index')); ?>" class="nav-link"><i class="bi bi-calendar-check me-2"></i> Presensi</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.vendor.index')); ?>" class="nav-link"><i class="bi bi-truck me-2"></i> Vendor</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.satuan.index')); ?>" class="nav-link"><i class="bi bi-upc-scan me-2"></i> Satuan</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.produk.index')); ?>" class="nav-link"><i class="bi bi-box-seam me-2"></i> Produk</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.bahan-baku.index')); ?>" class="nav-link"><i class="bi bi-basket3 me-2"></i> Bahan Baku</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.bop.index')); ?>" class="nav-link"><i class="bi bi-calculator me-2"></i> BOP</a></li>
        <li class="nav-item"><a href="<?php echo e(route('master-data.bom.index')); ?>" class="nav-link"><i class="bi bi-diagram-3 me-2"></i> BOM</a></li>

        <li class="nav-item mt-3">
            <div class="text-uppercase text-muted small mb-2">Transaksi</div>
        </li>
        <li class="nav-item"><a href="<?php echo e(route('transaksi.pembelian.index')); ?>" class="nav-link"><i class="bi bi-cart me-2"></i> Pembelian</a></li>
        <li class="nav-item"><a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="nav-link"><i class="bi bi-diagram-3 me-2"></i> Produksi</a></li>
        <li class="nav-item"><a href="<?php echo e(route('transaksi.penjualan.index')); ?>" class="nav-link"><i class="bi bi-currency-dollar me-2"></i> Penjualan</a></li>
        <li class="nav-item"><a href="<?php echo e(route('transaksi.retur.index')); ?>" class="nav-link"><i class="bi bi-arrow-counterclockwise me-2"></i> Retur</a></li>
        <li class="nav-item"><a href="<?php echo e(route('transaksi.expense-payment.index')); ?>" class="nav-link"><i class="bi bi-cash-coin me-2"></i> Pembayaran Beban</a></li>
        <li class="nav-item"><a href="<?php echo e(route('transaksi.ap-settlement.index')); ?>" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pelunasan Utang</a></li>
        <li class="nav-item"><a href="<?php echo e(route('transaksi.penggajian.index')); ?>" class="nav-link"><i class="bi bi-wallet2 me-2"></i> Penggajian</a></li>

        <li class="nav-item mt-3">
            <div class="text-uppercase text-muted small mb-2">Laporan</div>
        </li>
        <li class="nav-item"><a href="<?php echo e(route('laporan.stok')); ?>" class="nav-link"><i class="bi bi-box-seam me-2"></i> Laporan Stok</a></li>
        <li class="nav-item"><a href="<?php echo e(route('laporan.penjualan')); ?>" class="nav-link"><i class="bi bi-file-bar-graph me-2"></i> Laporan Penjualan</a></li>
        <li class="nav-item"><a href="<?php echo e(route('laporan.pembelian')); ?>" class="nav-link"><i class="bi bi-file-text me-2"></i> Laporan Pembelian</a></li>
        <li class="nav-item"><a href="<?php echo e(route('laporan.retur')); ?>" class="nav-link"><i class="bi bi-arrow-return-left me-2"></i> Laporan Retur</a></li>
        <li class="nav-item"><a href="<?php echo e(route('laporan.penggajian')); ?>" class="nav-link"><i class="bi bi-cash-stack me-2"></i> Laporan Penggajian</a></li>
        <li class="nav-item"><a href="<?php echo e(route('laporan.pembayaran-beban')); ?>" class="nav-link"><i class="bi bi-credit-card-2-back me-2"></i> Laporan Pembayaran Beban</a></li>
        <li class="nav-item"><a href="<?php echo e(route('laporan.pelunasan-utang')); ?>" class="nav-link"><i class="bi bi-wallet2 me-2"></i> Laporan Pelunasan Utang</a></li>
        <li class="nav-item"><a href="<?php echo e(route('akuntansi.jurnal-umum')); ?>" class="nav-link"><i class="bi bi-journal-text me-2"></i> Jurnal Umum</a></li>
        <li class="nav-item"><a href="<?php echo e(route('akuntansi.buku-besar')); ?>" class="nav-link"><i class="bi bi-journal-richtext me-2"></i> Buku Besar</a></li>
        <li class="nav-item mb-1"><a href="<?php echo e(route('akuntansi.neraca-saldo')); ?>" class="nav-link"><i class="bi bi-ui-checks-grid me-2"></i> Neraca Saldo</a></li>
        <li class="nav-item mb-1"><a href="<?php echo e(route('akuntansi.laba-rugi')); ?>" class="nav-link"><i class="bi bi-graph-up me-2"></i> Laba Rugi</a></li>

    </ul>
</div>
<?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>