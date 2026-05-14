<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="GET" class="row g-3">
            <input type="hidden" name="tab" value="pembelian">
            <div class="col-md-4">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo e(request('start_date')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo e(request('end_date')); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select">
                    <option value="">Semua Vendor</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($vendor->id); ?>" <?php echo e(request('vendor_id') == $vendor->id ? 'selected' : ''); ?>>
                            <?php echo e($vendor->nama_vendor); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<div class="row mb-4 summary-grid">
    <div class="col">
        <div class="card bg-primary text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian</h5>
                <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPembelianFiltered, 0, ',', '.')); ?></h3>
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
    <div class="col">
        <div class="card bg-success text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian Tunai</h5>
                <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPembelianTunai, 0, ',', '.')); ?></h3>
                <small class="text-dark opacity-75">Pembayaran Cash</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-info text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian Kredit</h5>
                <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPembelianKredit, 0, ',', '.')); ?></h3>
                <small class="text-dark opacity-75">Pembayaran Kredit</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-secondary text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian Non Tunai</h5>
                <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPembelianNonTunai, 0, ',', '.')); ?></h3>
                <small class="text-dark opacity-75">Pembayaran Transfer</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title text-dark">Total Pembelian Belum Lunas</h5>
                <h3 class="mb-0 text-dark">Rp <?php echo e(number_format($totalPembelianBelumLunas, 0, ',', '.')); ?></h3>
                <small class="text-dark opacity-75">Sisa Utang</small>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-pembelian">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:5%">No</th>
                        <th class="text-center nowrap">No. Transaksi</th>
                        <th class="text-center nowrap">Tanggal</th>
                        <th class="text-center nowrap">Vendor</th>
                        <th class="text-center">Item Dibeli</th>
                        <th class="text-center nowrap">Total</th>
                        <th class="text-center nowrap">Metode Pembayaran</th>
                        <th class="text-center nowrap">Status</th>
                        <th class="text-center nowrap" style="width:12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e($pembelian->firstItem() + $index); ?></td>
                            <td class="text-center nowrap"><strong><?php echo e($p->nomor_pembelian ?? '-'); ?></strong></td>
                            <td class="text-center nowrap"><?php echo e(optional($p->tanggal)->format('d/m/Y') ?? '-'); ?></td>
                            <td class="text-center nowrap"><?php echo e($p->vendor->nama_vendor ?? '-'); ?></td>
                            <td class="text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->details && $p->details->count() > 0): ?>
                                    <div class="small text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $p->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="mb-1 text-center">
                                                • 
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->tipe_item === 'bahan_baku' && $detail->bahanBaku): ?>
                                                    <?php echo e($detail->bahanBaku->nama_bahan); ?>

                                                <?php elseif($detail->tipe_item === 'bahan_pendukung' && $detail->bahanPendukung): ?>
                                                    <?php echo e($detail->bahanPendukung->nama_bahan); ?>

                                                <?php elseif($detail->bahan_pendukung_id && $detail->bahanPendukung): ?>
                                                    <?php echo e($detail->bahanPendukung->nama_bahan); ?>

                                                <?php elseif($detail->bahan_baku_id && $detail->bahanBaku): ?>
                                                    <?php echo e($detail->bahanBaku->nama_bahan); ?>

                                                <?php else: ?>
                                                    Item
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php
                                                    $qty = $detail->jumlah ?? 0;
                                                    // Remove .00 from whole numbers
                                                    $qtyFormatted = ($qty == floor($qty)) ? number_format($qty, 0, ',', '.') : number_format($qty, 2, ',', '.');
                                                ?>
                                                <span class="text-muted">- <?php echo e($qtyFormatted); ?></span>
                                                - Rp <?php echo e(number_format($detail->harga_satuan ?? 0, 0, ',', '.')); ?>

                                                = <strong>Rp <?php echo e(number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.')); ?></strong>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Detail tidak tersedia
                                    </span>
                                    <div class="small text-muted text-center mt-1">
                                        Total: Rp <?php echo e(number_format($p->total_harga ?? 0, 0, ',', '.')); ?>

                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php
                                    $totalPembelian = 0;
                                    if ($p->details && $p->details->count() > 0) {
                                        $totalPembelian = $p->details->sum(function($detail) {
                                            return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                        });
                                    }
                                    if ($p->total_harga > $totalPembelian) {
                                        $totalPembelian = $p->total_harga;
                                    }
                                ?>
                                <strong>Rp <?php echo e(number_format($totalPembelian, 0, ',', '.')); ?></strong>
                            </td>
                            <td class="text-center">
                                <?php
                                    $paymentMethodText = '';
                                    switch($p->payment_method) {
                                        case 'cash':
                                            $paymentMethodText = 'Tunai';
                                            break;
                                        case 'transfer':
                                            $paymentMethodText = 'Transfer';
                                            break;
                                        case 'credit':
                                            $paymentMethodText = 'Kredit';
                                            break;
                                        default:
                                            $paymentMethodText = ucfirst($p->payment_method ?? 'Tunai');
                                    }
                                ?>
                                <?php echo e($paymentMethodText); ?>

                            </td>
                            <td class="text-center">
                                <?php
                                    $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $p->id)->exists();
                                    $statusText = $hasRetur ? 'Ada Retur' : 'Tidak Ada Retur';
                                ?>
                                <?php echo e($statusText); ?>

                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="<?php echo e(route('transaksi.pembelian.show', $p)); ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('laporan.pembelian.invoice', $p)); ?>" target="_blank" class="btn btn-sm btn-primary" title="Cetak Invoice">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Tidak ada data pembelian</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembelian->hasPages()): ?>
        <div class="card-footer">
            <?php echo e($pembelian->withQueryString()->links('vendor.pagination.custom-small')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/laporan/pembelian/partials/pembelian-content.blade.php ENDPATH**/ ?>