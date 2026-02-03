<?php $__env->startSection('title', 'Tentang Perusahaan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-building me-2"></i>Tentang Perusahaan
        </h2>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'owner'): ?>
            <a href="/tentang-perusahaan/edit" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Data
            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Info untuk admin bahwa ini adalah view-only -->
    <?php if(auth()->user()->role !== 'owner'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Informasi:</strong> Halaman ini bersifat read-only. Untuk mengubah data perusahaan, silakan hubungi owner.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('info')): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?php echo e(session('info')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-building me-2"></i>Informasi Perusahaan
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%" class="bg-light">Nama Perusahaan</th>
                                    <td><?php echo e($dataPerusahaan->nama); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Alamat</th>
                                    <td><?php echo e($dataPerusahaan->alamat); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Email</th>
                                    <td><?php echo e($dataPerusahaan->email); ?></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Telepon</th>
                                    <td><?php echo e($dataPerusahaan->telepon); ?></td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dataPerusahaan->kode): ?>
                                    <tr>
                                        <th class="bg-light">Kode Perusahaan</th>
                                        <td>
                                            <span class="badge bg-primary"><?php echo e($dataPerusahaan->kode); ?></span>
                                        </td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Kode Perusahaan:</strong> Digunakan untuk login pegawai dan kasir
                                    </p>
                                    <p class="mb-2">
                                        <strong>Akses Edit:</strong> Hanya user dengan role Owner yang dapat mengubah data perusahaan
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Role yang Terhubung:</strong> Admin, Pegawai, Kasir
                                    </p>
                                    <p class="mb-2">
                                        <strong>Update Otomatis:</strong> Perubahan data akan langsung terupdate di seluruh sistem
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/tentang-perusahaan/index.blade.php ENDPATH**/ ?>