<?php $__env->startSection('content'); ?>
<div class="container">
    <h1 class="mb-4">Data Produk</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <a href="<?php echo e(route('master-data.produk.create')); ?>" class="btn btn-primary mb-3">Tambah Produk</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Produk</th>
                <th>Harga BOM</th>
                <th>Presentase Keuntungan</th>
                <th>Harga Jual</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $bom = (float) ($hargaBom[$produk->id] ?? 0);
                    $margin = (float) ($produk->margin_percent ?? 0);
                    $hargaJualHitung = $bom * (1 + $margin/100);
                ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($produk->nama_produk); ?></td>
                    <td>Rp <?php echo e(number_format($bom, 0, ',', '.')); ?></td>
                    <td><?php echo e(rtrim(rtrim(number_format($margin,2,',','.'),'0'),',')); ?>%</td>
                    <td>Rp <?php echo e(number_format($hargaJualHitung, 0, ',', '.')); ?></td>
                    <td>
                        <a href="<?php echo e(route('master-data.produk.edit', $produk->id)); ?>" class="btn btn-warning btn-sm">Edit</a>
                        <form action="<?php echo e(route('master-data.produk.destroy', $produk->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center">Belum ada produk</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/produk/index.blade.php ENDPATH**/ ?>