<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-people-fill me-2"></i>Daftar Pegawai
        </h2>
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <form method="GET" action="<?php echo e(route('master-data.pegawai.index')); ?>" class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Cari pegawai..." 
                               value="<?php echo e(request('search')); ?>"
                               style="color: #000; background-color: #f8f9fa;">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="<?php echo e(route('master-data.pegawai.index')); ?>">
                        <select name="jenis" class="form-select" onchange="this.form.submit()" style="color: #000; background-color: #f8f9fa;">
                            <option value="">Semua Kategori</option>
                            <option value="btkl" <?php echo e(request('jenis') == 'btkl' ? 'selected' : ''); ?>>BTKL</option>
                            <option value="btktl" <?php echo e(request('jenis') == 'btktl' ? 'selected' : ''); ?>>BTKTL</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-wide">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telp</th>
                            <th class="col-alamat">Alamat</th>
                            <th>Jenis Kelamin</th>
                            <th>Jabatan</th>
                            <th class="text-center">Kategori</th>
                            <th>Bank</th>
                            <th>No. Rekening</th>
                            <th>Nama Rekening</th>
                            <th class="text-end">Gaji Pokok</th>
                            <th class="text-end">Tarif/Jam</th>
                            <th class="text-end">Tunjangan</th>
                            <th class="text-end">Asuransi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pegawais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pegawai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center text-muted"><?php echo e(($pegawais->currentPage() - 1) * $pegawais->perPage() + $loop->iteration); ?></td>
                            <td><?php echo e($pegawai->kode_pegawai); ?></td>
                            <td><?php echo e($pegawai->nama); ?></td>
                            <td><?php echo e($pegawai->email); ?></td>
                            <td><?php echo e($pegawai->no_telp); ?></td>
                            <td class="col-alamat"><small class="text-muted"><?php echo e(Str::limit($pegawai->alamat, 40)); ?></small></td>
                            <td><?php echo e($pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'); ?></td>
                            <td><?php echo e($pegawai->jabatan); ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo e($pegawai->jenis_pegawai == 'btkl' ? 'primary' : 'success'); ?>">
                                    <?php echo e(strtoupper($pegawai->jenis_pegawai)); ?>

                                </span>
                            </td>
                            <td><?php echo e(strtoupper($pegawai->bank ?? '-')); ?></td>
                            <td><?php echo e($pegawai->nomor_rekening ?? '-'); ?></td>
                            <td><?php echo e($pegawai->nama_rekening ?? '-'); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($pegawai->gaji_pokok, 0, ',', '.')); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($pegawai->tarif_per_jam, 0, ',', '.')); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($pegawai->tunjangan, 0, ',', '.')); ?></td>
                            <td class="text-end">Rp <?php echo e(number_format($pegawai->asuransi, 0, ',', '.')); ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo e(route('master-data.pegawai.edit', ['pegawai' => $pegawai->id])); ?>" 
                                       class="btn btn-outline-primary" 
                                       data-bs-toggle="tooltip" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal<?php echo e($pegawai->id); ?>"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal<?php echo e($pegawai->id); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus data pegawai <strong><?php echo e($pegawai->nama); ?></strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="<?php echo e(route('master-data.pegawai.destroy', ['pegawai' => $pegawai->id])); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="17" class="text-center py-4">
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
<script>
    // Inisialisasi tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto close alert setelah 5 detik
    setTimeout(function() {
        var alert = document.querySelector('.alert');
        if (alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
</script>
<?php $__env->stopPush(); ?>

<style>
    .table-responsive { overflow-x: auto; }
    .table-wide { min-width: 1800px; }
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/pegawai/index.blade.php ENDPATH**/ ?>