<?php $__env->startSection('content'); ?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Jurnal Umum</h3>
    <form method="get" class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label">Dari</label>
        <input type="date" name="from" value="<?php echo e($from); ?>" class="form-control">
      </div>
      <div class="col-auto">
        <label class="form-label">Sampai</label>
        <input type="date" name="to" value="<?php echo e($to); ?>" class="form-control">
      </div>
      <div class="col-auto">
        <label class="form-label">Ref Type</label>
        <input type="text" name="ref_type" value="<?php echo e($refType ?? ''); ?>" class="form-control" placeholder="mis: purchase/sale/production_*">
      </div>
      <div class="col-auto">
        <label class="form-label">Ref ID</label>
        <input type="number" name="ref_id" value="<?php echo e($refId ?? ''); ?>" class="form-control" placeholder="ID transaksi">
      </div>
      <div class="col-auto">
        <button class="btn btn-primary">Terapkan</button>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:12%">Tanggal</th>
          <th>Ref</th>
          <th>Memo</th>
          <th>Akun</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Kredit</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $__currentLoopData = $e->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <?php if($i===0): ?>
                <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e($e->tanggal); ?></td>
                <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e($e->ref_type); ?>#<?php echo e($e->ref_id); ?></td>
                <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e($e->memo); ?></td>
              <?php endif; ?>
              <td>
                <?php echo e($l->account->code); ?> - <?php echo e($l->account->name); ?>

                <span class="badge bg-secondary ms-1"><?php echo e(($l->debit ?? 0) > 0 ? 'D' : 'K'); ?></span>
              </td>
              <td class="text-end"><?php echo e($l->debit>0 ? 'Rp '.number_format($l->debit,0,',','.') : '-'); ?></td>
              <td class="text-end"><?php echo e($l->credit>0 ? 'Rp '.number_format($l->credit,0,',','.') : '-'); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/akuntansi/jurnal-umum.blade.php ENDPATH**/ ?>