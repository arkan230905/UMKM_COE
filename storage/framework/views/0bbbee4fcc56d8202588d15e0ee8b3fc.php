<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-plus-circle"></i> Tambah Penggajian</h3>

    <div class="card bg-dark text-white border-0">
        <div class="card-body">
            <form action="<?php echo e(route('transaksi.penggajian.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label for="pegawai_id" class="form-label">Pilih Pegawai</label>
                    <select name="pegawai_id" id="pegawai_id" class="form-select" required>
                        <option value="">-- Pilih Pegawai --</option>
                        <?php $__currentLoopData = $pegawais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pegawai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pegawai->id); ?>"><?php echo e($pegawai->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tanggal_penggajian" class="form-label">Tanggal Penggajian</label>
                    <input type="date" name="tanggal_penggajian" id="tanggal_penggajian" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="<?php echo e(route('transaksi.penggajian.index')); ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/penggajian/create.blade.php ENDPATH**/ ?>