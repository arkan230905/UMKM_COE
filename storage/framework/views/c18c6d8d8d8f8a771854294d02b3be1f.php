<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 text-light">
  <div class="bg-dark rounded-3 border border-secondary-subtle p-4 mb-4">
    <form method="get" class="row g-3 align-items-end">
      <div class="col-lg-3 col-md-4">
        <label for="period" class="form-label small text-uppercase text-secondary mb-1">Pilih Bulan</label>
        <input type="month" name="period" id="period" class="form-control bg-dark text-light border-secondary" value="<?php echo e($period); ?>">
      </div>
      <div class="col-auto">
        <button class="btn btn-primary px-4" type="submit">Filter</button>
      </div>
    </form>
  </div>

  <div class="px-lg-3">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-white mb-1">UMKM COE</h2>
      <h4 class="fw-semibold text-white-50 mb-1">Laporan Laba Rugi</h4>
      <span class="text-secondary">Periode <?php echo e($periodLabel); ?></span>
    </div>

    <div class="mb-5">
      <div class="text-uppercase text-secondary fw-semibold mb-3">Pendapatan</div>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $revenueAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
          <div class="me-3">
            <div class="small text-secondary"><?php echo e($account['code']); ?></div>
            <div class="fw-semibold text-white"><?php echo e($account['name']); ?></div>
          </div>
          <div class="text-end fw-semibold">Rp <?php echo e(number_format($account['amount'],0,',','.')); ?></div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-secondary fst-italic">Tidak ada pendapatan pada periode ini.</div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase small fw-bold">
        <span>Total Pendapatan</span>
        <span>Rp <?php echo e(number_format($totalRevenue,0,',','.')); ?></span>
      </div>
    </div>

    <div class="mb-5">
      <div class="text-uppercase text-secondary fw-semibold mb-3">Beban</div>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $expenseAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
          <div class="me-3">
            <div class="small text-secondary"><?php echo e($account['code']); ?></div>
            <div class="fw-semibold text-white"><?php echo e($account['name']); ?></div>
          </div>
          <div class="text-end fw-semibold">Rp <?php echo e(number_format($account['amount'],0,',','.')); ?></div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-secondary fst-italic">Tidak ada beban pada periode ini.</div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase small fw-bold">
        <span>Total Beban</span>
        <span>Rp <?php echo e(number_format($totalExpense,0,',','.')); ?></span>
      </div>
    </div>

    <div class="pt-4 border-top border-secondary d-flex justify-content-between align-items-center text-uppercase">
      <span class="fw-semibold"><?php echo e($netProfit >= 0 ? 'Laba Bersih' : 'Rugi Bersih'); ?></span>
      <span class="fw-bold fs-5 text-white">Rp <?php echo e(number_format(abs($netProfit),0,',','.')); ?></span>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($revenueAccounts->isEmpty() && $expenseAccounts->isEmpty()): ?>
      <div class="mt-4 p-3 rounded-3 border border-warning text-warning small">
        Data tidak tersedia untuk laba/rugi pada periode ini.
      </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/akuntansi/laba-rugi.blade.php ENDPATH**/ ?>