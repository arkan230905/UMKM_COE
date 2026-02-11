<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-sitemap me-2"></i>Bill of Materials (BOM)
        </h2>
        <a href="<?php echo e(route('master-data.bom.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Buat BOM Baru
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Produk
            </h6>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('master-data.bom.index')); ?>" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="<?php echo e(request('nama_produk')); ?>" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status BOM</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua</option>
                            <option value="ada" <?php echo e(request('status') == 'ada' ? 'selected' : ''); ?>>Sudah Ada BOM</option>
                            <option value="belum" <?php echo e(request('status') == 'belum' ? 'selected' : ''); ?>>Belum Ada BOM</option>
                            <option value="lengkap" <?php echo e(request('status') == 'lengkap' ? 'selected' : ''); ?>>BOM Lengkap</option>
                            <option value="tidak_lengkap" <?php echo e(request('status') == 'tidak_lengkap' ? 'selected' : ''); ?>>BOM Tidak Lengkap</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- BOM Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar BOM Produk
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Produk</th>
                            <th>Biaya Bahan</th>
                            <th>Biaya BTKL</th>
                            <th>Biaya BOP</th>
                            <th>Total Biaya BOM</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $missingColumns = [];
                                if (($produk->total_biaya_bahan ?? 0) == 0) $missingColumns[] = 'Biaya Bahan';
                                if (($produk->total_btkl ?? 0) == 0) $missingColumns[] = 'Biaya BTKL';
                                if (($produk->total_bop ?? 0) == 0) $missingColumns[] = 'Biaya BOP';
                                $hasBom = $produk->bomJobCosting || $produk->boms->isNotEmpty();
                                $isIncomplete = !empty($missingColumns);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-box text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($produk->nama_produk); ?></div>
                                            <small class="text-muted">ID: <?php echo e($produk->id); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="<?php if(($produk->total_biaya_bahan ?? 0) == 0): ?> text-warning fw-bold <?php endif; ?>">
                                    <div class="fw-semibold <?php if(($produk->total_biaya_bahan ?? 0) == 0): ?> text-warning <?php endif; ?>">
                                        Rp <?php echo e(number_format($produk->total_biaya_bahan ?? 0, 0, ',', '.')); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($produk->total_biaya_bahan ?? 0) == 0): ?>
                                            <i class="fas fa-exclamation-triangle ms-1" title="Biaya bahan kosong"></i>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Otomatis masuk sesuai dengan data halaman biaya bahan
                                    </small>
                                </td>
                                <td class="<?php if(($produk->total_btkl ?? 0) == 0): ?> text-warning fw-bold <?php endif; ?>">
                                    <div class="fw-semibold <?php if(($produk->total_btkl ?? 0) == 0): ?> text-warning <?php endif; ?>">
                                        Rp <?php echo e(number_format($produk->total_btkl ?? 0, 0, ',', '.')); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($produk->total_btkl ?? 0) == 0): ?>
                                            <i class="fas fa-exclamation-triangle ms-1" title="Biaya BTKL kosong"></i>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Otomatis masuk sesuai dengan data halaman btkl
                                    </small>
                                </td>
                                <td class="<?php if(($produk->total_bop ?? 0) == 0): ?> text-warning fw-bold <?php endif; ?>">
                                    <div class="fw-semibold <?php if(($produk->total_bop ?? 0) == 0): ?> text-warning <?php endif; ?>">
                                        Rp <?php echo e(number_format($produk->total_bop ?? 0, 0, ',', '.')); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($produk->total_bop ?? 0) == 0): ?>
                                            <i class="fas fa-exclamation-triangle ms-1" title="Biaya BOP kosong"></i>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Otomatis masuk sesuai dengan data halaman bop
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">
                                        Rp <?php echo e(number_format($produk->total_bom_cost ?? 0, 0, ',', '.')); ?>

                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-calculator"></i> Nominal Biaya bahan + BTKL + BOP, sistem otomatis menambahkan sendiri
                                    </small>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBom && !$isIncomplete): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Produk Sudah Memiliki BOM
                                        </span>
                                    <?php elseif($hasBom && $isIncomplete): ?>
                                        <span class="badge bg-warning" title="BOM belum lengkap: <?php echo e(implode(', ', $missingColumns)); ?>">
                                            <i class="fas fa-exclamation-triangle me-1"></i>BOM Belum Lengkap
                                        </span>
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-info-circle"></i> Kolom kosong: <?php echo e(implode(', ', $missingColumns)); ?>

                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Belum Ada BOM
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('master-data.bom.show', $produk->id)); ?>" class="btn btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data BOM</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan <?php echo e($produks->firstItem()); ?> - <?php echo e($produks->lastItem()); ?> dari <?php echo e($produks->total()); ?> data
                </div>
                <?php echo e($produks->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .text-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border-radius: 4px;
        padding: 2px 4px;
    }
    
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table th {
        border-top: 2px solid #dee2e6 !important;
        border-bottom: 2px solid #dee2e6 !important;
        font-weight: 600;
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    .table td {
        border-bottom: 1px solid #dee2e6;
        text-align: center !important;
        vertical-align: middle !important;
    }
    
    .table td:first-child {
        text-align: left !important;
    }
    
    .table td:nth-child(5),
    .table td:nth-child(6),
    .table td:nth-child(7) {
        text-align: center !important;
    }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/index.blade.php ENDPATH**/ ?>