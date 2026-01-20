<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs me-2"></i>BTKL
        </h2>
        <a href="<?php echo e(route('master-data.btkl.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah BTKL
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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar BTKL (Biaya Tenaga Kerja Langsung)
                </h5>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksis->total() > 0): ?>
                    <small class="text-muted">Total: <?php echo e($prosesProduksis->total()); ?> proses BTKL</small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksis->count() > 0): ?>
                    <span class="badge bg-success"><?php echo e($prosesProduksis->count()); ?> dari <?php echo e($prosesProduksis->total()); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Kode</th>
                            <th>Nama Proses</th>
                            <th class="text-end">Tarif BTKL/Jam</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Kapasitas/Jam</th>
                            <th class="text-end">
                                Biaya per Produk
                                <i class="fas fa-info-circle text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Dihitung dari: Tarif BTKL per Jam ÷ Kapasitas per Jam"></i>
                            </th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $prosesProduksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e(($prosesProduksis->currentPage() - 1) * $prosesProduksis->perPage() + $key + 1); ?></td>
                                <td><code><?php echo e($proses->kode_proses); ?></code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-cogs text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($proses->nama_proses); ?></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->deskripsi): ?>
                                                <small class="text-muted"><?php echo e(Str::limit($proses->deskripsi, 50)); ?></small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-semibold">Rp <?php echo e(number_format($proses->tarif_btkl, 0, ',', '.')); ?></div>
                                    <small class="text-muted">per <?php echo e($proses->satuan_btkl ?? 'jam'); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?php echo e($proses->satuan_btkl ?? 'jam'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php echo e($proses->kapasitas_per_jam ?? 0); ?> unit/jam</span>
                                </td>
                                <td class="text-end" 
                                    data-biaya-per-produk="<?php echo e(number_format($proses->biaya_per_produk, 2, ',', '.')); ?>"
                                    data-tarif="<?php echo e(number_format($proses->tarif_btkl, 0, ',', '.')); ?>"
                                    data-kapasitas="<?php echo e($proses->kapasitas_per_jam); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->biaya_per_produk > 0): ?>
                                        <div class="fw-semibold text-success"><?php echo e($proses->biaya_per_produk_formatted); ?></div>
                                        <small class="text-muted">per unit</small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('master-data.btkl.show', $proses)); ?>" class="btn btn-outline-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('master-data.btkl.edit', $proses)); ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('master-data.btkl.destroy', $proses)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus proses ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data BTKL</p>
                                    <a href="<?php echo e(route('master-data.btkl.create')); ?>" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Tambah BTKL Pertama
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksis->count() > 0): ?>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <div class="fw-bold text-primary"><?php echo e($prosesProduksis->total()); ?></div>
                                <small class="text-muted">Total Proses</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <?php
                                    $avgTarif = $prosesProduksis->avg('tarif_btkl');
                                ?>
                                <div class="fw-bold text-success">Rp <?php echo e(number_format($avgTarif, 0, ',', '.')); ?></div>
                                <small class="text-muted">Rata-rata Tarif/Jam</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <?php
                                    $avgKapasitas = $prosesProduksis->avg('kapasitas_per_jam');
                                ?>
                                <div class="fw-bold text-info"><?php echo e(number_format($avgKapasitas, 0, ',', '.')); ?></div>
                                <small class="text-muted">Rata-rata Kapasitas/Jam</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <?php
                                $validProses = $prosesProduksis->filter(function($p) { 
                                    return $p->kapasitas_per_jam > 0; 
                                });
                                $avgBiayaPerUnit = $validProses->count() > 0 ? 
                                    $validProses->avg(function($p) { 
                                        return $p->tarif_btkl / $p->kapasitas_per_jam; 
                                    }) : 0;
                            ?>
                            <div class="fw-bold text-warning">Rp <?php echo e(number_format($avgBiayaPerUnit, 2, ',', '.')); ?></div>
                            <small class="text-muted">Rata-rata Biaya/Unit</small>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksis->hasPages()): ?>
                    <div class="card-footer">
                        <?php echo e($prosesProduksis->links()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <div class="card-footer">
                    <div class="text-center text-muted py-2">
                        <small>Belum ada data untuk ditampilkan</small>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add hover effect to show calculation details
    const biayaPerProdukCells = document.querySelectorAll('td[data-biaya-per-produk]');
    biayaPerProdukCells.forEach(function(cell) {
        const tarif = cell.dataset.tarif;
        const kapasitas = cell.dataset.kapasitas;
        const biaya = cell.dataset.biayaPerProduk;
        
        cell.setAttribute('title', `Perhitungan: Rp ${tarif} ÷ ${kapasitas} unit = Rp ${biaya}`);
        
        // Initialize tooltip for calculation
        new bootstrap.Tooltip(cell);
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/proses-produksi/index.blade.php ENDPATH**/ ?>