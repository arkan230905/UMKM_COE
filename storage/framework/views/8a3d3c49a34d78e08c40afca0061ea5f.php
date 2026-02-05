

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Perhitungan Biaya Bahan
        </h2>
        <div class="btn-group">
            <form action="<?php echo e(route('master-data.biaya-bahan.recalculate')); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-warning" onclick="return confirm('Yakin ingin menghitung ulang semua biaya bahan?')">
                    <i class="fas fa-sync-alt"></i> Hitung Ulang Semua
                </button>
            </form>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo e(session('warning')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i><?php echo e(session('info')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Data
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['nama_produk', 'harga_min', 'harga_max'])): ?>
                    <small class="text-white-50">(Filter Aktif)</small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('master-data.biaya-bahan.index')); ?>" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="<?php echo e(request('nama_produk')); ?>" placeholder="Cari nama produk...">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_min" class="form-label">Harga BOM Min</label>
                        <input type="number" class="form-control" id="harga_min" name="harga_min" 
                               value="<?php echo e(request('harga_min')); ?>" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <label for="harga_max" class="form-label">Harga BOM Max</label>
                        <input type="number" class="form-control" id="harga_max" name="harga_max" 
                               value="<?php echo e(request('harga_max')); ?>" placeholder="999999999">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="<?php echo e(route('master-data.biaya-bahan.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 3%;" class="text-center">#</th>
                            <th style="width: 25%;">Produk</th>
                            <th style="width: 15%;" class="text-center">Bahan Baku</th>
                            <th style="width: 15%;" class="text-center">Bahan Pendukung</th>
                            <th style="width: 17%;" class="text-end">Total Biaya</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 15%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $biaya = $produkBiaya[$produk->id] ?? [];
                                $totalBiaya = $biaya['total_biaya'] ?? 0;
                                $totalBiayaBahanBaku = $biaya['total_biaya_bahan_baku'] ?? 0;
                                $totalBiayaBahanPendukung = $biaya['total_biaya_bahan_pendukung'] ?? 0;
                                $jumlahBahanBaku = count($biaya['detail_bahan_baku'] ?? []);
                                $jumlahBahanPendukung = count($biaya['detail_bahan_pendukung'] ?? []);
                            ?>
                            <tr>
                                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->foto): ?>
                                            <img src="<?php echo e(Storage::url($produk->foto)); ?>" 
                                                 alt="<?php echo e($produk->nama_produk); ?>" 
                                                 class="rounded me-2"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-box text-white"></i>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?php echo e($produk->nama_produk); ?></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->barcode): ?>
                                                <small class="text-muted"><?php echo e($produk->barcode); ?></small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jumlahBahanBaku > 0): ?>
                                        <div class="mb-1">
                                            <span class="badge bg-info"><?php echo e($jumlahBahanBaku); ?> item</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            Rp <?php echo e(number_format($totalBiayaBahanBaku, 0, ',', '.')); ?>

                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jumlahBahanPendukung > 0): ?>
                                        <div class="mb-1">
                                            <span class="badge bg-warning text-dark"><?php echo e($jumlahBahanPendukung); ?> item</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            Rp <?php echo e(number_format($totalBiayaBahanPendukung, 0, ',', '.')); ?>

                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalBiaya > 0): ?>
                                        <div class="fw-bold text-success fs-5">
                                            Rp <?php echo e(number_format($totalBiaya, 0, ',', '.')); ?>

                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->harga_jual): ?>
                                            <?php
                                                $margin = $produk->harga_jual > 0 ? (($produk->harga_jual - $totalBiaya) / $produk->harga_jual * 100) : 0;
                                            ?>
                                            <small class="text-muted">
                                                Margin: 
                                                <span class="badge <?php echo e($margin >= 20 ? 'bg-success' : ($margin >= 10 ? 'bg-warning text-dark' : 'bg-danger')); ?>">
                                                    <?php echo e(number_format($margin, 1)); ?>%
                                                </span>
                                            </small>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalBiaya > 0): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Lengkap
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-minus-circle"></i> Kosong
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalBiaya > 0): ?>
                                            <a href="<?php echo e(route('master-data.biaya-bahan.show', $produk->id)); ?>" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('master-data.biaya-bahan.edit', $produk->id)); ?>" 
                                               class="btn btn-outline-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('master-data.biaya-bahan.destroy', $produk->id)); ?>" 
                                                  method="POST" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus semua biaya bahan untuk <?php echo e($produk->nama_produk); ?>?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" 
                                                        class="btn btn-outline-danger" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('master-data.biaya-bahan.create', $produk->id)); ?>" 
                                               class="btn btn-success btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Tambah Biaya Bahan">
                                                <i class="fas fa-plus"></i> Tambah
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-calculator fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Belum ada data perhitungan biaya bahan</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produks->count() > 0): ?>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total Keseluruhan:</th>
                            <th class="text-center">
                                <div class="badge bg-info">
                                    <?php echo e(collect($produkBiaya)->sum(fn($item) => count($item['detail_bahan_baku'] ?? []))); ?> item
                                </div>
                                <div class="small text-muted mt-1">
                                    Rp <?php echo e(number_format(collect($produkBiaya)->sum('total_biaya_bahan_baku'), 0, ',', '.')); ?>

                                </div>
                            </th>
                            <th class="text-center">
                                <div class="badge bg-warning text-dark">
                                    <?php echo e(collect($produkBiaya)->sum(fn($item) => count($item['detail_bahan_pendukung'] ?? []))); ?> item
                                </div>
                                <div class="small text-muted mt-1">
                                    Rp <?php echo e(number_format(collect($produkBiaya)->sum('total_biaya_bahan_pendukung'), 0, ',', '.')); ?>

                                </div>
                            </th>
                            <th class="text-end">
                                <div class="fw-bold text-success fs-5">
                                    Rp <?php echo e(number_format(collect($produkBiaya)->sum('total_biaya'), 0, ',', '.')); ?>

                                </div>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan <?php echo e($produks->firstItem()); ?> sampai <?php echo e($produks->lastItem()); ?> dari <?php echo e($produks->total()); ?> data
                </div>
                <?php echo e($produks->links()); ?>

            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.875rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        font-weight: 500;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    /* Hover effect untuk row */
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transition: background-color 0.2s ease;
    }
    
    /* Style untuk gambar produk */
    .table img {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Style untuk total biaya */
    .fs-5 {
        font-size: 1.1rem !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/biaya-bahan/index.blade.php ENDPATH**/ ?>