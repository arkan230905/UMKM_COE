<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="bi bi-camera-fill me-2 text-primary"></i> Verifikasi Wajah Pegawai
        </h2>
        <a href="<?php echo e(route('transaksi.presensi.verifikasi-wajah.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tambah Verifikasi Wajah
        </a>
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

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>Daftar Verifikasi Wajah
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th style="min-width: 200px;">Nama Pegawai</th>
                            <th class="text-center" style="width: 120px;">NIP</th>
                            <th class="text-center" style="width: 100px;">Foto Wajah</th>
                            <th class="text-center" style="width: 140px;">Tanggal Verifikasi</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th class="text-center" style="width: 220px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $verifikasiWajahs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $verifikasi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e(($verifikasiWajahs->currentPage() - 1) * $verifikasiWajahs->perPage() + $index + 1); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($verifikasi->pegawai): ?>
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <?php echo e(strtoupper(substr($verifikasi->pegawai->nama, 0, 1))); ?>

                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="bi bi-person" style="font-size: 14px;"></i>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?php echo e(optional($verifikasi->pegawai)->nama ?? 'Tidak Diketahui'); ?></div>
                                        <small class="text-muted"><?php echo e(optional($verifikasi->pegawai)->jabatan ?? '-'); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark"><?php echo e(optional($verifikasi->pegawai)->kode_pegawai ?? '-'); ?></span>
                            </td>
                            <td class="text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($verifikasi->foto_wajah): ?>
                                    <div class="position-relative d-inline-block">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Storage::disk('public')->exists($verifikasi->foto_wajah)): ?>
                                            <img src="<?php echo e(Storage::url($verifikasi->foto_wajah)); ?>?v=<?php echo e(uniqid()); ?>" 
                                                 alt="Foto <?php echo e(optional($verifikasi->pegawai)->nama); ?>" 
                                                 class="img-thumbnail rounded-circle shadow-sm" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #e9ecef; cursor: pointer;"
                                                 onclick="viewPhoto('<?php echo e(Storage::url($verifikasi->foto_wajah)); ?>', '<?php echo e(optional($verifikasi->pegawai)->nama); ?>')"
                                                 onerror="this.onerror=null; this.src='<?php echo e(asset('images/default-avatar.png')); ?>';">
                                            <div class="position-absolute bottom-0 end-0 bg-success rounded-circle" 
                                                 style="width: 10px; height: 10px; border: 2px solid white;"></div>
                                        <?php else: ?>
                                            <div class="text-center">
                                                <i class="bi bi-image text-muted" style="font-size: 1.8rem;"></i>
                                                <div class="small text-muted">File Not Found</div>
                                                <div class="small text-muted"><?php echo e($verifikasi->foto_wajah); ?></div>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <i class="bi bi-person-circle text-muted" style="font-size: 1.8rem;"></i>
                                        <div class="small text-muted">No Photo</div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column">
                                    <span><?php echo e($verifikasi->created_at->format('d/m/Y')); ?></span>
                                    <small class="text-muted"><?php echo e($verifikasi->created_at->format('H:i')); ?></small>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($verifikasi->aktif): ?>
                                    <span class="badge bg-success d-flex align-items-center justify-content-center gap-1" style="width: 70px;">
                                        <i class="bi bi-check-circle" style="font-size: 10px;"></i>
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary d-flex align-items-center justify-content-center gap-1" style="width: 70px;">
                                        <i class="bi bi-x-circle" style="font-size: 10px;"></i>
                                        Non-Aktif
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="<?php echo e(route('transaksi.presensi.verifikasi-wajah.edit', $verifikasi->id)); ?>" 
                                       class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                                       title="Edit Verifikasi">
                                        <i class="bi bi-pencil-square"></i>
                                        <span class="d-none d-md-inline">Edit</span>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-info btn-sm d-flex align-items-center gap-1"
                                            onclick="viewPhoto('<?php echo e(asset('storage/' . $verifikasi->foto_wajah)); ?>?v=<?php echo e(uniqid()); ?>', '<?php echo e(optional($verifikasi->pegawai)->nama); ?>')"
                                            title="Lihat Foto">
                                        <i class="bi bi-eye"></i>
                                        <span class="d-none d-md-inline">Lihat</span>
                                    </button>
                                    <form action="<?php echo e(route('transaksi.presensi.verifikasi-wajah.destroy', $verifikasi->id)); ?>" 
                                          method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm d-flex align-items-center gap-1"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus verifikasi wajah untuk <?php echo e(optional($verifikasi->pegawai)->nama); ?>?')"
                                                title="Hapus Verifikasi">
                                            <i class="bi bi-trash"></i>
                                            <span class="d-none d-md-inline">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-camera fs-1 d-block mb-3 text-muted"></i>
                                <h5 class="mb-2">Belum ada data verifikasi wajah</h5>
                                <p class="mb-0">Klik tombol "Tambah Verifikasi Wajah" untuk menambahkan data baru</p>
                            </td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($verifikasiWajahs->hasPages()): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($verifikasiWajahs->links()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal for viewing photo -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="photoModalLabel">
                    <i class="bi bi-person-circle me-2"></i>Foto Verifikasi Wajah
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h6 class="mb-3 text-muted" id="pegawaiName"></h6>
                <img id="modalPhoto" src="" alt="Foto Wajah" class="img-fluid rounded shadow" style="max-height: 400px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function viewPhoto(photoUrl, pegawaiName) {
    console.log('Opening modal with photo:', photoUrl);
    console.log('Pegawai name:', pegawaiName);
    
    const modalPhoto = document.getElementById('modalPhoto');
    const pegawaiNameElement = document.getElementById('pegawaiName');
    
    if (modalPhoto && pegawaiNameElement) {
        modalPhoto.src = photoUrl;
        pegawaiNameElement.textContent = pegawaiName || 'Foto Verifikasi Wajah';
        
        // Add error handling for modal image
        modalPhoto.onerror = function() {
            console.error('Failed to load modal image:', photoUrl);
            this.src = '<?php echo e(asset('images/default-avatar.png')); ?>';
        };
        
        modalPhoto.onload = function() {
            console.log('Modal image loaded successfully:', photoUrl);
        };
        
        const modal = new bootstrap.Modal(document.getElementById('photoModal'));
        modal.show();
    } else {
        console.error('Modal elements not found');
    }
}

// Debug: Check if Bootstrap is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded!');
    } else {
        console.log('Bootstrap is loaded, modal function available');
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/transaksi/presensi/verifikasi-wajah/index.blade.php ENDPATH**/ ?>