<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Master Data BOP (Biaya Overhead Pabrik)</h2>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Aktual</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $akunBeban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $akun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $bop = $bops[$akun->kode_akun] ?? null;
                                $hasBudget = $bop && $bop->budget > 0;
                                $sisa = $hasBudget ? ($bop->budget - ($bop->aktual ?? 0)) : 0;
                                $textClass = $sisa < 0 ? 'text-danger' : 'text-success';
                            ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($akun->kode_akun); ?></td>
                                <td><?php echo e($akun->nama_akun); ?></td>
                                <td class="text-end"><?php echo e($hasBudget ? number_format($bop->budget, 0, ',', '.') : '-'); ?></td>
                                <td class="text-end"><?php echo e($hasBudget ? number_format($bop->aktual ?? 0, 0, ',', '.') : '-'); ?></td>
                                <td class="text-end <?php echo e($textClass); ?>">
                                    <?php echo e($hasBudget ? number_format($sisa, 0, ',', '.') : '-'); ?>

                                </td>
                                <td class="text-center">
                                    <?php if($hasBudget): ?>
                                        <a href="<?php echo e(route('master-data.bop.edit', $bop->id)); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <form action="<?php echo e(route('master-data.bop.destroy', $bop->id)); ?>" 
                                              method="POST" 
                                              class="d-inline delete-bop-form"
                                              data-bop-id="<?php echo e($bop->id); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="button" class="btn btn-sm btn-danger delete-bop-btn" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Hapus Budget">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('master-data.bop.create', ['kode_akun' => $akun->kode_akun])); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Input
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data akun beban</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete button
        document.querySelectorAll('.delete-bop-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                if (confirm('Apakah Anda yakin ingin menghapus budget BOP ini?')) {
                    form.submit();
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/bop/index.blade.php ENDPATH**/ ?>