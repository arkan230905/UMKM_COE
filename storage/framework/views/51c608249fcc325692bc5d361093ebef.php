<?php $__env->startSection('title', 'Jurnal Umum'); ?>

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
        <a href="<?php echo e(route('akuntansi.jurnal-umum.export-pdf', request()->all())); ?>" 
           class="btn btn-danger btn-sm" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> Export PDF
        </a>
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
            <option value="production_material" <?php echo e($refType === 'production_material' ? 'selected' : ''); ?>>Produksi - Material</option>
            <option value="production_labor_overhead" <?php echo e($refType === 'production_labor_overhead' ? 'selected' : ''); ?>>Produksi - BTKL</option>
            <option value="production_bop" <?php echo e($refType === 'production_bop' ? 'selected' : ''); ?>>Produksi - BOP</option>
            <option value="production_finish" <?php echo e($refType === 'production_finish' ? 'selected' : ''); ?>>Produksi - Barang Jadi</option>
            <option value="saldo_awal" <?php echo e($refType === 'saldo_awal' ? 'selected' : ''); ?>>Saldo Awal</option>
            <option value="pembayaran_beban" <?php echo e($refType === 'pembayaran_beban' ? 'selected' : ''); ?>>Pembayaran Beban</option>
            <option value="penggajian" <?php echo e($refType === 'penggajian' ? 'selected' : ''); ?>>Penggajian</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Filter Akun</label>
          <select name="account_code" class="form-select">
            <option value="">Semua Akun</option>
            <?php
              $coas = \App\Models\Coa::orderBy('kode_akun')->get();
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($coa->kode_akun); ?>" <?php echo e(request('account_code') === $coa->kode_akun ? 'selected' : ''); ?>><?php echo e($coa->kode_akun); ?> - <?php echo e($coa->nama_akun); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
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
    <div class="col-md-12">
      <div class="card border-left border-primary border-4">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-6 text-center">
              <i class="bi bi-arrow-up-circle fs-2 text-primary"></i>
              <h6 class="text-muted mb-2">Total Debit</h6>
              <h4 class="mb-0 text-primary">Rp <?php echo e(number_format($entries->flatMap->lines->sum('debit'), 0, ',', '.')); ?></h4>
            </div>
            <div class="col-6 text-center">
              <i class="bi bi-arrow-down-circle fs-2 text-success"></i>
              <h6 class="text-muted mb-2">Total Kredit</h6>
              <h4 class="mb-0 text-success">Rp <?php echo e(number_format($entries->flatMap->lines->sum('credit'), 0, ',', '.')); ?></h4>
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
      <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-hover mb-0" style="border: 2px solid #dee2e6; min-width: 1400px;">
          <thead class="table-light" style="position: sticky; top: 0; z-index: 2; background-color: #f8f9fa;">
            <tr>
              <th class="border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Tanggal</th>
              <th class="border-end" style="width:25%; border-bottom: 2px solid #dee2e6;">Deskripsi</th>
              <th class="border-end" style="width:8%; border-bottom: 2px solid #dee2e6;">Kode Akun</th>
              <th class="border-end" style="width:25%; border-bottom: 2px solid #dee2e6;">Nama Akun</th>
              <th class="border-end" style="width:12%; border-bottom: 2px solid #dee2e6;">Keterangan</th>
              <th class="text-end border-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Debit</th>
              <th class="text-end" style="width:10%; border-bottom: 2px solid #dee2e6;">Kredit</th>
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
                      </div>
                    </td>
                    <td rowspan="<?php echo e($e->lines->count()); ?>" class="align-middle" style="border-right: 2px solid #dee2e6;">
                      <div>
                        <?php echo e($e->memo); ?>

                      </div>
                    </td>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <td class="align-middle" style="border-right: 2px solid #dee2e6;">
                    <div class="fw-semibold"><?php echo e($l->coa->kode_akun); ?></div>
                  </td>
                  <td class="align-middle" style="border-right: 2px solid #dee2e6;">
                    <div class="fw-semibold">
                      <?php echo e($l->coa->nama_akun); ?>

                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->coa->tipe_akun): ?>
                      <div class="small text-muted"><?php echo e($l->coa->tipe_akun); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </td>
                  <td class="align-middle" style="border-right: 2px solid #dee2e6;">
                    <div class="text-muted small">
                      <?php echo e($l->memo ?? '-'); ?>

                    </div>
                  </td>
                  <td class="align-middle text-end" style="border-right: 2px solid #dee2e6;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->debit > 0): ?>
                      <span class="text-primary fw-semibold">Rp <?php echo e(number_format($l->debit,0,',','.')); ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </td>
                  <td class="align-middle text-end">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($l->credit > 0): ?>
                      <span class="text-success fw-semibold">Rp <?php echo e(number_format($l->credit,0,',','.')); ?></span>
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="text-center py-4">
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
  
  /* Ensure table cells don't truncate text */
  .table td, .table th {
    white-space: normal !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
  }
  
  /* Remove any text truncation */
  .text-truncate {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: clip !important;
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/akuntansi/jurnal-umum.blade.php ENDPATH**/ ?>