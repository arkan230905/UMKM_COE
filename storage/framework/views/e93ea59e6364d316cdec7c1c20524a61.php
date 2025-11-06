<?php $__env->startSection('content'); ?>
<div class="container">
  <h3>Pelunasan Utang Pembelian #<?php echo e($pembelian->id); ?></h3>
  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>
  <form action="<?php echo e(route('transaksi.ap-settlement.store')); ?>" method="POST"><?php echo csrf_field(); ?>
    <input type="hidden" name="pembelian_id" value="<?php echo e($pembelian->id); ?>">
    <div class="mb-3">
      <label class="form-label">Tanggal</label>
      <input type="date" name="tanggal" class="form-control" required>
    </div>
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Total Tagihan</label>
        <input type="number" step="0.01" min="0" name="total_tagihan" class="form-control" value="<?php echo e($pembelian->total); ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Diskon</label>
        <input type="number" step="0.01" min="0" name="diskon" class="form-control" value="0">
      </div>
      <div class="col-md-3">
        <label class="form-label">Denda/Bunga</label>
        <input type="number" step="0.01" min="0" name="denda_bunga" class="form-control" value="0">
      </div>
      <div class="col-md-3">
        <label class="form-label">Dibayar Bersih</label>
        <input type="number" step="0.01" min="0" name="dibayar_bersih" id="dibayar_bersih" class="form-control" required>
      </div>
    </div>
    <div class="row g-3 mt-2">
      <div class="col-md-4">
        <label class="form-label">Metode Bayar</label>
        <select name="metode_bayar" class="form-select">
          <option value="cash">Cash</option>
          <option value="bank">Bank</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">COA Kas/Bank</label>
        <select name="coa_kasbank" class="form-select">
          <option value="101">101 - Kas</option>
        </select>
      </div>
    </div>
    <div class="mb-3 mt-2">
      <label class="form-label">Keterangan</label>
      <input type="text" name="keterangan" class="form-control">
    </div>
    <button class="btn btn-success">Simpan</button>
    <a href="<?php echo e(route('transaksi.ap-settlement.index')); ?>" class="btn btn-secondary">Batal</a>
  </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const total = document.querySelector('input[name="total_tagihan"]');
  const diskon = document.querySelector('input[name="diskon"]');
  const denda = document.querySelector('input[name="denda_bunga"]');
  const bersih = document.getElementById('dibayar_bersih');
  function recalc(){
    const t = parseFloat(total.value || '0')||0;
    const d = parseFloat(diskon.value || '0')||0;
    const f = parseFloat(denda.value || '0')||0;
    const val = Math.max(t - d + f, 0);
    bersih.value = val.toFixed(2);
  }
  [total,diskon,denda].forEach(el=>{ if(el){ el.addEventListener('input', recalc); }});
  recalc();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/ap-settlement/create.blade.php ENDPATH**/ ?>