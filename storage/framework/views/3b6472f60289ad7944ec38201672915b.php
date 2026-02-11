<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
  <!-- Header Section -->
  <div class="row mb-4">
    <div class="col-md-6">
      <h1 class="h3 mb-0">
        <i class="bi bi-journal-text me-2"></i>
        Jurnal Umum
      </h1>
      <p class="text-muted mb-0">Catatan transaksi keuangan perusahaan</p>
    </div>
    <div class="col-md-6 text-end">
      <div class="d-flex gap-2 justify-content-end">
        <a href="<?php echo e(route('akuntansi.jurnal-umum.export-pdf', request()->all())); ?>" class="btn btn-danger btn-sm">
          <i class="bi bi-file-pdf me-1"></i> PDF
        </a>
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
          <i class="bi bi-printer me-1"></i> Cetak
        </button>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form method="get" class="row g-3 align-items-end">
        <div class="col-md-2">
          <label class="form-label fw-semibold">Dari Tanggal</label>
          <input type="date" name="from" value="<?php echo e($from); ?>" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Sampai Tanggal</label>
          <input type="date" name="to" value="<?php echo e($to); ?>" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Tipe Transaksi</label>
          <select name="ref_type" class="form-select">
            <option value="">Semua</option>
            <option value="purchase" <?php echo e($refType === 'purchase' ? 'selected' : ''); ?>>Pembelian</option>
            <option value="sale" <?php echo e($refType === 'sale' ? 'selected' : ''); ?>>Penjualan</option>
            <option value="production" <?php echo e($refType === 'production' ? 'selected' : ''); ?>>Produksi</option>
            <option value="saldo_awal" <?php echo e($refType === 'saldo_awal' ? 'selected' : ''); ?>>Saldo Awal</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Filter Akun</label>
          <select name="account_code" class="form-select">
            <option value="">Semua Akun</option>
            <option value="5101" <?php echo e(request('account_code') === '5101' ? 'selected' : ''); ?>>HPP (5101)</option>
            <option value="1107" <?php echo e(request('account_code') === '1107' ? 'selected' : ''); ?>>Persediaan Barang Jadi (1107)</option>
            <option value="4101" <?php echo e(request('account_code') === '4101' ? 'selected' : ''); ?>>Penjualan (4101)</option>
            <option value="1101" <?php echo e(request('account_code') === '1101' ? 'selected' : ''); ?>>Kas (1101)</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">ID Transaksi</label>
          <input type="number" name="ref_id" value="<?php echo e($refId ?? ''); ?>" class="form-control" placeholder="ID">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i> Filter
          </button>
        </div>
        <div class="col-md-2">
          <a href="<?php echo e(route('akuntansi.jurnal-umum')); ?>" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Summary Cards -->
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($entries->count() > 0): ?>
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card border-left border-primary border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="text-muted mb-2">Total Debit</h6>
              <h4 class="mb-0 text-primary">Rp <?php echo e(number_format($entries->flatMap->lines->sum('debit'), 0, ',', '.')); ?></h4>
            </div>
            <div class="text-primary">
              <i class="bi bi-arrow-up-circle fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-left border-success border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="text-muted mb-2">Total Kredit</h6>
              <h4 class="mb-0 text-success">Rp <?php echo e(number_format($entries->flatMap->lines->sum('credit'), 0, ',', '.')); ?></h4>
            </div>
            <div class="text-success">
              <i class="bi bi-arrow-down-circle fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-left border-info border-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h6 class="text-muted mb-2">Balance</h6>
              <h4 class="mb-0 text-info">Rp <?php echo e(number_format($entries->flatMap->lines->sum('debit') - $entries->flatMap->lines->sum('credit'), 0, ',', '.')); ?></h4>
            </div>
            <div class="text-info">
              <i class="bi bi-balance-scale fs-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <!-- Journal Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" style="border: 2px solid #dee2e6;">
          <thead class="table-light sticky-top">
            <tr>
              <th class="border-end" style="width:8%; border-bottom: 2px solid #dee2e6;">Tanggal</th>
              <th class="border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Ref</th>
              <th class="border-end" style="width:25%; border-bottom: 2px solid #dee2e6;">Deskripsi</th>
              <th class="border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Kode Akun</th>
              <th class="border-end" style="width:20%; border-bottom: 2px solid #dee2e6;">Nama Akun</th>
              <th class="text-end border-end" style="width:12%; border-bottom: 2px solid #dee2e6;">Debit</th>
              <th class="text-end" style="width:12%; border-bottom: 2px solid #dee2e6;">Kredit</th>
              <th class="text-center" style="width:5%; border-bottom: 2px solid #dee2e6;">D/K</th>
            </tr>
          </thead>
          <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $e->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($i % 2 === 0 ? 'bg-light' : ''); ?>" style="border-bottom: 1px solid #dee2e6;">
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i===0): ?>
                    <td rowspan="<?php echo e($e->lines->count()); ?>" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div class="text-center">
                        <div class="fw-bold"><?php echo e(\Carbon\Carbon::parse($e->tanggal)->format('d/m/Y')); ?></div>
                        <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($e->tanggal)->format('H:i')); ?></small>
                      </div>
                    </td>
                    <td rowspan="<?php echo e($e->lines->count()); ?>" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div>
                        <?php
                          $badgeColor = match($e->ref_type) {
                            'purchase' => 'danger',
                            'sale' => 'success',
                            'production' => 'warning',
                            'saldo_awal' => 'info',
                            default => 'secondary'
                          };
                        ?>
                        <span class="badge bg-<?php echo e($badgeColor); ?> text-white">
                          <?php echo e($e->ref_type); ?>

                        </span>
                        <div class="small text-muted">#<?php echo e($e->ref_id); ?></div>
                      </div>
                    </td>
                    <td rowspan="<?php echo e($e->lines->count()); ?>" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div class="text-truncate" style="max-width: 150px;" title="<?php echo e($e->memo); ?>">
                        <?php echo e($e->memo); ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($e->ref_type === 'sale'): ?>
                          <?php
                            $hppTotal = 0;
                            $penjualanTotal = 0;
                            foreach($e->lines as $line) {
                              if($line->account->code === '5101') {
                                $hppTotal = $line->debit;
                              }
                              if($line->account->code === '1101') {
                                $penjualanTotal = $line->debit;
                              }
                            }
                            $margin = $penjualanTotal - $hppTotal;
                            $marginPercent = $penjualanTotal > 0 ? ($margin / $penjualanTotal * 100) : 0;
                          ?>
                          
                          <div class="mt-2">
                            <small class="text-muted d-block">Detail HPP:</small>
                            <div class="d-flex gap-2 flex-wrap">
                              <small class="badge bg-light text-dark">
                                <i class="bi bi-cash-stack me-1"></i>HPP: Rp <?php echo e(number_format($hppTotal,0,',','.')); ?>

                              </small>
                              <small class="badge bg-light text-dark">
                                <i class="bi bi-graph-up <?php echo e($margin >= 0 ? 'text-success' : 'text-danger'); ?> me-1"></i>Margin: <?php echo e($margin >= 0 ? '+' : ''); ?><?php echo e(number_format($marginPercent,1,',','.')); ?>%
                              </small>
                            </div>
                          </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                      </div>
                    </td>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <td class="align-middle" style="border-right: 1px solid #dee2e6;">
                    <code class="text-primary"><?php echo e($l->account->code ?? '-'); ?></code>
                  </td>
                  <td class="align-middle" style="border-right: 1px solid #dee2e6;">
                    <div>
                      <div class="fw-semibold"><?php echo e($l->account->name ?? 'Akun tidak ditemukan'); ?></div>
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->account): ?>
                        <small class="text-muted"><?php echo e($l->account->type ?? ''); ?></small>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($e->ref_type === 'sale' && $l->account->code === '5101'): ?>
                          <div class="mt-1">
                            <small class="badge bg-warning text-dark">
                              <i class="bi bi-info-circle me-1"></i>HPP Penjualan
                            </small>
                          </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($e->ref_type === 'sale' && $l->account->code === '1107'): ?>
                          <div class="mt-1">
                            <small class="badge bg-info text-dark">
                              <i class="bi bi-box-seam me-1"></i>Persediaan Keluar
                            </small>
                          </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                  </td>
                  <td class="align-middle text-end" style="border-right: 1px solid #dee2e6;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->debit > 0): ?>
                      <span class="text-primary fw-semibold">Rp <?php echo e(number_format($l->debit,0,',','.')); ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </td>
                  <td class="align-middle text-end" style="border-right: 1px solid #dee2e6;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->credit > 0): ?>
                      <span class="text-success fw-semibold">Rp <?php echo e(number_format($l->credit,0,',','.')); ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </td>
                  <td class="align-middle text-center">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->debit > 0): ?>
                      <span class="badge bg-primary text-white">D</span>
                    <?php else: ?>
                      <span class="badge bg-success text-white">K</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="8" class="text-center py-4">
                  <div class="text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <h5>Tidak ada data jurnal</h5>
                    <p class="mb-0">Silakan pilih filter yang berbeda atau tambahkan data jurnal terlebih dahulu</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<style>
  .no-print {
    display: none !important;
  }
  
  @media print {
    .no-print { 
      display: none !important; 
    }
    .table th, .table td { 
      padding: .5rem .5rem !important; 
      font-size: 12px !important;
    }
    .card {
      box-shadow: none !important;
      border: 1px solid #dee2e6 !important;
    }
    .badge {
      font-size: 8px !important;
    }
    body { 
      -webkit-print-color-adjust: exact; 
      print-color-adjust: exact; 
    }
  }
</style>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/akuntansi/jurnal-umum.blade.php ENDPATH**/ ?>