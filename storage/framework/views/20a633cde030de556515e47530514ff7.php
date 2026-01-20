

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-chart-pie me-2"></i>BOP per Proses
        </h2>
        <div>
            <a href="<?php echo e(route('master-data.bop-proses.sync-kapasitas')); ?>" class="btn btn-warning me-2" 
               onclick="return confirm('Sync kapasitas dari BTKL untuk semua BOP?')">
                <i class="fas fa-sync me-2"></i>Sync Kapasitas
            </a>
            <a href="<?php echo e(route('master-data.bop-proses.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah BOP Proses
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <?php echo e(session('warning')); ?>

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
                    <i class="fas fa-list me-2"></i>Daftar BOP (Biaya Overhead Pabrik) per Proses
                </h5>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($prosesProduksis->total() > 0): ?>
                    <small class="text-muted">Total: <?php echo e($prosesProduksis->total()); ?> proses produksi</small>
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
                            <th>Kode Proses</th>
                            <th>Nama Proses</th>
                            <th class="text-end">Total BOP/Jam</th>
                            <th class="text-center">Kapasitas/Jam</th>
                            <th class="text-end">
                                BOP/Unit
                                <i class="fas fa-info-circle text-muted ms-1" 
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Dihitung dari: Total BOP per Jam ÷ Kapasitas per Jam"></i>
                            </th>
                            <th class="text-center">Status</th>
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
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-chart-pie text-warning"></i>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->bopProses): ?>
                                        <div class="fw-semibold text-warning"><?php echo e($proses->bopProses->total_bop_per_jam_formatted); ?></div>
                                        <small class="text-muted">per jam mesin</small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php echo e($proses->kapasitas_per_jam ?? 0); ?> unit/jam</span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->bopProses && $proses->bopProses->kapasitas_per_jam != $proses->kapasitas_per_jam): ?>
                                        <br><small class="text-danger">⚠️ Tidak sync</small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->bopProses && $proses->bopProses->bop_per_unit > 0): ?>
                                        <div class="fw-semibold text-success"><?php echo e($proses->bopProses->bop_per_unit_formatted); ?></div>
                                        <small class="text-muted">per unit</small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->kapasitas_per_jam <= 0): ?>
                                        <span class="badge bg-danger">Kapasitas Kosong</span>
                                    <?php elseif($proses->bopProses): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->bopProses->isConfigured()): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Belum Dikonfigurasi</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum Ada BOP</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->bopProses): ?>
                                            <a href="<?php echo e(route('master-data.bop-proses.show', $proses->bopProses->id)); ?>" class="btn btn-outline-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('master-data.bop-proses.edit', $proses->bopProses->id)); ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('master-data.bop-proses.destroy', $proses->bopProses->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus BOP proses ini?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->kapasitas_per_jam > 0): ?>
                                                <a href="<?php echo e(route('master-data.bop-proses.create', ['proses_id' => $proses->id])); ?>" class="btn btn-outline-success" title="Buat BOP">
                                                    <i class="fas fa-plus"></i> Buat BOP
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Perlu kapasitas BTKL</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data proses produksi</p>
                                    <a href="<?php echo e(route('master-data.btkl.create')); ?>" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Buat BTKL Terlebih Dahulu
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
                                    $withBop = $prosesProduksis->filter(function($p) { return $p->bopProses; })->count();
                                ?>
                                <div class="fw-bold text-success"><?php echo e($withBop); ?></div>
                                <small class="text-muted">Sudah Ada BOP</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <?php
                                    $withoutCapacity = $prosesProduksis->filter(function($p) { return $p->kapasitas_per_jam <= 0; })->count();
                                ?>
                                <div class="fw-bold text-danger"><?php echo e($withoutCapacity); ?></div>
                                <small class="text-muted">Tanpa Kapasitas</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <?php
                                $avgBopPerUnit = $prosesProduksis->filter(function($p) { 
                                    return $p->bopProses && $p->bopProses->bop_per_unit > 0; 
                                })->avg(function($p) { 
                                    return $p->bopProses->bop_per_unit; 
                                });
                            ?>
                            <div class="fw-bold text-warning">Rp <?php echo e(number_format($avgBopPerUnit ?? 0, 2, ',', '.')); ?></div>
                            <small class="text-muted">Rata-rata BOP/Unit</small>
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
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop-proses/index.blade.php ENDPATH**/ ?>