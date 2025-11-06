<?php $__env->startSection('content'); ?>
<div class="container">
    <h3 class="mb-3">Tambah Produksi</h3>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('transaksi.produksi.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Produk</label>
                <select name="produk_id" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($prod->id); ?>"><?php echo e($prod->nama_produk); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="<?php echo e(now()->toDateString()); ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Qty Produksi</label>
                <input type="number" name="qty_produksi" step="0.0001" min="0.0001" class="form-control" required>
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/produksi/create.blade.php ENDPATH**/ ?>