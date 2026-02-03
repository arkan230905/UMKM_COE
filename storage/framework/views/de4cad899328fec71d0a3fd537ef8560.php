<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📦 Tambah Produksi</h4>
        </div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mb-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form method="POST" action="<?php echo e(route('transaksi.produksi.store')); ?>">
                <?php echo csrf_field(); ?>
                
                <!-- Form Input -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">🏷️ Produk</label>
                        <select name="produk_id" id="produk_id" class="form-select form-select-lg" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($prod->id); ?>" data-harga="<?php echo e($prod->harga_pokok ?? 0); ?>">
                                    <?php echo e($prod->nama_produk); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📅 Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo e(now()->toDateString()); ?>" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📊 Qty</label>
                        <input type="number" name="qty_produksi" id="qty_produksi" step="0.01" min="0.01" class="form-control form-control-lg" required>
                    </div>
                </div>

                <!-- Informasi BOM Produk -->
                <div class="card bg-light mb-4" id="bom-info" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📋 Informasi BOM Produk</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <strong>Harga Pokok Produk:</strong> <span id="harga-pokok">Rp 0</span>
                            <br>
                            <small class="text-muted">Harga pokok akan dihitung berdasarkan BOM dan qty produksi</small>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="btn btn-secondary btn-lg">
                        ✖️ Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Simpan Produksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('produk_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const hargaPokok = selectedOption.getAttribute('data-harga');
    
    if (hargaPokok) {
        document.getElementById('bom-info').style.display = 'block';
        document.getElementById('harga-pokok').textContent = 'Rp ' + parseFloat(hargaPokok).toLocaleString('id-ID');
    } else {
        document.getElementById('bom-info').style.display = 'none';
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/transaksi/produksi/create.blade.php ENDPATH**/ ?>