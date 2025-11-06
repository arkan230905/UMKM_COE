<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h2>Daftar Satuan</h2>

    <a href="<?php echo e(route('master-data.satuan.create')); ?>" class="btn btn-primary mb-3">Tambah Satuan</a>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($item->id); ?></td>
                    <td><?php echo e($item->kode); ?></td>
                    <td><?php echo e($item->nama); ?></td>
                    <td>
                        <a href="<?php echo e(route('master-data.satuan.edit', $item->id)); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form action="<?php echo e(route('master-data.satuan.destroy', $item->id)); ?>" method="POST" style="display:inline-block;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" onclick="return confirm('Hapus data ini?')" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data satuan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/satuan/index.blade.php ENDPATH**/ ?>