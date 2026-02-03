<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-user-clock me-2"></i>Daftar Proses Produksi (BTKL)
        </h2>
        <a href="<?php echo e(route('master-data.btkl.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus"></i> Tambah Proses
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
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-wide">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 8%">Kode</th>
                            <th style="width: 15%">Nama Proses</th>
                            <th style="width: 15%">Jabatan BTKL</th>
                            <th style="width: 10%">Jumlah Pegawai</th>
                            <th style="width: 12%">Tarif BTKL</th>
                            <th style="width: 8%">Satuan</th>
                            <th style="width: 12%">Kapasitas/Jam</th>
                            <th style="width: 12%">Biaya Per Produk</th>
                            <th style="width: 15%">Deskripsi</th>
                            <th style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $btkls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $btkl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?php echo e($btkl->kode_proses); ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-gear-fill me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold"><?php echo e($btkl->nama_btkl ?? '-'); ?></div>
                                        <small class="text-muted">Nama proses produksi</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-workspace me-2 text-info"></i>
                                    <div>
                                        <div class="fw-bold"><?php echo e($btkl->jabatan->nama ?? '-'); ?></div>
                                        <small class="text-muted"><?php echo e($btkl->jabatan->kategori ?? ''); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people-fill me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold text-primary"><?php echo e($btkl->jabatan->pegawais->count() ?? 0); ?> orang</div>
                                        <small class="text-muted">Jabatan: <?php echo e($btkl->jabatan->nama ?? '-'); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-success"><?php echo e($btkl->tarif_per_jam_formatted); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo e($btkl->satuan); ?></span>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo e(number_format($btkl->kapasitas_per_jam)); ?> pcs</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-stack me-2 text-warning"></i>
                                    <div>
                                        <div class="fw-bold text-warning"><?php echo e($btkl->biaya_per_produk_formatted); ?></div>
                                        <small class="text-muted">Rp <?php echo e(number_format($btkl->tarif_per_jam / $btkl->kapasitas_per_jam, 2, ",", ".")); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small><?php echo e($btkl->deskripsi_proses ?? '-'); ?></small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?php echo e(route('master-data.btkl.edit', $btkl->id)); ?>" 
                                       class="btn btn-sm btn-warning text-white rounded-pill px-3"
                                       data-bs-toggle="tooltip" 
                                       title="Edit BTKL">
                                        <i class="bi bi-pencil-square me-1"></i>
                                        <span class="d-none d-md-inline">Edit</span>
                                    </a>
                                    <button type="button" 
                                           class="btn btn-sm btn-danger text-white rounded-pill px-3"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#deleteModal<?php echo e($btkl->id); ?>"
                                           data-bs-toggle="tooltip" 
                                           title="Hapus BTKL">
                                        <i class="bi bi-trash3 me-1"></i>
                                        <span class="d-none d-md-inline">Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                    <p>Belum ada data proses produksi</p>
                                    <a href="<?php echo e(route('master-data.btkl.create')); ?>" class="btn btn-primary">
                                        <i class="bi bi-plus"></i> Tambah Proses Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modals -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $btkls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $btkl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="modal fade" id="deleteModal<?php echo e($btkl->id); ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo e($btkl->id); ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel<?php echo e($btkl->id); ?>">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-trash3 text-danger" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-danger fw-bold">Apakah Anda yakin?</h6>
                    <p class="text-muted mb-0">Data BTKL untuk proses <strong>"<?php echo e($btkl->jabatan->nama ?? 'Tidak Diketahui'); ?>"</strong> akan dihapus secara permanen.</p>
                </div>
                
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-warning me-2"></i>
                        <div>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-1 small">
                                <li>Kode Proses: <code><?php echo e($btkl->kode_proses); ?></code></li>
                                <li>Tarif BTKL: <?php echo e($btkl->tarif_per_jam_formatted); ?></li>
                                <li>Kapasitas: <?php echo e(number_format($btkl->kapasitas_per_jam)); ?> pcs/jam</li>
                                <li>Biaya/Produk: <?php echo e($btkl->biaya_per_produk_formatted); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </button>
                <form action="<?php echo e(route('master-data.btkl.destroy', $btkl->id)); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash3 me-1"></i>Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/btkl/index.blade.php ENDPATH**/ ?>