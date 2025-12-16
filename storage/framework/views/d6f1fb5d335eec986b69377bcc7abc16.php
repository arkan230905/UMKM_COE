

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4 text-light">
  <div class="bg-dark rounded-3 border border-secondary-subtle p-4 mb-4">
    <form method="get" class="row g-3 align-items-end">
      <div class="col-lg-3 col-md-4">
        <label for="period" class="form-label small text-uppercase text-secondary mb-1">Periode</label>
        <input type="month" name="period" id="period" class="form-control bg-dark text-light border-secondary" value="<?php echo e($period); ?>">
      </div>
      <div class="col-auto">
        <button class="btn btn-primary px-4" type="submit">Terapkan</button>
      </div>
    </form>
  </div>

  <div class="px-lg-3">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-white mb-1"><?php echo e($companyName); ?></h2>
      <h4 class="fw-semibold text-white-50 mb-1">Laporan Neraca</h4>
      <span class="text-secondary">Periode <?php echo e($periodLabel); ?></span>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="mb-4">
          <div class="text-uppercase text-secondary fw-semibold mb-3">Aktiva</div>
          <?php $hasAssetItems = false; ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $assetGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $hasAssetItems = $hasAssetItems || !empty($group['items']); ?>
            <div class="mb-4">
              <div class="fw-semibold text-white mb-2"><?php echo e($group['label']); ?></div>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
                  <div class="me-3">
                    <div class="small text-secondary"><?php echo e($item['code']); ?></div>
                    <div class="fw-semibold text-white"><?php echo e($item['name']); ?></div>
                  </div>
                  <div class="text-end fw-semibold">Rp <?php echo e(number_format($item['amount'], 0, ',', '.')); ?></div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-secondary fst-italic">Tidak ada data</div>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <div class="d-flex justify-content-between pt-2 mt-2 border-top border-secondary-subtle text-uppercase small fw-semibold">
                <span>Subtotal <?php echo e($group['label']); ?></span>
                <span>Rp <?php echo e(number_format($group['subtotal'], 0, ',', '.')); ?></span>
              </div>
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasAssetItems): ?>
            <div class="text-secondary fst-italic">Tidak ada data aktiva untuk periode ini.</div>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase fw-bold fs-5">
          <span>Total Aktiva</span>
          <span>Rp <?php echo e(number_format($totalAssets, 0, ',', '.')); ?></span>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="mb-4">
          <div class="text-uppercase text-secondary fw-semibold mb-3">Pasiva</div>

          <div class="mb-4">
            <div class="fw-semibold text-white mb-2">Kewajiban</div>
            <?php $hasLiabilityItems = false; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $liabilityGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php $hasLiabilityItems = $hasLiabilityItems || !empty($group['items']); ?>
              <div class="mb-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
                    <div class="me-3">
                      <div class="small text-secondary"><?php echo e($item['code']); ?></div>
                      <div class="fw-semibold text-white"><?php echo e($item['name']); ?></div>
                    </div>
                    <div class="text-end fw-semibold">Rp <?php echo e(number_format($item['amount'], 0, ',', '.')); ?></div>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <div class="text-secondary fst-italic">Tidak ada data</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="d-flex justify-content-between pt-2 mt-2 border-top border-secondary-subtle text-uppercase small fw-semibold">
                  <span>Subtotal <?php echo e($group['label']); ?></span>
                  <span>Rp <?php echo e(number_format($group['subtotal'], 0, ',', '.')); ?></span>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasLiabilityItems): ?>
              <div class="text-secondary fst-italic">Tidak ada kewajiban untuk periode ini.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase fw-bold">
              <span>Total Kewajiban</span>
              <span>Rp <?php echo e(number_format($totalLiabilities, 0, ',', '.')); ?></span>
            </div>
          </div>

          <div class="mb-4">
            <div class="fw-semibold text-white mb-2">Modal &amp; Ekuitas</div>
            <?php
              $hasEquityItems = false;
              $runningNote = null;
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $equityGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $hasEquityItems = $hasEquityItems || !empty($group['items']);
                if(isset($group['meta']['net_profit']) && $group['meta']['net_profit'] != 0) {
                    $runningNote = $group['meta']['net_profit'];
                }
              ?>
              <div class="mb-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                  <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary-subtle">
                    <div class="me-3">
                      <div class="small text-secondary"><?php echo e($item['code']); ?></div>
                      <div class="fw-semibold text-white"><?php echo e($item['name']); ?></div>
                    </div>
                    <div class="text-end fw-semibold">Rp <?php echo e(number_format($item['amount'], 0, ',', '.')); ?></div>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                  <div class="text-secondary fst-italic">Tidak ada data</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="d-flex justify-content-between pt-2 mt-2 border-top border-secondary-subtle text-uppercase small fw-semibold">
                  <span>Subtotal <?php echo e($group['label']); ?></span>
                  <span>Rp <?php echo e(number_format($group['subtotal'], 0, ',', '.')); ?></span>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasEquityItems): ?>
              <div class="text-secondary fst-italic">Tidak ada modal/ekuitas untuk periode ini.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!is_null($runningNote)): ?>
              <div class="small text-secondary fst-italic">
                Termasuk <?php echo e($runningNote >= 0 ? 'laba' : 'rugi'); ?> tahun berjalan sebesar Rp <?php echo e(number_format(abs($runningNote), 0, ',', '.')); ?>.
              </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="d-flex justify-content-between pt-3 mt-3 border-top border-secondary text-uppercase fw-bold">
              <span>Total Modal &amp; Ekuitas</span>
              <span>Rp <?php echo e(number_format($totalEquity, 0, ',', '.')); ?></span>
            </div>
          </div>

          <div class="d-flex justify-content-between pt-3 mt-4 border-top border-secondary text-uppercase fw-bold fs-5">
            <span>Total Pasiva</span>
            <span>Rp <?php echo e(number_format($totalLiabilitiesEquity, 0, ',', '.')); ?></span>
          </div>
        </div>
      </div>

      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(abs($totalAssets - $totalLiabilitiesEquity) >= 1): ?>
        <div class="mt-4 p-3 rounded-3 border border-warning text-warning small">
          Selisih saldo terdeteksi. Mohon periksa jurnal agar neraca seimbang.
        </div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/akuntansi/neraca.blade.php ENDPATH**/ ?>