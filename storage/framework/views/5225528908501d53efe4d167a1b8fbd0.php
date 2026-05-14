<?php $__env->startSection('title', 'Perhitungan Biaya Bahan Baku'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-calculator me-2"></i>Perhitungan Biaya Bahan Baku
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

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background-color: #a0826d; color: white;">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Data
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
                            <button type="submit" class="btn" style="background-color: #a0826d; color: white;">
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
                    <thead class="table-warning">
                        <tr>
                            <th style="width: 3%;" class="text-center">No</th>

                            <th style="width: 25%;">Produk</th>
                            <th style="width: 20%;" class="text-center">Bahan Baku</th>
                            <th style="width: 22%;" class="text-end">Total Biaya Bahan Baku</th>
<th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 20%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $produkBiaya; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $produk = $data['produk'] ?? null;
                                $biaya = $data;
                                $totalBiaya = $biaya['total_biaya'] ?? 0;
                                $totalBiayaBahanBaku = $biaya['total_biaya_bahan_baku'] ?? 0;
                                $totalBiayaBahanPendukung = $biaya['total_biaya_bahan_pendukung'] ?? 0;
                                
                                // HANYA HITUNG ITEM BAHAN BAKU YANG VALID (harga > 0)
                                $detailBahanBaku = $biaya['detail_bahan_baku'] ?? [];
                                $detailBahanPendukung = $biaya['detail_bahan_pendukung'] ?? [];
                                
                                $jumlahBahanBaku = collect($detailBahanBaku)->filter(function($item) {
                                    return ($item['subtotal'] ?? 0) > 0;
                                })->count();
                                
                                $jumlahBahanPendukung = collect($detailBahanPendukung)->filter(function($item) {
                                    return ($item['subtotal'] ?? 0) > 0;
                                })->count();
                            ?>
                            <tr>
                                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk && $produk->foto): ?>
                                            <img src="<?php echo e(storage_url($produk->foto)); ?>" 
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
                                            <div class="fw-bold"><?php echo e($produk ? $produk->nama_produk : 'Unknown'); ?></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk && $produk->barcode): ?>
                                                <small class="text-muted"><?php echo e($produk->barcode); ?></small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jumlahBahanBaku > 0): ?>
                                        <div class="mb-1">
                                            <span class="text-warning fw-semibold"><?php echo e($jumlahBahanBaku); ?> item</span>
                                        </div>
                                        <small class="text-muted d-block">
                                            Rp <?php echo e(number_format($totalBiayaBahanBaku, 0, ',', '.')); ?>

                                        </small>
                                    <?php else: ?>

                                        <span class="text-muted">0 item</span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold" style="color: #a0826d;">
                                        Rp <?php echo e(number_format($totalBiayaBahanBaku, 0, ',', '.')); ?>

                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalBiayaBahanPendukung > 0): ?>
                                        <small class="text-muted d-block">
                                            + Rp <?php echo e(number_format($totalBiayaBahanPendukung, 0, ',', '.')); ?>

                                        </small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jumlahBahanBaku > 0 || $jumlahBahanPendukung > 0): ?>
                                        <span class="badge bg-success">Valid</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Kosong</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jumlahBahanBaku > 0 || $jumlahBahanPendukung > 0): ?>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo e(route('master-data.biaya-bahan.detail', $produk->id)); ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Detail Biaya Bahan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('master-data.biaya-bahan.edit', $produk->id)); ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?php echo e(route('master-data.biaya-bahan.create', $produk->id)); ?>" 
                                           class="btn btn-sm" style="background-color: #a0826d; color: white;" title="Input Biaya Bahan">
                                            <i class="fas fa-plus"></i> Input
                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>

                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p>Belum ada data biaya bahan</p>
                                        <small>Silakan input biaya bahan untuk produk yang tersedia</small>
                                    </div>
</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>

</table>
            </div>
            
            <!-- Summary -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($produkBiaya) > 0): ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-box me-2" style="color: #a0826d;"></i>
                                            <div>
                                                <small class="text-muted">Total Keseluruhan:</small>
                                                <div class="fw-bold"><?php echo e(count($produkBiaya)); ?> item</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calculator text-success me-2"></i>
                                            <div>
                                                <small class="text-muted">Total Biaya Bahan Baku:</small>
                                                <div class="fw-bold">
                                                    Rp <?php echo e(number_format(array_sum(array_column($produkBiaya, 'total_biaya_bahan_baku')), 0, ',', '.')); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-chart-line text-info me-2"></i>
                                            <div>
                                                <small class="text-muted">Total Biaya Keseluruhan:</small>
                                                <div class="fw-bold">
                                                    Rp <?php echo e(number_format(array_sum(array_column($produkBiaya, 'total_biaya_bahan')), 0, ',', '.')); ?>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/biaya-bahan/index.blade.php ENDPATH**/ ?>