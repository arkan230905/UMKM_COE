<?php $__env->startSection('title', 'Daftar Bahan Baku'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2"></i>Bahan Baku
        </h2>
        <a href="<?php echo e(route('master-data.bahan-baku.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Bahan Baku
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar Bahan Baku
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Satuan Utama</th>
                            <th class="text-end">Stok Saat Ini</th>
                            <th class="text-end">Harga Satuan Utama</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bahanBaku; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-box text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($bahan->nama_bahan); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahan->satuan): ?>
                                        <?php echo e($bahan->satuan->nama); ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php
                                        $stokSaatIni = $bahan->stok_real_time ?? 0;
                                        $stokMinimum = $bahan->stok_minimum ?? 0;
                                    ?>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stokSaatIni <= 0): ?>
                                        <span class="badge bg-danger"><?php echo e(number_format($stokSaatIni, 2, ',', '.')); ?></span>
                                        <small class="text-danger d-block">Habis</small>
                                    <?php elseif($stokSaatIni <= $stokMinimum): ?>
                                        <span class="badge bg-warning"><?php echo e(number_format($stokSaatIni, 2, ',', '.')); ?></span>
                                        <small class="text-warning d-block">Hampir Habis</small>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo e(number_format($stokSaatIni, 2, ',', '.')); ?></span>
                                        <small class="text-success d-block">Aman</small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp <?php echo e(number_format($bahan->harga_satuan_display ?? $bahan->harga_satuan ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('master-data.bahan-baku.show', $bahan->id)); ?>" class="btn btn-outline-info" title="Detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <form action="<?php echo e(route('master-data.bahan-baku.destroy', $bahan->id)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus bahan baku \'<?php echo e($bahan->nama_bahan); ?>\'?\n\nPerhatian: Data tidak dapat dihapus jika masih digunakan di BOM, Pembelian, atau Produksi.')" title="Hapus">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data bahan baku</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/bahan-baku/index.blade.php ENDPATH**/ ?>