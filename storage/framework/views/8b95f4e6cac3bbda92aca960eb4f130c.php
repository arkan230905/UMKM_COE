

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">Dashboard</h2>
            <p class="text-muted">Selamat datang, <?php echo e(Auth::user()->name); ?>! <?php echo e(now()->format('l, d F Y H:i')); ?> WIB</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-4">
            <select class="form-select" id="monthFilter" onchange="applyFilter()">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php echo e($month == $key ? 'selected' : ''); ?>>
                        <?php echo e($month); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" id="yearFilter" onchange="applyFilter()">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $availableYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($availableYear); ?>" <?php echo e($year == $availableYear ? 'selected' : ''); ?>>
                        <?php echo e($availableYear); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100" onclick="applyFilter()">
                <i class="fas fa-filter"></i> Terapkan Filter
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: var(--primary-gold);">
                                <i class="fas fa-wallet text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Kas & Bank</h6>
                            <h4 class="mb-0">Rp <?php echo e(number_format($totalKasBank, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: #28A745;">
                                <i class="fas fa-chart-line text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pendapatan Bulan Ini</h6>
                            <h4 class="mb-0">Rp <?php echo e(number_format($pendapatanBulanIni, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: #FFC107;">
                                <i class="fas fa-receipt text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Piutang</h6>
                            <h4 class="mb-0">Rp <?php echo e(number_format($totalPiutang, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: #DC3545;">
                                <i class="fas fa-credit-card text-white fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Utang</h6>
                            <h4 class="mb-0">Rp <?php echo e(number_format($totalUtang, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Data Section -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Master Data</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <h6 class="mb-1">Pegawai</h6>
                                <h4 class="mb-0"><?php echo e($totalPegawai); ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-box text-white"></i>
                                </div>
                                <h6 class="mb-1">Produk</h6>
                                <h4 class="mb-0"><?php echo e($totalProduk); ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-truck text-white"></i>
                                </div>
                                <h6 class="mb-1">Vendor</h6>
                                <h4 class="mb-0"><?php echo e($totalVendor); ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-cubes text-white"></i>
                                </div>
                                <h6 class="mb-1">Bahan Baku</h6>
                                <h4 class="mb-0"><?php echo e($totalBahanBaku); ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-list-alt text-white"></i>
                                </div>
                                <h6 class="mb-1">COA</h6>
                                <h4 class="mb-0"><?php echo e($totalCOA); ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <div class="text-center p-3 rounded" style="background-color: var(--light-gold); border: 1px solid rgba(139, 115, 92, 0.1);">
                                <div class="rounded-circle mx-auto mb-2 p-2" style="background-color: var(--primary-gold); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-shopping-cart text-white"></i>
                                </div>
                                <h6 class="mb-1">Pembelian</h6>
                                <h4 class="mb-0"><?php echo e($totalPembelian); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>Lihat Semua
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo e(route('transaksi.penjualan.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-cash-register me-2"></i>Penjualan
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo e(route('transaksi.pembelian.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-shopping-cart me-2"></i>Pembelian
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-industry me-2"></i>Produksi
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="<?php echo e(route('akuntansi.laba-rugi')); ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-line me-2"></i>Laporan Laba Rugi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pembayaran Beban Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">Belum ada transaksi</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Pelunasan Utang Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-credit-card text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p class="text-muted mt-3">Belum ada transaksi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function applyFilter() {
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;
    window.location.href = `<?php echo e(route('dashboard')); ?>?month=${month}&year=${year}`;
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/dashboard.blade.php ENDPATH**/ ?>