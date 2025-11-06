<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Data COA</h1>

    <a href="<?php echo e(route('master-data.coa.create')); ?>" class="btn btn-primary mb-3">Tambah COA</a>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="table-responsive table-scroll-x">
        <table class="table table-bordered table-striped align-middle table-wide table-nowrap">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Kategori Akun</th>
                    <th>Kode Induk</th>
                    <th>Saldo Normal</th>
                    <th>Saldo Awal</th>
                    <th class="col-keterangan">Keterangan</th>
                    <th style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($coa->id); ?></td>
                    <td><?php echo e($coa->kode_akun); ?></td>
                    <td><?php echo e($coa->nama_akun); ?></td>
                    <td><?php echo e($coa->kategori_akun); ?></td>
                    <td><?php echo e($coa->kode_induk); ?></td>
                    <td class="text-capitalize"><?php echo e($coa->saldo_normal); ?></td>
                    <td>Rp <?php echo e(number_format((float)($coa->saldo_awal ?? 0), 0, ',', '.')); ?></td>
                    <td class="col-keterangan"><small class="text-muted"><?php echo e($coa->keterangan); ?></small></td>
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
</div>
<?php $__env->stopSection(); ?>

<style>
    .table-scroll-x { overflow-x: auto !important; width: 100%; }
    .table-wide { width: max-content; min-width: 1600px; }
    .table-nowrap th, .table-nowrap td { white-space: nowrap; }
    .table-nowrap .col-keterangan { white-space: normal; min-width: 260px; }
</style>

<!-- No custom JS: rely on native horizontal scrollbar/trackpad like tabel Pegawai -->
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/coa/index.blade.php ENDPATH**/ ?>