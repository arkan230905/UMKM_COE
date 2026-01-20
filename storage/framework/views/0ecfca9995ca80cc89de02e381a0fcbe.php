<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-sitemap me-2"></i>Bill of Materials (BOM)
        </h2>
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
                    <div class="col-md-6">
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
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-block">&nbsp;</label>
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-outline-secondary">
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
                            <th style="width: 30%;">Produk</th>
                            <th style="width: 15%;" class="text-center">Jumlah Bahan</th>
                            <th style="width: 17%;" class="text-end">Total Biaya BOM</th>
                            <th style="width: 15%;" class="text-center">Status</th>
                            <th style="width: 20%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                // Cek apakah produk sudah punya BOM
                                $bom = $produk->boms->first();
                                $bomJobCosting = $produk->bomJobCosting; // Sudah di-load di controller
                                
                                // Hitung jumlah bahan dan total biaya HANYA jika BOM benar-benar ada
                                $jumlahBahanBaku = 0;
                                $jumlahBahanPendukung = 0;
                                $totalBiaya = 0;
                                
                                // Tentukan apakah benar-benar ada BOM aktif
                                $hasBOM = ($bom !== null);
                                
                                if ($bom) {
                                    // Jika ada BOM aktif, hitung data dari BOM
                                    $jumlahBahanBaku = \App\Models\BomDetail::where('bom_id', $bom->id)->count();
                                    $totalBiaya = $bom->total_hpp ?? $bom->total_biaya ?? 0;
                                }
                                
                                // BomJobCosting hanya untuk referensi, tidak mempengaruhi status BOM
                                // Ini data historis atau data untuk referensi saja
                                
                                $jumlahTotal = $jumlahBahanBaku + $jumlahBahanPendukung;
                                
                                // Cek biaya bahan
                                $biayaBahan = $produk->biaya_bahan ?? 0;
                                $hasBiayaBahan = $biayaBahan > 0;
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bom && $jumlahTotal > 0): ?>
                                        <div class="mb-1">
                                            <span class="badge bg-info"><?php echo e($jumlahTotal); ?> item</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            BBB: <?php echo e($jumlahBahanBaku); ?> | BP: <?php echo e($jumlahBahanPendukung); ?>

                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bom && $totalBiaya > 0): ?>
                                        <div class="fw-bold text-success fs-5">
                                            Rp <?php echo e(number_format($totalBiaya, 0, ',', '.')); ?>

                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Rp 0</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bom): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Sudah Ada BOM
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-minus-circle"></i> Belum Ada BOM
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <br>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasBiayaBahan): ?>
                                        <small class="badge bg-warning text-dark mt-1">
                                            <i class="fas fa-exclamation-triangle"></i> Belum ada biaya bahan
                                        </small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bomJobCosting && !$bom): ?>
                                        <small class="badge bg-info text-white mt-1">
                                            <i class="fas fa-calculator"></i> Ada Job Costing
                                        </small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bom): ?>
                                        
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo e(route('master-data.bom.show', $bom->id)); ?>" 
                                               class="btn btn-outline-primary" 
                                               data-bs-toggle="tooltip" 
                                               title="Lihat Detail BOM">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a href="<?php echo e(route('master-data.bom.edit', $bom->id)); ?>" 
                                               class="btn btn-outline-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit BOM">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="<?php echo e(route('master-data.bom.destroy', $bom->id)); ?>" 
                                                  method="POST" 
                                                  class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus BOM untuk <?php echo e($produk->nama_produk); ?>?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Hapus BOM">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasBiayaBahan): ?>
                                            <a href="<?php echo e(route('master-data.bom.create', ['produk_id' => $produk->id])); ?>" 
                                               class="btn btn-success btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Tambah BOM">
                                                <i class="fas fa-plus"></i> Tambah BOM
                                            </a>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bomJobCosting): ?>
                                                <br><small class="text-info mt-1">
                                                    <i class="fas fa-info-circle"></i> Ada data Job Costing
                                                </small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-warning btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#biayaBahanModal<?php echo e($produk->id); ?>"
                                                    title="Isi biaya bahan dulu">
                                                <i class="fas fa-exclamation-triangle"></i> Isi Biaya Bahan Dulu
                                            </button>
                                            
                                            <!-- Modal Notifikasi -->
                                            <div class="modal fade" id="biayaBahanModal<?php echo e($produk->id); ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-warning">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-exclamation-triangle"></i> Biaya Bahan Belum Ada
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Produk <strong><?php echo e($produk->nama_produk); ?></strong> belum memiliki data biaya bahan.</p>
                                                            <p>Silakan isi biaya bahan terlebih dahulu sebelum membuat BOM.</p>
                                                            <div class="alert alert-info">
                                                                <i class="fas fa-info-circle"></i> 
                                                                <strong>Kenapa harus isi biaya bahan dulu?</strong><br>
                                                                BOM membutuhkan data biaya bahan baku dan bahan pendukung untuk menghitung total biaya produksi.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <a href="<?php echo e(route('master-data.biaya-bahan.create', $produk->id)); ?>" class="btn btn-primary">
                                                                <i class="fas fa-calculator"></i> Isi Biaya Bahan
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-sitemap fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-0">Belum ada data produk</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produks->hasPages()): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan <?php echo e($produks->firstItem()); ?> sampai <?php echo e($produks->lastItem()); ?> dari <?php echo e($produks->total()); ?> data
                </div>
                <?php echo e($produks->links()); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transition: background-color 0.2s ease;
    }
    
    .table img {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/index.blade.php ENDPATH**/ ?>