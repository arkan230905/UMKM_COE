<?php $__env->startSection('title', 'Kualifikasi Tenaga Kerja'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-briefcase me-2"></i>Kualifikasi Tenaga Kerja
        </h2>
        <a href="<?php echo e(route('master-data.kualifikasi-tenaga-kerja.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Kualifikasi
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-1">
                <i class="fas fa-list me-2"></i>Daftar Kualifikasi Tenaga Kerja
            </h5>
            
            <!-- Modern Filter Section -->
            <form method="GET" class="d-flex align-items-center gap-2" style="margin-left: 30px;">
                <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white; min-width: 320px;">
                    <input type="text" 
                           name="search" 
                           value="<?php echo e(request('search')); ?>" 
                           class="form-control border-0" 
                           placeholder="Cari nama"
                           style="padding: 8px 15px; background: white; border-radius: 20px 0 0 20px; outline: none; box-shadow: none; font-size: 14px;">
                    
                    <select name="kategori" class="form-select border-0" style="padding: 8px 12px; background: white; border-radius: 0 20px 20px 0; outline: none; box-shadow: none; border-left: 1px solid #e0e0e0; font-size: 14px;">
                        <option value="">Semua Kategori</option>
                        <option value="btkl" <?php echo e(request('kategori') == 'btkl' ? 'selected' : ''); ?>>BTKL</option>
                        <option value="btktl" <?php echo e(request('kategori') == 'btktl' ? 'selected' : ''); ?>>BTKTL</option>
                    </select>
                </div>
                
                <button type="submit" class="btn shadow-sm" style="border-radius: 20px; padding: 8px 20px; background: #8B7355; color: white; border: none; font-size: 14px;">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('search') || request('kategori')): ?>
                    <a href="<?php echo e(route('master-data.kualifikasi-tenaga-kerja.index')); ?>" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 8px 15px; font-size: 14px;">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nama Kualifikasi</th>
                            <th>Kategori</th>
                            <th>Tunjangan</th>
                            <th>Asuransi</th>
                            <th>Gaji Pokok</th>
                            <th>Tarif/Jam</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $jabatans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e(($jabatans->currentPage()-1)*$jabatans->perPage() + $loop->iteration); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-briefcase text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo e($row->nama); ?></div>
                                        <small class="text-muted">ID: <?php echo e($row->id); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($row->kategori==='btkl'?'primary':'success'); ?>">
                                    <?php echo e(strtoupper($row->kategori)); ?>

                                </span>
                            </td>
                            <td class="fw-semibold">Rp <?php echo e(number_format($row->tunjangan,0,',','.')); ?></td>
                            <td class="fw-semibold">Rp <?php echo e(number_format($row->asuransi,0,',','.')); ?></td>
                            <td class="fw-semibold">Rp <?php echo e(number_format($row->gaji,0,',','.')); ?></td>
                            <td class="fw-semibold">Rp <?php echo e(number_format($row->tarif,0,',','.')); ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('master-data.kualifikasi-tenaga-kerja.edit',$row->id)); ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('master-data.kualifikasi-tenaga-kerja.destroy',$row->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus kualifikasi ini?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data kualifikasi tenaga kerja</p>
                            </td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jabatans->hasPages()): ?>
        <div class="card-footer">
            <?php echo e($jabatans->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/jabatan/index.blade.php ENDPATH**/ ?>