<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-flask me-2"></i>Detail Bahan Pendukung
            <small class="text-muted fw-normal">- <?php echo e($bahanPendukung->nama_bahan); ?></small>
        </h2>
        <div class="btn-group">
            <a href="<?php echo e(route('master-data.bahan-pendukung.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="<?php echo e(route('master-data.bahan-pendukung.edit', $bahanPendukung->id)); ?>" class="btn btn-warning">
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
        <div class="card-header bg-warning text-white">
            <h6 class="mb-0">
                <i class="fas fa-flask me-2"></i>Informasi Utama
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Bahan:</strong></td>
                            <td>
                                <span class="fw-semibold"><?php echo e($bahanPendukung->nama_bahan); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Satuan Utama:</strong></td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanPendukung->satuan): ?>
                                    <span class="badge bg-info"><?php echo e($bahanPendukung->satuan->nama); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Harga Satuan Utama:</strong></td>
                            <td>
                                <span class="fw-bold text-success">Rp <?php echo e(number_format($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0, 0, ',', '.')); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Stok Saat Ini:</strong></td>
                            <td>
                                <span class="fw-semibold"><?php echo e($bahanPendukung->stok ? rtrim(rtrim(number_format($bahanPendukung->stok, 5, ',', '.'), '0'), ',') : '0'); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($bahanPendukung->stok ?? 0) <= ($bahanPendukung->stok_minimum ?? 0) && ($bahanPendukung->stok ?? 0) > 0): ?>
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Stok hampir habis"></i>
                                <?php elseif(($bahanPendukung->stok ?? 0) <= 0): ?>
                                    <i class="fas fa-times-circle text-danger ms-1" title="Stok habis"></i>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Stok Minimum:</strong></td>
                            <td>
                                <span class="text-muted"><?php echo e($bahanPendukung->stok_minimum ? rtrim(rtrim(number_format($bahanPendukung->stok_minimum, 5, ',', '.'), '0'), ',') : '0'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td>
                                <span class="text-muted"><?php echo e($bahanPendukung->deskripsi ?: '-'); ?></span>
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
            <div class="row">
                <!-- Sub Satuan 1 -->
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-cube me-2"></i>Sub Satuan 1
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanPendukung->subSatuan1 && ($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0) > 0): ?>
                                <?php
                                    $hargaUtama = $bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0;
                                    $konversi1 = $bahanPendukung->sub_satuan_1_konversi ?? 1;
                                    $nilai1 = $bahanPendukung->sub_satuan_1_nilai ?? 1;
                                    $hargaSubSatuan1 = ($konversi1 * $hargaUtama) / $nilai1;
                                ?>
                                <div class="text-center mb-3">
                                    <h5 class="text-primary fw-bold">
                                        Rp <?php echo e(number_format($hargaSubSatuan1, 0, ',', '.')); ?>

                                    </h5>
                                    <small class="text-muted">per <?php echo e($bahanPendukung->subSatuan1->nama); ?></small>
                                </div>
                                <div class="alert alert-info">
                                    <small class="mb-0">
                                        <strong><?php echo e(rtrim(rtrim(number_format($konversi1, 5, ',', '.'), '0'), ',')); ?> <?php echo e($bahanPendukung->satuan ? $bahanPendukung->satuan->nama : ''); ?> = <?php echo e(rtrim(rtrim(number_format($nilai1, 5, ',', '.'), '0'), ',')); ?> <?php echo e($bahanPendukung->subSatuan1->nama); ?></strong>
                                    </small>
                                </div>
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">
                                        <strong>Rumus:</strong><br>
                                        (<?php echo e(rtrim(rtrim(number_format($konversi1, 5, ',', '.'), '0'), ',')); ?> × Rp <?php echo e(number_format($hargaUtama, 0, ',', '.')); ?>) ÷ <?php echo e(rtrim(rtrim(number_format($nilai1, 5, ',', '.'), '0'), ',')); ?> = Rp <?php echo e(number_format($hargaSubSatuan1, 0, ',', '.')); ?>

                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-cube fa-2x mb-2"></i>
                                    <p class="mb-0">Sub Satuan 1 tidak tersedia</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sub Satuan 2 -->
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-success">
                                <i class="fas fa-cube me-2"></i>Sub Satuan 2
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanPendukung->subSatuan2 && ($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0) > 0): ?>
                                <?php
                                    $hargaUtama = $bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0;
                                    $konversi2 = $bahanPendukung->sub_satuan_2_konversi ?? 1;
                                    $nilai2 = $bahanPendukung->sub_satuan_2_nilai ?? 1;
                                    $hargaSubSatuan2 = ($konversi2 * $hargaUtama) / $nilai2;
                                ?>
                                <div class="text-center mb-3">
                                    <h5 class="text-success fw-bold">
                                        Rp <?php echo e(number_format($hargaSubSatuan2, 0, ',', '.')); ?>

                                    </h5>
                                    <small class="text-muted">per <?php echo e($bahanPendukung->subSatuan2->nama); ?></small>
                                </div>
                                <div class="alert alert-success">
                                    <small class="mb-0">
                                        <strong><?php echo e(rtrim(rtrim(number_format($konversi2, 5, ',', '.'), '0'), ',')); ?> <?php echo e($bahanPendukung->satuan ? $bahanPendukung->satuan->nama : ''); ?> = <?php echo e(rtrim(rtrim(number_format($nilai2, 5, ',', '.'), '0'), ',')); ?> <?php echo e($bahanPendukung->subSatuan2->nama); ?></strong>
                                    </small>
                                </div>
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">
                                        <strong>Rumus:</strong><br>
                                        (<?php echo e(rtrim(rtrim(number_format($konversi2, 5, ',', '.'), '0'), ',')); ?> × Rp <?php echo e(number_format($hargaUtama, 0, ',', '.')); ?>) ÷ <?php echo e(rtrim(rtrim(number_format($nilai2, 5, ',', '.'), '0'), ',')); ?> = Rp <?php echo e(number_format($hargaSubSatuan2, 0, ',', '.')); ?>

                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-cube fa-2x mb-2"></i>
                                    <p class="mb-0">Sub Satuan 2 tidak tersedia</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sub Satuan 3 -->
                <div class="col-md-4">
                    <div class="card border-warning">
                        <div class="card-header bg-light">
                            <h6 class="mb-0 text-warning">
                                <i class="fas fa-cube me-2"></i>Sub Satuan 3
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahanPendukung->subSatuan3 && ($bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0) > 0): ?>
                                <?php
                                    $hargaUtama = $bahanPendukung->harga_satuan_display ?? $bahanPendukung->harga_satuan ?? 0;
                                    $konversi3 = $bahanPendukung->sub_satuan_3_konversi ?? 1;
                                    $nilai3 = $bahanPendukung->sub_satuan_3_nilai ?? 1;
                                    $hargaSubSatuan3 = ($konversi3 * $hargaUtama) / $nilai3;
                                ?>
                                <div class="text-center mb-3">
                                    <h5 class="text-warning fw-bold">
                                        Rp <?php echo e(number_format($hargaSubSatuan3, 0, ',', '.')); ?>

                                    </h5>
                                    <small class="text-muted">per <?php echo e($bahanPendukung->subSatuan3->nama); ?></small>
                                </div>
                                <div class="alert alert-warning">
                                    <small class="mb-0">
                                        <strong><?php echo e(rtrim(rtrim(number_format($konversi3, 5, ',', '.'), '0'), ',')); ?> <?php echo e($bahanPendukung->satuan ? $bahanPendukung->satuan->nama : ''); ?> = <?php echo e(rtrim(rtrim(number_format($nilai3, 5, ',', '.'), '0'), ',')); ?> <?php echo e($bahanPendukung->subSatuan3->nama); ?></strong>
                                    </small>
                                </div>
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">
                                        <strong>Rumus:</strong><br>
                                        (<?php echo e(rtrim(rtrim(number_format($konversi3, 5, ',', '.'), '0'), ',')); ?> × Rp <?php echo e(number_format($hargaUtama, 0, ',', '.')); ?>) ÷ <?php echo e(rtrim(rtrim(number_format($nilai3, 5, ',', '.'), '0'), ',')); ?> = Rp <?php echo e(number_format($hargaSubSatuan3, 0, ',', '.')); ?>

                                    </small>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-cube fa-2x mb-2"></i>
                                    <p class="mb-0">Sub Satuan 3 tidak tersedia</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bahan-pendukung/show.blade.php ENDPATH**/ ?>