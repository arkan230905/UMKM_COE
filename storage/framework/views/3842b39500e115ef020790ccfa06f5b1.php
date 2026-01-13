

<?php $__env->startPush('styles'); ?>
<style>
/* Bahan Pendukung page specific - BLACK text for kode and nama bahan */
.table tbody td:nth-child(1) {
    color: #333 !important; /* Kolom Kode */
    text-align: center !important;
    padding: 8px !important;
}
.table tbody td:nth-child(2) {
    color: #333 !important; /* Kolom Nama Bahan */
}
.table tbody td:nth-child(2) strong {
    color: #333 !important; /* Nama bahan yang bold */
}

/* Special styling for code column (1st column) - Purple rounded pill like bahan baku */
.table tbody td:nth-child(1) code {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    font-weight: bold !important;
    padding: 8px 20px !important;
    border-radius: 25px !important;
    display: inline-block !important;
    min-width: 120px !important;
    text-align: center !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
    font-size: 14px !important;
    letter-spacing: 0.5px !important;
    border: none !important;
}

.table tbody tr:hover td:nth-child(1) code {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%) !important;
    transform: scale(1.05) !important;
    transition: all 0.3s ease !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Bahan Pendukung</h1>
        <a href="<?php echo e(route('master-data.bahan-pendukung.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Bahan Pendukung
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

    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">Daftar Bahan Pendukung</h5>
            <small class="text-muted">Bahan tidak langsung seperti gas, bumbu, minyak, listrik, dll</small>
        </div>
        <div class="card-body">
            <!-- Filter -->
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau kode..." value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($kat->id); ?>" <?php echo e(request('kategori') == $kat->id ? 'selected' : ''); ?>><?php echo e($kat->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="<?php echo e(route('master-data.bahan-pendukung.index')); ?>" class="btn btn-secondary w-100">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
                <div class="col-md-1">
                    <a href="<?php echo e(route('master-data.kategori-bahan-pendukung.index')); ?>" class="btn btn-outline-info w-100" title="Kelola Kategori">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover custom-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="ps-3 py-3" width="10%">Kode</th>
                            <th>Nama Bahan</th>
                            <th width="12%">Kategori</th>
                            <th class="text-end">Harga/Satuan</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bahanPendukungs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><code><?php echo e($bahan->kode_bahan); ?></code></td>
                                <td>
                                    <strong><?php echo e($bahan->nama_bahan); ?></strong>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahan->deskripsi): ?>
                                        <br><small class="text-muted"><?php echo e(Str::limit($bahan->deskripsi, 50)); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo e($bahan->kategoriBahanPendukung->nama ?? ucfirst($bahan->kategori)); ?>

                                    </span>
                                </td>
                                <td class="text-end">
                                    Rp <?php echo e(number_format($bahan->harga_satuan, 0, ',', '.')); ?><br>
                                    <small class="text-muted">per <?php echo e($bahan->satuanRelation ? ($bahan->satuanRelation->kode . ' - ' . $bahan->satuanRelation->nama) : '-'); ?></small>
                                </td>
                                <td class="text-center">
                                    <?php echo e(number_format($bahan->stok, 2)); ?> <?php echo e($bahan->satuanRelation ? ($bahan->satuanRelation->kode . ' - ' . $bahan->satuanRelation->nama) : '-'); ?><br>
                                    <small class="text-muted">Min: <?php echo e(number_format($bahan->stok_minimum, 2)); ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahan->status_stok == 'Habis'): ?>
                                        <span class="badge bg-danger">Habis</span>
                                    <?php elseif($bahan->status_stok == 'Menipis'): ?>
                                        <span class="badge bg-warning">Menipis</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aman</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <br>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bahan->is_active): ?>
                                        <small class="text-success">Aktif</small>
                                    <?php else: ?>
                                        <small class="text-muted">Nonaktif</small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('master-data.bahan-pendukung.edit', $bahan)); ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('master-data.bahan-pendukung.destroy', $bahan)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus bahan ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data bahan pendukung</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($bahanPendukungs->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bahan-pendukung/index.blade.php ENDPATH**/ ?>