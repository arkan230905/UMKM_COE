<?php $__env->startSection('content'); ?>
<style>
    .card-footer nav svg { width: 14px; height: 14px; }
    .card-footer nav span, .card-footer nav a { font-size: 0.875rem; }
    .card-footer .pagination .page-link { padding: .25rem .5rem; }
</style>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-briefcase me-2"></i>Klasifikasi Tenaga Kerja</h2>
        <a href="<?php echo e(route('master-data.jabatan.create')); ?>" class="btn btn-success btn-lg">
            <i class="bi bi-plus-circle me-2"></i>Tambah Klasifikasi
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" 
                           name="search" 
                           value="<?php echo e(request('search')); ?>" 
                           class="form-control" 
                           placeholder="Cari nama Klasifikasi Tenaga Kerja..."
                           style="color: #000; background-color: #f8f9fa;">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select" onchange="this.form.submit()" style="color: #000; background-color: #f8f9fa;">
                        <option value="">Semua Kategori</option>
                        <option value="btkl" <?php echo e(request('kategori') == 'btkl' ? 'selected' : ''); ?>>BTKL</option>
                        <option value="btktl" <?php echo e(request('kategori') == 'btktl' ? 'selected' : ''); ?>>BTKTL</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('search') || request('kategori')): ?>
                        <a href="<?php echo e(route('master-data.jabatan.index')); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Jabatan</th>
                            <th>Kategori</th>
                            <th>Tunjangan</th>
                            <th>Asuransi</th>
                            <th>Gaji</th>
                            <th>Tarif/Jam</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $jabatans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(($jabatans->currentPage()-1)*$jabatans->perPage() + $loop->iteration); ?></td>
                            <td><?php echo e($row->nama); ?></td>
                            <td><span class="badge bg-<?php echo e($row->kategori==='btkl'?'primary':'success'); ?>"><?php echo e(strtoupper($row->kategori)); ?></span></td>
                            <td>Rp <?php echo e(number_format($row->tunjangan,0,',','.')); ?></td>
                            <td>Rp <?php echo e(number_format($row->asuransi,0,',','.')); ?></td>
                            <td>Rp <?php echo e(number_format($row->gaji,0,',','.')); ?></td>
                            <td>Rp <?php echo e(number_format($row->tarif,0,',','.')); ?></td>
                            <td class="text-center">
                                <a href="<?php echo e(route('master-data.jabatan.edit',$row->id)); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="<?php echo e(route('master-data.jabatan.destroy',$row->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus jabatan ini?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jabatans->hasPages()): ?>
        <div class="card-footer bg-white">
            <?php echo e($jabatans->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/jabatan/index.blade.php ENDPATH**/ ?>