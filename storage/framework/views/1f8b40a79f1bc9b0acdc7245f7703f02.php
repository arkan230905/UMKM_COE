<?php $__env->startSection('content'); ?>
<div class="container">
  <h3>Edit Pembayaran Beban</h3>
  
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </ul>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <form action="<?php echo e(route('transaksi.pembayaran-beban.update', $row->id)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    
    <div class="mb-3">
      <label class="form-label">Tanggal</label>
      <input type="date" name="tanggal" class="form-control" value="<?php echo e($row->tanggal); ?>" required>
    </div>
    
    <div class="mb-3">
      <label class="form-label">COA Beban</label>
      <select name="coa_beban_id" class="form-select" required>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($c->kode_akun); ?>" <?php echo e($row->coa_beban_id == $c->kode_akun ? 'selected' : ''); ?>>
            <?php echo e($c->kode_akun); ?> - <?php echo e($c->nama_akun); ?>

          </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </select>
    </div>
    
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Metode Bayar</label>
        <select name="metode_bayar" class="form-select">
          <option value="cash" <?php echo e($row->metode_bayar == 'cash' ? 'selected' : ''); ?>>Cash</option>
          <option value="bank" <?php echo e($row->metode_bayar == 'bank' ? 'selected' : ''); ?>>Bank</option>
        </select>
      </div>
      
      <div class="col-md-4">
        <label class="form-label">COA Kas/Bank</label>
        <select name="coa_kasbank" class="form-select">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $kasbank; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($k->kode_akun); ?>" <?php echo e($row->coa_kasbank == $k->kode_akun ? 'selected' : ''); ?>>
              <?php echo e($k->kode_akun); ?> - <?php echo e($k->nama_akun); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
      </div>
      
      <div class="col-md-4">
        <label class="form-label">Nominal</label>
        <input type="number" step="0.01" min="0" name="nominal" class="form-control" value="<?php echo e($row->nominal); ?>" required>
      </div>
    </div>
    
    <div class="mb-3 mt-3">
      <label class="form-label">Keterangan</label>
      <input type="text" name="deskripsi" class="form-control" value="<?php echo e($row->deskripsi); ?>">
    </div>
    
    <button type="submit" class="btn btn-success">Update</button>
    <a href="<?php echo e(route('transaksi.pembayaran-beban.index')); ?>" class="btn btn-secondary">Batal</a>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/expense-payment/edit.blade.php ENDPATH**/ ?>