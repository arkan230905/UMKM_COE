<?php $__env->startSection('content'); ?>
<div class="container">
    <h1 class="mb-4">Daftar Bahan Baku</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <a href="<?php echo e(route('master-data.bahan-baku.create')); ?>" class="btn btn-primary mb-3">+ Tambah Bahan Baku</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th width="20%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bahanBaku; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($bahan->id); ?></td>
                    <td><?php echo e($bahan->nama_bahan); ?></td>
                    <td><?php echo e($bahan->satuan ? $bahan->satuan->nama . ' (' . $bahan->satuan->kode . ')' : '-'); ?></td>
                    <td>Rp <?php echo e(number_format($bahan->harga_satuan, 0, ',', '.')); ?></td>
                    <td>
                        <a href="<?php echo e(route('master-data.bahan-baku.edit', $bahan->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form action="<?php echo e(route('master-data.bahan-baku.destroy', $bahan->id)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center">Belum ada data bahan baku.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/bahan-baku/index.blade.php ENDPATH**/ ?>