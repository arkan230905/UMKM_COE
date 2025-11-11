<?php $__env->startSection('title', 'Daftar Pembelian'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Daftar Pembelian</h2>
        <a href="<?php echo e(route('transaksi.pembelian.create')); ?>" class="btn btn-primary">+ Tambah Pembelian</a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Tanggal</th>
                <th>Vendor</th>
                <th>Item Dibeli</th>
                <th>Pembayaran</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $pembelians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pembelian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($pembelian->tanggal->format('d-m-Y')); ?></td>
                    <td><?php echo e($pembelian->vendor->nama_vendor ?? '-'); ?></td>
                    <td>
                        <?php if($pembelian->details && $pembelian->details->count() > 0): ?>
                            <small>
                            <?php $__currentLoopData = $pembelian->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    • <?php echo e($detail->bahanBaku->nama_bahan ?? '-'); ?> 
                                    (<?php echo e(number_format($detail->jumlah ?? 0, 0, ',', '.')); ?> <?php echo e($detail->bahanBaku->satuan->nama ?? 'unit'); ?>)
                                    - Rp <?php echo e(number_format($detail->harga_satuan ?? 0, 0, ',', '.')); ?>

                                    = <strong>Rp <?php echo e(number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.')); ?></strong>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </small>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo e(($pembelian->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai'); ?></td>
                    <td>
                        <?php
                            $totalPembelian = $pembelian->total;
                            // Jika total = 0, hitung dari details
                            if ($totalPembelian == 0 && $pembelian->details && $pembelian->details->count() > 0) {
                                $totalPembelian = $pembelian->details->sum(function($detail) {
                                    return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                });
                            }
                        ?>
                        <strong>Rp <?php echo e(number_format($totalPembelian, 0, ',', '.')); ?></strong>
                    </td>
                    <td>
                        <a href="<?php echo e(route('transaksi.pembelian.show', $pembelian->id)); ?>" class="btn btn-info btn-sm">Detail</a>
                        <form action="<?php echo e(route('transaksi.pembelian.destroy', $pembelian->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center">Belum ada data pembelian.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/pembelian/index.blade.php ENDPATH**/ ?>