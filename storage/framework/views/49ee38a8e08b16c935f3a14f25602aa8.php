<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Data COA</h1>

    <a href="<?php echo e(route('master-data.coa.create')); ?>" class="btn btn-primary mb-3">Tambah COA</a>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Tipe Akun</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($coa->id); ?></td>
                <td><?php echo e($coa->kode_akun); ?></td>
                <td><?php echo e($coa->nama_akun); ?></td>
                <td><?php echo e($coa->tipe_akun); ?></td>
                <td>
                    <a href="<?php echo e(route('master-data.coa.edit', $coa->kode_akun)); ?>" class="btn btn-warning btn-sm">Edit</a>
                    <form action="<?php echo e(route('master-data.coa.destroy', $coa->kode_akun)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/coa/index.blade.php ENDPATH**/ ?>