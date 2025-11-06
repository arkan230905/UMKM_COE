<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4" style="background-color: #1b1b28; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-white mb-0">
            <i class="bi bi-box-seam me-2"></i>Tambah Produk
        </h1>
        <a href="<?php echo e(route('master-data.produk.index')); ?>" class="btn btn-outline-light">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Terjadi Kesalahan!</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="<?php echo e(route('master-data.produk.store')); ?>" method="POST" id="produkForm">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="nama_produk" class="form-label text-white">
                        <i class="bi bi-tag me-1"></i>Nama Produk <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nama_produk" id="nama_produk" 
                           class="form-control bg-dark text-white border-dark" 
                           value="<?php echo e(old('nama_produk')); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label text-white">
                        <i class="bi bi-card-text me-1"></i>Deskripsi Produk (Opsional)
                    </label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" 
                              class="form-control bg-dark text-white border-dark"><?php echo e(old('deskripsi')); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="harga_jual" class="form-label text-white">
                        <i class="bi bi-currency-dollar me-1"></i>Harga Jual (Rp)
                    </label>
                    <input type="number" name="harga_jual" id="harga_jual" 
                           class="form-control bg-dark text-white border-dark" value="" readonly>
                    <small class="text-muted">Harga jual akan dihitung otomatis setelah menambahkan BOM.</small>
                </div>

                <div class="border rounded p-3 mb-3" style="background-color: #1e1e2f; border-color: #2d2d3a !important;">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-sliders me-1"></i>Parameter Harga & Overhead
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="bi bi-percent me-1"></i>Presentase Keuntungan (%)
                            </label>
                            <input type="number" step="0.01" name="margin_percent" 
                                   class="form-control bg-dark text-white border-dark" 
                                   value="<?php echo e(old('margin_percent', 30)); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="bi bi-gear me-1"></i>Metode BOPB
                            </label>
                            <select name="bopb_method" class="form-select bg-dark text-white border-dark">
                                <option value="">- Pilih -</option>
                                <option value="per_unit">Per Unit Produksi</option>
                                <option value="per_hour">Per Jam Kerja</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="bi bi-currency-dollar me-1"></i>Tarif BOPB
                            </label>
                            <input type="number" step="0.01" name="bopb_rate" 
                                   class="form-control bg-dark text-white border-dark" 
                                   placeholder="Biaya per unit / per jam">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="bi bi-clock me-1"></i>Jam Tenaga Kerja/Unit
                            </label>
                            <input type="number" step="0.01" name="labor_hours_per_unit" 
                                   class="form-control bg-dark text-white border-dark" 
                                   placeholder="Wajib bila metode per jam">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white">
                                <i class="bi bi-people me-1"></i>BTKL per Unit
                            </label>
                            <input type="number" step="0.01" name="btkl_per_unit" 
                                   class="form-control bg-dark text-white border-dark" 
                                   placeholder="Jika kosong, gunakan default">
                        </div>
                    </div>
                </div>
            </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="<?php echo e(route('master-data.produk.index')); ?>" class="btn btn-outline-light">
                        <i class="bi bi-x-circle me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('produkForm');
    
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat fa-spin me-1"></i> Menyimpan...';
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<style>
    .form-control, .form-select, .form-control:focus, .form-select:focus {
        background-color: #1e1e2f !important;
        border-color: #2d2d3a !important;
        color: #ffffff !important;
    }
    
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25) !important;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    option {
        background-color: #1e1e2f;
        color: #ffffff;
    }
    
    .card {
        background-color: #222232;
        border: 1px solid #2d2d3a;
    }
    
    .text-muted {
        color: #8a8a9a !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/produk/create.blade.php ENDPATH**/ ?>