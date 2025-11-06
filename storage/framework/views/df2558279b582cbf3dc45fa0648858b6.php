<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Laporan Pembelian</h3>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Tanggal</th>
                    <th>Vendor</th>
                    <th class="text-end">Total</th>
                    <th style="width:15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $pembelian; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><?php echo e(optional($p->tanggal)->format('d-m-Y') ?? $p->tanggal); ?></td>
                        <td><?php echo e($p->vendor->nama_vendor ?? '-'); ?></td>
                        <td class="text-end">Rp <?php echo e(number_format($p->total, 0, ',', '.')); ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?php echo e(route('laporan.pembelian.invoice', $p->id)); ?>">
                                Cetak Invoice
                            </a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/laporan/pembelian/index.blade.php ENDPATH**/ ?>