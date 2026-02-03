<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Tambah Vendor</h2>

    <form action="<?php echo e(route('master-data.vendor.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label for="nama_vendor" class="form-label">Nama Vendor</label>
            <input type="text" name="nama_vendor" class="form-control" value="<?php echo e(old('nama_vendor')); ?>" required>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori Vendor</label>
            <select name="kategori" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Bahan Baku" <?php echo e(old('kategori') == 'Bahan Baku' ? 'selected' : ''); ?>>Bahan Baku</option>
                <option value="Bahan Pendukung" <?php echo e(old('kategori') == 'Bahan Pendukung' ? 'selected' : ''); ?>>Bahan Pendukung</option>
                <option value="Aset" <?php echo e(old('kategori') == 'Aset' ? 'selected' : ''); ?>>Aset</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" value="<?php echo e(old('alamat')); ?>">
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telepon</label>
            <input type="text" name="no_telp" class="form-control" value="<?php echo e(old('no_telp')); ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="<?php echo e(route('master-data.vendor.index')); ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/vendor/create.blade.php ENDPATH**/ ?>