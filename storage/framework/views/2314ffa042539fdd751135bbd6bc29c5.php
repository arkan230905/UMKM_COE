<?php $__env->startSection('title', 'Laporan Pelunasan Utang'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Laporan Pelunasan Utang</h5>
                    
                    
                    
                    
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->has('bulan')): ?>
                        <a href="<?php echo e(route('laporan.pelunasan-utang', ['bulan' => request('bulan'), 'export' => 'pdf'])); ?>" 
                           class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    <?php else: ?>
                        <span class="text-muted small">Pilih bulan untuk export PDF</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('laporan.pelunasan-utang')); ?>" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bulan">Pilih Bulan</label>
                                    <input type="month" name="bulan" id="bulan" class="form-control" 
                                           value="<?php echo e(request('bulan', now()->format('Y-m'))); ?>">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="<?php echo e(route('laporan.pelunasan-utang')); ?>" class="btn btn-secondary ml-2">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Pelunasan</th>
                                    <th>Vendor</th>
                                    <th>No. Faktur</th>
                                    <th>Total Tagihan</th>
                                    <th>Dibayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pelunasanUtang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($item->tanggal)->format('d/m/Y')); ?></td>
                                    <td><?php echo e($item->kode_transaksi); ?></td>
                                    <td><?php echo e($item->pembelian->vendor->nama_vendor ?? '-'); ?></td>
                                    <td><?php echo e($item->pembelian->nomor_faktur ?? $item->pembelian->nomor_pembelian ?? '-'); ?></td>
                                    <td class="text-right">Rp <?php echo e(number_format($item->pembelian->total_harga ?? 0, 0, ',', '.')); ?></td>
                                    <td class="text-right">Rp <?php echo e(number_format($item->jumlah, 0, ',', '.')); ?></td>
                                    <td>
                                        <?php
                                            $statusPembayaran = $item->pembelian->status_pembayaran;
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusPembayaran === 'Lunas'): ?>
                                            <span class="badge badge-success">Lunas</span>
                                        <?php elseif($statusPembayaran === 'Sebagian'): ?>
                                            <span class="badge badge-warning">Sebagian</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Belum Bayar</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pelunasan utang</td>
                                </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Total</th>
                                    <th class="text-right">
                                        Rp <?php echo e(number_format($pelunasanUtang->sum(function($item) { return $item->pembelian->total_harga ?? 0; }), 0, ',', '.')); ?>

                                    </th>
                                    <th class="text-right">Rp <?php echo e(number_format($total, 0, ',', '.')); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/laporan/pelunasan-utang/index.blade.php ENDPATH**/ ?>