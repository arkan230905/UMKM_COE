<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Laporan Retur</h3>
    </div>

    <!-- Retur Penjualan Section -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-undo me-2"></i>Retur Penjualan
            </h5>
        </div>
        <div class="card-body">
            <!-- Filter Form for Sales Returns -->
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai (Retur Penjualan)</label>
                    <input type="date" name="sales_start_date" class="form-control" value="<?php echo e(request('sales_start_date')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai (Retur Penjualan)</label>
                    <input type="date" name="sales_end_date" class="form-control" value="<?php echo e(request('sales_end_date')); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?php echo e(route('laporan.retur')); ?>" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
                <!-- Hidden fields to preserve purchase filter -->
                <input type="hidden" name="purchase_start_date" value="<?php echo e(request('purchase_start_date')); ?>">
                <input type="hidden" name="purchase_end_date" value="<?php echo e(request('purchase_end_date')); ?>">
            </form>

            <!-- Summary Card for Sales Returns -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card bg-info text-dark">
                        <div class="card-body">
                            <h6 class="card-title text-dark">Total Retur Penjualan</h6>
                            <h4 class="mb-0 text-dark">Rp <?php echo e(number_format($totalSalesReturns, 0, ',', '.')); ?></h4>
                            <small class="text-dark opacity-75">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('sales_start_date') && request('sales_end_date')): ?>
                                    <?php echo e(\Carbon\Carbon::parse(request('sales_start_date'))->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse(request('sales_end_date'))->format('d/m/Y')); ?>

                                <?php else: ?>
                                    Semua Periode
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Returns Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>No. Retur</th>
                            <th>Tanggal</th>
                            <th>No. Penjualan</th>
                            <th>Item Diretur</th>
                            <th>Alasan</th>
                            <th class="text-end">Total Retur</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $salesReturns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($salesReturns->firstItem() + $index); ?></td>
                                <td><strong><?php echo e($return->return_number ?? '-'); ?></strong></td>
                                <td><?php echo e($return->return_date ? $return->return_date->format('d/m/Y') : '-'); ?></td>
                                <td><?php echo e($return->penjualan->nomor_penjualan ?? '-'); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($return->items && $return->items->count() > 0): ?>
                                        <div class="small">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $return->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="mb-1">
                                                    • <?php echo e($item->penjualanDetail->produk->nama_produk ?? 'Produk'); ?>

                                                    <span class="text-muted">
                                                        (<?php echo e(number_format($item->quantity ?? 0, 0, ',', '.')); ?> pcs)
                                                    </span>
                                                    - Rp <?php echo e(number_format($item->unit_price ?? 0, 0, ',', '.')); ?>

                                                    = <strong>Rp <?php echo e(number_format($item->subtotal ?? 0, 0, ',', '.')); ?></strong>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark"><?php echo e($return->reason ?? '-'); ?></span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp <?php echo e(number_format($return->total_return_amount ?? 0, 0, ',', '.')); ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?php echo e($return->status === 'approved' ? 'bg-success' : ($return->status === 'pending' ? 'bg-warning' : 'bg-secondary')); ?>">
                                        <?php echo e(ucfirst($return->status ?? 'pending')); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data retur penjualan</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($salesReturns->hasPages()): ?>
                <div class="mt-3">
                    <?php echo e($salesReturns->withQueryString()->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Retur Pembelian Section -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-undo me-2"></i>Retur Pembelian
            </h5>
        </div>
        <div class="card-body">
            <!-- Filter Form for Purchase Returns -->
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai (Retur Pembelian)</label>
                    <input type="date" name="purchase_start_date" class="form-control" value="<?php echo e(request('purchase_start_date')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai (Retur Pembelian)</label>
                    <input type="date" name="purchase_end_date" class="form-control" value="<?php echo e(request('purchase_end_date')); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?php echo e(route('laporan.retur')); ?>" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
                <!-- Hidden fields to preserve sales filter -->
                <input type="hidden" name="sales_start_date" value="<?php echo e(request('sales_start_date')); ?>">
                <input type="hidden" name="sales_end_date" value="<?php echo e(request('sales_end_date')); ?>">
            </form>

            <!-- Summary Card for Purchase Returns -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h6 class="card-title text-dark">Total Retur Pembelian</h6>
                            <h4 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPurchaseReturns, 0, ',', '.')); ?></h4>
                            <small class="text-dark opacity-75">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('purchase_start_date') && request('purchase_end_date')): ?>
                                    <?php echo e(\Carbon\Carbon::parse(request('purchase_start_date'))->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse(request('purchase_end_date'))->format('d/m/Y')); ?>

                                <?php else: ?>
                                    Semua Periode
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Returns Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th>No. Retur</th>
                            <th>Tanggal</th>
                            <th>No. Pembelian</th>
                            <th>Vendor</th>
                            <th>Item Diretur</th>
                            <th>Alasan</th>
                            <th class="text-end">Total Retur</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $purchaseReturns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($purchaseReturns->firstItem() + $index); ?></td>
                                <td><strong><?php echo e($return->return_number ?? '-'); ?></strong></td>
                                <td><?php echo e($return->return_date ? $return->return_date->format('d/m/Y') : '-'); ?></td>
                                <td><?php echo e($return->pembelian->nomor_pembelian ?? '-'); ?></td>
                                <td><?php echo e($return->pembelian->vendor->nama_vendor ?? '-'); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($return->items && $return->items->count() > 0): ?>
                                        <div class="small">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $return->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="mb-1">
                                                    • <?php echo e($item->pembelianDetail->bahanBaku->nama_bahan ?? 'Bahan Baku'); ?>

                                                    <span class="text-muted">
                                                        (<?php echo e(number_format($item->quantity ?? 0, 0, ',', '.')); ?> <?php echo e($item->pembelianDetail->bahanBaku->satuan->nama ?? 'unit'); ?>)
                                                    </span>
                                                    - Rp <?php echo e(number_format($item->unit_price ?? 0, 0, ',', '.')); ?>

                                                    = <strong>Rp <?php echo e(number_format($item->subtotal ?? 0, 0, ',', '.')); ?></strong>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark"><?php echo e($return->reason ?? '-'); ?></span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp <?php echo e(number_format($return->total_return_amount ?? 0, 0, ',', '.')); ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?php echo e($return->status === 'approved' ? 'bg-success' : ($return->status === 'pending' ? 'bg-warning' : 'bg-secondary')); ?>">
                                        <?php echo e(ucfirst($return->status ?? 'pending')); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>Tidak ada data retur pembelian</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($purchaseReturns->hasPages()): ?>
                <div class="mt-3">
                    <?php echo e($purchaseReturns->withQueryString()->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/laporan/retur/index.blade.php ENDPATH**/ ?>