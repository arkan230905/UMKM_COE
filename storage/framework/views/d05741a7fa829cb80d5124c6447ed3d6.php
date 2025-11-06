<?php $__env->startSection('content'); ?>
<div class="container">
    <h2 class="mb-4">Edit Bahan Baku</h2>

    <form action="<?php echo e(route('master-data.bahan-baku.update', $bahanBaku->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="stok" value="<?php echo e(old('stok', $bahanBaku->stok)); ?>">

        <div class="mb-3">
            <label for="nama_bahan" class="form-label">Nama Bahan</label>
            <input type="text" name="nama_bahan" class="form-control" value="<?php echo e($bahanBaku->nama_bahan); ?>" required>
        </div>

        <div class="mb-3">
            <label for="satuan_id" class="form-label">Satuan</label>
            <select name="satuan_id" id="satuan_id" class="form-select bg-dark text-white" required>
                <option value="" disabled>-- Pilih Satuan --</option>
                <?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($satuan->id); ?>" <?php echo e(old('satuan_id', $bahanBaku->satuan_id) == $satuan->id ? 'selected' : ''); ?>>
                        <?php echo e($satuan->nama); ?> (<?php echo e($satuan->kode); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['satuan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="text-danger"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3">
            <label for="harga_satuan" class="form-label">Harga Satuan (Rp)</label>
            <input type="number" 
                   name="harga_satuan" 
                   id="harga_satuan"
                   class="form-control <?php $__errorArgs = ['harga_satuan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                   value="<?php echo e(old('harga_satuan', $bahanBaku->harga_satuan)); ?>" 
                   step="0.01" 
                   min="0" 
                   required>
            <?php $__errorArgs = ['harga_satuan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button type="submit" class="btn btn-success">Perbarui</button>
        <a href="<?php echo e(route('master-data.bahan-baku.index')); ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/master-data/bahan-baku/edit.blade.php ENDPATH**/ ?>