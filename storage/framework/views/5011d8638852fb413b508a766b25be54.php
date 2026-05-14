<?php $__env->startSection('title', 'Daftar Pegawai'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-end align-items-center mb-4">
        <div>
            <a href="<?php echo e(route('master-data.pegawai.create')); ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>Tambah Pegawai
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-1">
                <i class="bi bi-people-fill me-2"></i>Daftar Pegawai
            </h5>
            
            <!-- Modern Filter Section -->
            <form method="GET" action="<?php echo e(route('master-data.pegawai.index')); ?>" class="d-flex align-items-center gap-2" style="margin-left: 30px;">
                <div class="d-flex shadow-sm" style="border-radius: 20px; overflow: hidden; background: white; min-width: 320px;">
                    <input type="text" 
                           name="search" 
                           value="<?php echo e(request('search')); ?>" 
                           class="form-control border-0" 
                           placeholder="Cari pegawai"
                           style="padding: 8px 15px; background: white; border-radius: 20px 0 0 20px; outline: none; box-shadow: none; font-size: 14px;">
                    
                    <select name="jenis" class="form-select border-0" style="padding: 8px 12px; background: white; border-radius: 0 20px 20px 0; outline: none; box-shadow: none; border-left: 1px solid #e0e0e0; font-size: 14px;">
                        <option value="">Semua Kategori</option>
                        <option value="btkl" <?php echo e(request('jenis') == 'btkl' ? 'selected' : ''); ?>>BTKL</option>
                        <option value="btktl" <?php echo e(request('jenis') == 'btktl' ? 'selected' : ''); ?>>BTKTL</option>
                    </select>
                </div>
                
                <button type="submit" class="btn shadow-sm" style="border-radius: 20px; padding: 8px 20px; background: #8B7355; color: white; border: none; font-size: 14px;">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('search') || request('jenis')): ?>
                    <a href="<?php echo e(route('master-data.pegawai.index')); ?>" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 8px 15px; font-size: 14px;">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th class="text-center">Kategori</th>
                            <th>Bank</th>
                            <th>No. Rekening</th>
                            <th>Nama Rekening</th>
                            <th>Alamat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pegawais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pegawai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php ($rowKey = $pegawai->getKey()); ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo e(($pegawais->currentPage() - 1) * $pegawais->perPage() + $loop->iteration); ?></td>
                            <td><?php echo e($pegawai->kode_pegawai); ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo e($pegawai->nama); ?></div>
                                <small class="text-muted"><?php echo e($pegawai->email); ?></small>
                            </td>
                            <td><?php echo e($pegawai->jabatan); ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo e($pegawai->jenis_pegawai == 'btkl' ? 'primary' : 'success'); ?>">
                                    <?php echo e(strtoupper($pegawai->jenis_pegawai)); ?>

                                </span>
                            </td>
                            <td><?php echo e(strtoupper($pegawai->bank ?? '-')); ?></td>
                            <td><?php echo e($pegawai->nomor_rekening ?? '-'); ?></td>
                            <td><?php echo e($pegawai->nama_rekening ?? '-'); ?></td>
                            <td><small><?php echo e($pegawai->alamat ?? '-'); ?></small></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('master-data.pegawai.edit', $pegawai)); ?>"
                                       class="btn btn-outline-primary"
                                       data-bs-toggle="tooltip"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('master-data.pegawai.destroy', $pegawai)); ?>" method="POST" class="d-inline delete-form" data-pegawai-nama="<?php echo e($pegawai->nama); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="button"
                                                class="btn btn-outline-danger delete-btn"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-people display-6 d-block mb-2"></i>
                                    Tidak ada data pegawai yang ditemukan.
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pegawais->total() > 0): ?>
                            Menampilkan <?php echo e(($pegawais->currentPage() - 1) * $pegawais->perPage() + 1); ?> - 
                            <?php echo e(min($pegawais->currentPage() * $pegawais->perPage(), $pegawais->total())); ?> 
                            dari <?php echo e($pegawais->total()); ?> data
                        <?php else: ?>
                            Tidak ada data yang ditemukan
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pegawais->hasPages()): ?>
                    <div>
                        <?php echo e($pegawais->withQueryString()->links()); ?>

                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php $__env->startPush('scripts'); ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Inisialisasi tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle delete button dengan SweetAlert2
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.delete-form');
                const pegawaiNama = form.getAttribute('data-pegawai-nama');
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    html: `Apakah Anda yakin ingin menghapus pegawai:<br><strong>${pegawaiNama}</strong><br><small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return new Promise((resolve) => {
                            form.submit();
                            resolve();
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
            });
        });
    });
    
    // Auto close alert setelah 5 detik
    setTimeout(function() {
        document.querySelectorAll('.alert.alert-success, .alert.alert-danger').forEach(function (alertEl) {
            try {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                bsAlert.close();
            } catch (e) {
                // ignore
            }
        });
    }, 5000);
</script>
<?php $__env->stopPush(); ?>

<style>
    .table-responsive { overflow-x: auto; }
    .avatar {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-top: none;
    }
    .table > :not(:first-child) {
        border-top: 1px solid #e9ecef;
    }
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.05);
    }
    .form-control, .form-select {
        border-radius: 0.375rem;
    }
    .btn {
        border-radius: 0.375rem;
    }
    
    /* Pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    .pagination .page-link svg {
        width: 14px;
        height: 14px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/pegawai/index.blade.php ENDPATH**/ ?>