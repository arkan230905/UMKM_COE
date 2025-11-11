<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Laporan Pelunasan Utang</h5>
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
                                <?php $__empty_1 = true; $__currentLoopData = $pelunasanUtang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($item->tanggal)->format('d/m/Y')); ?></td>
                                    <td>PU-<?php echo e($item->id); ?></td>
                                    <td><?php echo e($item->pembelian->vendor->nama_vendor ?? ($item->vendor->nama_vendor ?? '-')); ?></td>
                                    <td>PB-<?php echo e($item->pembelian_id); ?></td>
                                    <td class="text-right">Rp <?php echo e(number_format($item->total_tagihan, 0, ',', '.')); ?></td>
                                    <td class="text-right">Rp <?php echo e(number_format($item->dibayar_bersih, 0, ',', '.')); ?></td>
                                    <td>
                                        <?php
                                            $pembelian = $item->pembelian;
                                            $isLunas = $pembelian && $pembelian->status == 'lunas';
                                        ?>
                                        <?php if($isLunas): ?>
                                            <span class="badge badge-success">Lunas</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Belum Lunas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pelunasan utang</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Total</th>
                                    <th class="text-right"><?php echo e(format_rupiah($pelunasanUtang->sum('total_tagihan'))); ?></th>
                                    <th class="text-right"><?php echo e(format_rupiah($total)); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="<?php echo e(route('laporan.pelunasan-utang', ['bulan' => request('bulan'), 'export' => 'pdf'])); ?>" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/laporan/pelunasan-utang/index.blade.php ENDPATH**/ ?>