<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Penjualan</h3>
        <div>
            <a href="<?php echo e(route('laporan.penjualan.export')); ?>" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="">Semua Metode</option>
                        <option value="cash" <?php echo e(request('payment_method') == 'cash' ? 'selected' : ''); ?>>Tunai</option>
                        <option value="transfer" <?php echo e(request('payment_method') == 'transfer' ? 'selected' : ''); ?>>Transfer</option>
                        <option value="credit" <?php echo e(request('payment_method') == 'credit' ? 'selected' : ''); ?>>Kredit</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Penjualan</h5>
                    <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPenjualanFiltered, 0, ',', '.')); ?></h3>
                    <small class="text-dark opacity-75">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('start_date') && request('end_date')): ?>
                            <?php echo e(\Carbon\Carbon::parse(request('start_date'))->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse(request('end_date'))->format('d/m/Y')); ?>

                        <?php else: ?>
                            Semua Periode
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Penjualan Tunai</h5>
                    <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPenjualanTunai, 0, ',', '.')); ?></h3>
                    <small class="text-dark opacity-75">Pembayaran Cash</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title text-dark">Total Penjualan Kredit</h5>
                    <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPenjualanKredit, 0, ',', '.')); ?></h3>
                    <small class="text-dark opacity-75">Pembayaran Kredit</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Produk Terjual</th>
                            <th>Pembayaran</th>
                            <th class="text-end">Total</th>
                            <th style="width:12%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $penjualan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($penjualan->firstItem() + $index); ?></td>
                                <td><strong><?php echo e($p->nomor_penjualan ?? '-'); ?></strong></td>
                                <td><?php echo e(optional($p->tanggal)->format('d/m/Y') ?? '-'); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->details && $p->details->count() > 0): ?>
                                        <div class="small">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $p->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="mb-1">
                                                    • <?php echo e($detail->produk->nama_produk ?? 'Produk'); ?>

                                                    <span class="text-muted">
                                                        (<?php echo e(number_format($detail->jumlah ?? 0, 0, ',', '.')); ?> pcs)
                                                    </span>
                                                    - Rp <?php echo e(number_format($detail->harga_satuan ?? 0, 0, ',', '.')); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail->diskon_nominal ?? 0) > 0): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            Diskon: Rp <?php echo e(number_format($detail->diskon_nominal, 0, ',', '.')); ?>

                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    = <strong>Rp <?php echo e(number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0), 0, ',', '.')); ?></strong>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <?php echo e($p->produk->nama_produk ?? '-'); ?>

                                        <span class="text-muted">
                                            (<?php echo e(number_format($p->jumlah ?? 0, 0, ',', '.')); ?> pcs)
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->payment_method === 'cash'): ?>
                                        <span class="badge bg-success">Tunai</span>
                                    <?php elseif($p->payment_method === 'transfer'): ?>
                                        <span class="badge bg-info">Transfer</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Kredit</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php
                                        $totalPenjualanItem = $p->total ?? 0;
                                        // Jika total = 0, hitung dari details
                                        if ($totalPenjualanItem == 0 && $p->details && $p->details->count() > 0) {
                                            $totalPenjualanItem = $p->details->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0) - ($detail->diskon_nominal ?? 0);
                                            });
                                        }
                                    ?>
                                    <strong>Rp <?php echo e(number_format($totalPenjualanItem, 0, ',', '.')); ?></strong>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('laporan.penjualan.invoice', $p->id)); ?>" target="_blank" class="btn btn-sm btn-primary" title="Cetak Invoice">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data penjualan</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($penjualan->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($penjualan->withQueryString()->links('vendor.pagination.custom-small')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .table th { white-space: nowrap; }
    .card-title { font-size: 0.9rem; margin-bottom: 0.5rem; }
    .card h3 { font-size: 1.5rem; font-weight: 600; }
    
    /* Memperkecil ukuran pagination - SUPER FORCE */
    .pagination {
        font-size: 0.7rem !important;
        margin: 0 !important;
    }
    
    .pagination .page-link {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.7rem !important;
        line-height: 1 !important;
        min-width: auto !important;
        height: auto !important;
    }
    
    .pagination .page-item {
        margin: 0 1px !important;
    }
    
    /* Memperkecil icon panah di pagination - SUPER FORCE */
    .pagination .page-link svg,
    .pagination .page-link i,
    .pagination .page-link span {
        width: 8px !important;
        height: 8px !important;
        font-size: 8px !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }
    
    /* Target semua elemen di dalam page-link */
    .pagination .page-link * {
        font-size: 8px !important;
        width: 8px !important;
        height: 8px !important;
    }
    
    /* Override Bootstrap default */
    nav[aria-label="Page navigation"] .pagination,
    nav .pagination,
    .card-footer .pagination {
        font-size: 0.7rem !important;
    }
    
    /* Khusus untuk Laravel pagination arrows */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        font-size: 0.6rem !important;
    }
    
    /* Hide text, show only small arrow */
    .pagination .page-item:first-child .page-link::before {
        content: "‹" !important;
        font-size: 10px !important;
    }
    
    .pagination .page-item:last-child .page-link::before {
        content: "›" !important;
        font-size: 10px !important;
    }
    
    .pagination .page-item:first-child .page-link svg,
    .pagination .page-item:last-child .page-link svg {
        display: none !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Perkecil pagination arrows
        setTimeout(function() {
            const paginationLinks = document.querySelectorAll('.pagination .page-link');
            paginationLinks.forEach(function(link) {
                // Ganti SVG dengan text kecil
                const svg = link.querySelector('svg');
                if (svg) {
                    const parent = link.querySelector('.page-item');
                    const isFirst = link.closest('.page-item:first-child');
                    const isLast = link.closest('.page-item:last-child');
                    
                    if (isFirst || link.textContent.includes('Previous') || link.textContent.includes('«')) {
                        link.innerHTML = '<span style="font-size: 10px;">‹</span>';
                    } else if (isLast || link.textContent.includes('Next') || link.textContent.includes('»')) {
                        link.innerHTML = '<span style="font-size: 10px;">›</span>';
                    }
                }
                
                // Paksa style kecil
                link.style.padding = '0.2rem 0.4rem';
                link.style.fontSize = '0.7rem';
                link.style.lineHeight = '1';
            });
        }, 100);
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/laporan/penjualan/index.blade.php ENDPATH**/ ?>