<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Jurnal Umum</h3>
    <div class="d-flex gap-2">
      <a href="<?php echo e(route('akuntansi.jurnal-umum.export-pdf', request()->all())); ?>" class="btn btn-danger" target="_blank">
        <i class="bi bi-file-pdf"></i> Download PDF
      </a>
      
      
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
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
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th style="width:10%">Tanggal</th>
          <th style="width:12%">Ref</th>
          <th style="width:20%">Memo</th>
          <th style="width:8%">Kode Akun</th>
          <th style="width:22%">Nama Akun</th>
          <th class="text-end" style="width:14%">Debit</th>
          <th class="text-end" style="width:14%">Kredit</th>
        </tr>
      </thead>
      <tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $e->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i===0): ?>
                <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e(\Carbon\Carbon::parse($e->tanggal)->format('d/m/Y')); ?></td>
                <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e($e->ref_type); ?>#<?php echo e($e->ref_id); ?></td>
                <td rowspan="<?php echo e($e->lines->count()); ?>"><?php echo e($e->memo); ?></td>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <td>
                <strong><?php echo e($l->account->code ?? '-'); ?></strong>
                <span class="badge bg-<?php echo e(($l->debit ?? 0) > 0 ? 'info' : 'warning'); ?> ms-1"><?php echo e(($l->debit ?? 0) > 0 ? 'D' : 'K'); ?></span>
              </td>
              <td>
                <strong><?php echo e($l->account->name ?? 'Akun tidak ditemukan'); ?></strong>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->account): ?>
                  <br><small class="text-muted"><?php echo e($l->account->type ?? ''); ?></small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </td>
              <td class="text-end"><?php echo e($l->debit>0 ? 'Rp '.number_format($l->debit,0,',','.') : '-'); ?></td>
              <td class="text-end"><?php echo e($l->credit>0 ? 'Rp '.number_format($l->credit,0,',','.') : '-'); ?></td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="7" class="text-center">Tidak ada data jurnal</td>
          </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </tbody>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($entries->count() > 0): ?>
      <tfoot class="table-secondary">
        <tr>
          <th colspan="5" class="text-end">Total:</th>
          <th class="text-end">
            Rp <?php echo e(number_format($entries->flatMap->lines->sum('debit'), 0, ',', '.')); ?>

          </th>
          <th class="text-end">
            Rp <?php echo e(number_format($entries->flatMap->lines->sum('credit'), 0, ',', '.')); ?>

          </th>
        </tr>
      </tfoot>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/akuntansi/jurnal-umum.blade.php ENDPATH**/ ?>