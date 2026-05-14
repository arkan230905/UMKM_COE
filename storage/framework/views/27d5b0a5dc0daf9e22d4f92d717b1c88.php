<!-- Filter Form for Purchase Returns -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <input type="hidden" name="tab" value="retur">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="purchase_start_date" class="form-control" value="<?php echo e(request('purchase_start_date')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="purchase_end_date" class="form-control" value="<?php echo e(request('purchase_end_date')); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="purchase_status" class="form-control">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo e(request('purchase_status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="disetujui" <?php echo e(request('purchase_status') == 'disetujui' ? 'selected' : ''); ?>>Disetujui</option>
                    <option value="dikirim" <?php echo e(request('purchase_status') == 'dikirim' ? 'selected' : ''); ?>>Dikirim</option>
                    <option value="selesai" <?php echo e(request('purchase_status') == 'selesai' ? 'selected' : ''); ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="<?php echo e(route('laporan.pembelian.index')); ?>?tab=retur" class="btn btn-secondary w-100">
                    <i class="fas fa-redo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Card for Purchase Returns -->
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="card-title text-dark">Total Retur Pembelian</h6>
                <h4 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPurchaseReturns ?? 0, 0, ',', '.')); ?></h4>
                <small class="text-dark opacity-75">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('purchase_start_date') && request('purchase_end_date')): ?>
                        <?php echo e(\Carbon\Carbon::parse(request('purchase_start_date'))->format('d/m/Y')); ?> - <?php echo e(\Carbon\Carbon::parse(request('purchase_end_date'))->format('d/m/Y')); ?>

                    <?php else: ?>
                        Semua Periode
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <br><i class="fas fa-info-circle me-1"></i>Sudah termasuk PPN (sesuai pembelian)
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Returns Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-retur">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:5%">No</th>
                        <th class="text-center nowrap">No. Retur</th>
                        <th class="text-center nowrap">Tanggal</th>
                        <th class="text-center nowrap">No. Transaksi</th>
                        <th class="text-center nowrap">Vendor</th>
                        <th class="text-center nowrap">Jenis Retur</th>
                        <th class="text-center">Item Diretur</th>
                        <th class="text-center">Alasan</th>
                        <th class="text-center nowrap">Total Retur</th>
                        <th class="text-center nowrap">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $purchaseReturns ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e(($purchaseReturns->firstItem() ?? 0) + $index); ?></td>
                            <td class="text-center nowrap"><strong><?php echo e($return->return_number ?? 'RET-' . str_pad($return->id, 4, '0', STR_PAD_LEFT)); ?></strong></td>
                            <td class="text-center nowrap"><?php echo e($return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d/m/Y') : '-'); ?></td>
                            <td class="text-center nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($return->pembelian): ?>
                                    <strong><?php echo e($return->pembelian->nomor_pembelian); ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center nowrap"><?php echo e($return->pembelian->vendor->nama_vendor ?? '-'); ?></td>
                            <td class="text-center nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($return->jenis_retur === 'tukar_barang'): ?>
                                    Tukar Barang
                                <?php elseif($return->jenis_retur === 'refund'): ?>
                                    Refund
                                <?php else: ?>
                                    <?php echo e($return->jenis_retur ?? 'Tidak Diketahui'); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($return->items && $return->items->count() > 0): ?>
                                    <div class="small">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $return->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="mb-1">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->bahanBaku): ?>
                                                    • <span class="text-primary fw-semibold">BB</span> <?php echo e($item->bahanBaku->nama_bahan); ?>

                                                <?php elseif($item->bahanPendukung): ?>
                                                    • <span class="text-info fw-semibold">BP</span> <?php echo e($item->bahanPendukung->nama_bahan); ?>

                                                <?php else: ?>
                                                    • Item tidak diketahui
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <span class="text-muted">
                                                    (<?php echo e(number_format($item->quantity ?? 0, 2)); ?> <?php echo e($item->unit ?? 'unit'); ?>)
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
                            <td class="text-center"><?php echo e($return->reason ?? '-'); ?></td>
                            <td class="text-center">
                                <?php
                                    $subtotal = $return->total_retur ?? 0;
                                    // Get PPN from pembelian, default to 11% if not set
                                    $ppnPersen = $return->pembelian->ppn_persen ?? 11;
                                    $ppnAmount = $subtotal * ($ppnPersen / 100);
                                    $totalWithPpn = $subtotal + $ppnAmount;
                                ?>
                                <div class="small text-muted">
                                    Subtotal: Rp <?php echo e(number_format($subtotal, 0, ',', '.')); ?><br>
                                    PPN <?php echo e($ppnPersen); ?>%: Rp <?php echo e(number_format($ppnAmount, 0, ',', '.')); ?>

                                </div>
                                <strong class="text-primary">Rp <?php echo e(number_format($totalWithPpn, 0, ',', '.')); ?></strong>
                            </td>
                            <td class="text-center">
                                <?php
                                    $statusBadge = $return->status_badge ?? ['class' => 'bg-secondary', 'text' => ucfirst($return->status ?? 'Unknown')];
                                ?>
                                <?php echo e($statusBadge['text']); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
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
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($purchaseReturns) && $purchaseReturns->hasPages()): ?>
        <div class="card-footer">
            <?php echo e($purchaseReturns->withQueryString()->links()); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/laporan/pembelian/partials/retur-content.blade.php ENDPATH**/ ?>