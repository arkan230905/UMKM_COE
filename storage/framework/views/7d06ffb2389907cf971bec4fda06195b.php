<?php $__env->startSection('title', 'Edit Vendor'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Edit Vendor</h2>

    <form action="<?php echo e(route('master-data.vendor.update', $vendor->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
            <label for="nama_vendor" class="form-label">Nama Vendor <span style="color: red;">*</span></label>
            <input type="text" name="nama_vendor" class="form-control" value="<?php echo e(old('nama_vendor', $vendor->nama_vendor)); ?>" required>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori Vendor <span style="color: red;">*</span></label>
            <select name="kategori" class="form-control" required>
                <option value="Bahan Baku" <?php echo e(old('kategori', $vendor->kategori) == 'Bahan Baku' ? 'selected' : ''); ?>>Bahan Baku</option>
                <option value="Bahan Pendukung" <?php echo e(old('kategori', $vendor->kategori) == 'Bahan Pendukung' ? 'selected' : ''); ?>>Bahan Pendukung</option>
                <option value="Aset" <?php echo e(old('kategori', $vendor->kategori) == 'Aset' ? 'selected' : ''); ?>>Aset</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat <span style="color: red;">*</span></label>
            <input type="text" name="alamat" class="form-control" value="<?php echo e(old('alamat', $vendor->alamat)); ?>" required>
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telepon <span style="color: red;">*</span></label>
            <input type="text" name="no_telp" class="form-control" value="<?php echo e(old('no_telp', $vendor->no_telp)); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
            <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $vendor->email)); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="<?php echo e(route('master-data.vendor.index')); ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/vendor/edit.blade.php ENDPATH**/ ?>