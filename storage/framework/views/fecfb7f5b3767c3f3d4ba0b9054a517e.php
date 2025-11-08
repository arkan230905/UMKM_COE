<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Edit Produk</h1>

    <form action="<?php echo e(route('master-data.produk.update', $produk->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="<?php echo e($produk->nama_produk); ?>" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"><?php echo e($produk->deskripsi); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Presentase Keuntungan (%)</label>
            <input type="number" step="0.01" name="margin_percent" class="form-control" value="<?php echo e(old('margin_percent', $produk->margin_percent)); ?>">
            <small class="text-muted">Harga jual dihitung otomatis dari Harga BOM × (1 + Margin%).</small>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="<?php echo e(route('master-data.produk.index')); ?>" class="btn btn-secondary">Kembali</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/produk/edit.blade.php ENDPATH**/ ?>