

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Detail Perhitungan Biaya Bahan
            <small class="text-muted fw-normal">- <?php echo e($produk->nama_produk); ?></small>
        </h2>
        <div class="btn-group">
            <a href="<?php echo e(route('master-data.biaya-bahan.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="<?php echo e(route('master-data.biaya-bahan.edit', $produk->id)); ?>" class="btn btn-warning">
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

    <!-- Product Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-box me-2"></i>Informasi Produk
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Produk:</strong></td>
                            <td><?php echo e($produk->nama_produk); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Deskripsi:</strong></td>
                            <td><?php echo e($produk->deskripsi ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Stok:</strong></td>
                            <td>
                                <span class="badge <?php echo e($produk->stok <= 0 ? 'bg-danger' : 'bg-success'); ?>">
                                    <?php echo e(number_format($produk->stok, 2, ',', '.')); ?> <?php echo e($produk->satuan ? $produk->satuan->nama : 'unit'); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Total Biaya Bahan:</strong></td>
                            <td>
                                <span class="badge bg-info">
                                    Rp <?php echo e(number_format($totalBiayaBahan, 0, ',', '.')); ?>

                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->foto): ?>
                        <div class="text-center">
                            <img src="<?php echo e(Storage::url($produk->foto)); ?>" 
                                 alt="<?php echo e($produk->nama_produk); ?>" 
                                 class="img-fluid rounded shadow"
                                 style="max-height: 150px;">
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials Used in Product -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Bahan yang Digunakan dalam Produk
            </h6>
        </div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($detailBahan) > 0): ?>
                <!-- Bahan Baku Section -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($detailBahanBaku) > 0): ?>
                    <div class="mb-4">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-cube me-2"></i>Bahan Baku (<?php echo e(count($detailBahanBaku)); ?> item)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nama Bahan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detailBahanBaku; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($index + 1); ?></td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold"><?php echo e($bahan['nama_bahan']); ?></div>
                                                    <small class="text-muted"><?php echo e($bahan['satuan_base']); ?></small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php echo e(number_format($bahan['qty'], 2, ',', '.')); ?> <?php echo e($bahan['satuan']); ?>

                                            </td>
                                            <td class="text-center">
                                                <?php echo e($bahan['satuan_base']); ?>

                                            </td>
                                            <td class="text-end">
                                                Rp <?php echo e(number_format($bahan['harga_satuan'], 0, ',', '.')); ?>

                                            </td>
                                            <td class="text-end">
                                                <strong>Rp <?php echo e(number_format($bahan['subtotal'], 0, ',', '.')); ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Bahan Baku:</th>
                                        <th class="text-end">
                                            <strong>Rp <?php echo e(number_format($totalBiayaBahanBaku, 0, ',', '.')); ?></strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <!-- Bahan Pendukung Section -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($detailBahanPendukung) > 0): ?>
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-flask me-2"></i>Bahan Pendukung (<?php echo e(count($detailBahanPendukung)); ?> item)
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Nama Bahan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $detailBahanPendukung; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($index + 1); ?></td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold"><?php echo e($bahan['nama_bahan']); ?></div>
                                                    <small class="text-muted"><?php echo e($bahan['satuan_base']); ?></small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php echo e(number_format($bahan['qty'], 2, ',', '.')); ?> <?php echo e($bahan['satuan']); ?>

                                            </td>
                                            <td class="text-center">
                                                <?php echo e($bahan['satuan_base']); ?>

                                            </td>
                                            <td class="text-end">
                                                Rp <?php echo e(number_format($bahan['harga_satuan'], 0, ',', '.')); ?>

                                            </td>
                                            <td class="text-end">
                                                <strong>Rp <?php echo e(number_format($bahan['subtotal'], 0, ',', '.')); ?></strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Total Bahan Pendukung:</th>
                                        <th class="text-end">
                                            <strong>Rp <?php echo e(number_format($totalBiayaBahanPendukung, 0, ',', '.')); ?></strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <!-- Summary -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-light">
                            <h6 class="alert-heading">Ringkasan Biaya Bahan untuk Produk</h6>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>Total Bahan Baku:</strong><br>
                                        <span class="text-info fs-5">Rp <?php echo e(number_format($totalBiayaBahanBaku, 0, ',', '.')); ?></span>
                                        <br><small class="text-muted"><?php echo e(count($detailBahanBaku)); ?> item</small>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>Total Bahan Pendukung:</strong><br>
                                        <span class="text-warning fs-5">Rp <?php echo e(number_format($totalBiayaBahanPendukung, 0, ',', '.')); ?></span>
                                        <br><small class="text-muted"><?php echo e(count($detailBahanPendukung)); ?> item</small>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>Total Biaya Bahan:</strong><br>
                                        <span class="text-success fs-5">Rp <?php echo e(number_format($totalBiayaBahan, 0, ',', '.')); ?></span>
                                        <br><small class="text-muted"><?php echo e(count($detailBahan)); ?> item total</small>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Perhitungan biaya bahan untuk produk ini
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="text-warning">Belum Ada Bahan</h5>
                    <p class="text-muted">Produk ini belum memiliki perhitungan biaya bahan</p>
                    <a href="<?php echo e(route('master-data.biaya-bahan.edit', $produk->id)); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Biaya Bahan
                    </a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
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
    
    .fs-5 {
        font-size: 1.25rem;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/biaya-bahan/show.blade.php ENDPATH**/ ?>