<?php $__env->startSection('content'); ?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Buku Besar</h3>
    <div class="d-flex gap-2">
      <a href="<?php echo e(route('akuntansi.buku-besar.export-excel', ['from' => $from, 'to' => $to])); ?>" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Export Excel (Semua Akun)
      </a>
    </div>
  </div>

  <form method="get" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
      <label class="form-label">Akun</label>
      <select name="account_id" class="form-select" onchange="this.form.submit()">
        <option value="">-- Pilih Akun --</option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($a->id); ?>" <?php echo e(($accountId==$a->id)?'selected':''); ?>><?php echo e($a->code); ?> - <?php echo e($a->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">Dari</label>
      <input type="date" name="from" value="<?php echo e($from); ?>" class="form-control">
    </div>
    <div class="col-auto">
      <label class="form-label">Sampai</label>
      <input type="date" name="to" value="<?php echo e($to); ?>" class="form-control">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Terapkan</button>
    </div>
  </form>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($accountId): ?>
  <div class="mb-2"><strong>Saldo Awal:</strong> Rp <?php echo e(number_format($saldoAwal,0,',','.')); ?></div>
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:12%">Tanggal</th>
          <th>Ref</th>
          <th>Memo</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Kredit</th>
          <th class="text-end">Saldo</th>
        </tr>
      </thead>
      <tbody>
        <?php $saldo = (float)$saldoAwal; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $saldo += ((float)$l->debit - (float)$l->credit); ?>
          <tr>
            <td><?php echo e($l->entry->tanggal ?? ''); ?></td>
            <td><?php echo e($l->display_ref ?? (($l->entry->ref_type ?? '') . ($l->entry->ref_id ? '#' . $l->entry->ref_id : ''))); ?></td>
            <td><?php echo e($l->entry->memo ?? ''); ?></td>
            <td class="text-end"><?php echo e($l->debit>0 ? 'Rp '.number_format($l->debit,0,',','.') : '-'); ?></td>
            <td class="text-end"><?php echo e($l->credit>0 ? 'Rp '.number_format($l->credit,0,',','.') : '-'); ?></td>
            <td class="text-end">Rp <?php echo e(number_format($saldo,0,',','.')); ?></td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/akuntansi/buku-besar.blade.php ENDPATH**/ ?>