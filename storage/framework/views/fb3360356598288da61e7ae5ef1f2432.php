<?php $__env->startSection('title', 'Detail Bahan Baku'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-boxes me-2"></i>Detail Bahan Baku
            <small class="text-muted fw-normal">- <?php echo e($bahanBaku->nama_bahan); ?></small>
        </h2>
        <div class="btn-group">
            <a href="<?php echo e(route('master-data.bahan-baku.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="<?php echo e(route('master-data.bahan-baku.edit', $bahanBaku->id)); ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Main Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-box me-2"></i>Informasi Utama
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Bahan:</strong></td>
                            <td>
                                <span class="fw-semibold"><?php echo e($bahanBaku->nama_bahan); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Satuan Utama:</strong></td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanBaku->satuan): ?>
                                    <?php echo e($bahanBaku->satuan->nama); ?>

                                <?php else: ?>
                                    -
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Harga Satuan Utama:</strong></td>
                            <td>
                                <span class="fw-bold text-success">Rp <?php echo e(number_format($bahanBaku->harga_satuan_display ?? $bahanBaku->harga_satuan ?? 0, 0, ',', '.')); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Stok Saat Ini:</strong></td>
                            <td>
                                <span class="fw-semibold"><?php echo e($bahanBaku->stok_real_time ? rtrim(rtrim(number_format($bahanBaku->stok_real_time, 5, ',', '.'), '0'), ',') : '0'); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($bahanBaku->stok_real_time ?? 0) <= ($bahanBaku->stok_minimum ?? 0) && ($bahanBaku->stok_real_time ?? 0) > 0): ?>
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stok hampir habis"></i>
                                <?php elseif(($bahanBaku->stok_real_time ?? 0) <= 0): ?>
                                    <i class="fas fa-times-circle text-danger ms-1" title="Stok habis"></i>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Stok Minimum:</strong></td>
                            <td>
                                <span class="text-muted"><?php echo e($bahanBaku->stok_minimum ? rtrim(rtrim(number_format($bahanBaku->stok_minimum, 5, ',', '.'), '0'), ',') : '0'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td>
                                <span class="text-muted"><?php echo e($bahanBaku->deskripsi ?: '-'); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Konversi Satuan Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-exchange-alt me-2"></i>Konversi Satuan
            </h6>
        </div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($subSatuanPrices) > 0): ?>
                <div class="row">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $subSatuanPrices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $subSatuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4">
                            <div class="card border-<?php echo e($index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning')); ?>">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-<?php echo e($index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning')); ?>">
                                        <i class="fas fa-cube me-2"></i>Sub Satuan <?php echo e($index + 1); ?>

                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h5 class="text-<?php echo e($index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning')); ?> fw-bold">
                                            Rp <?php echo e(number_format($subSatuan['harga_per_unit'], 0, ',', '.')); ?>

                                        </h5>
                                        <small class="text-muted">per <?php echo e($subSatuan['satuan_nama']); ?></small>
                                    </div>
                                    <div class="alert alert-<?php echo e($index == 0 ? 'primary' : ($index == 1 ? 'success' : 'warning')); ?>">
                                        <small class="mb-0">
                                            <strong><?php echo e($subSatuan['konversi_text']); ?></strong>
                                        </small>
                                    </div>
                                    <div class="bg-light p-2 rounded">
                                        <p class="mb-0">
                                            <strong>Rumus:</strong> <?php echo e($subSatuan['formula_text']); ?>

                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-cube fa-3x mb-3"></i>
                    <h5>Tidak Ada Sub Satuan</h5>
                    <p class="mb-0">Bahan baku ini belum memiliki konversi sub satuan</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- COA Information Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-book me-2"></i>Akun COA
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-success fw-bold">COA Pembelian</h6>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanBaku->coaPembelian): ?>
                            <div class="fw-semibold"><?php echo e($bahanBaku->coaPembelian->nama_akun); ?></div>
                            <small class="text-muted"><?php echo e($bahanBaku->coaPembelian->kode_akun); ?></small>
                        <?php else: ?>
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-info fw-bold">COA Persediaan</h6>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanBaku->coaPersediaan): ?>
                            <div class="fw-semibold"><?php echo e($bahanBaku->coaPersediaan->nama_akun); ?></div>
                            <small class="text-muted"><?php echo e($bahanBaku->coaPersediaan->kode_akun); ?></small>
                        <?php else: ?>
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <h6 class="text-warning fw-bold">COA HPP</h6>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanBaku->coaHpp): ?>
                            <div class="fw-semibold"><?php echo e($bahanBaku->coaHpp->nama_akun); ?></div>
                            <small class="text-muted"><?php echo e($bahanBaku->coaHpp->kode_akun); ?></small>
                        <?php else: ?>
                            <div class="text-muted">-</div>
                            <small class="text-muted">Tidak ada COA</small>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .alert {
        border: none;
        border-radius: 0.5rem;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/bahan-baku/show.blade.php ENDPATH**/ ?>