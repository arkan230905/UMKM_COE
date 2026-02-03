<?php $__env->startSection('content'); ?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Neraca Saldo</h3>
    <div class="d-flex gap-2">
      <form method="get" class="d-flex gap-2 align-items-end">
        <div>
          <label class="form-label">Pilih Periode</label>
          <select name="period_id" class="form-select" onchange="this.form.submit()" style="min-width: 200px;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($p->id); ?>" <?php echo e($periode && $periode->id == $p->id ? 'selected' : ''); ?>>
                <?php echo e(\Carbon\Carbon::parse($p->periode.'-01')->isoFormat('MMMM YYYY')); ?>

                <?php echo e($p->is_closed ? '✓' : ''); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </select>
        </div>
      </form>
      
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($periode && !$periode->is_closed): ?>
        <form method="post" action="<?php echo e(route('coa-period.post', $periode->id)); ?>" onsubmit="return confirm('Yakin ingin menutup periode ini dan posting saldo ke periode berikutnya?')">
          <?php echo csrf_field(); ?>
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-success d-block">
            <i class="bi bi-check-circle"></i> Post Saldo Akhir
          </button>
        </form>
      <?php else: ?>
        <form method="post" action="<?php echo e(route('coa-period.reopen', $periode->id)); ?>" onsubmit="return confirm('Yakin ingin membuka kembali periode ini?')">
          <?php echo csrf_field(); ?>
          <label class="form-label">&nbsp;</label>
          <button type="submit" class="btn btn-warning d-block">
            <i class="bi bi-unlock"></i> Buka Periode
          </button>
        </form>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </div>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?php echo e(session('success')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?php echo e(session('error')); ?>

      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

  <div class="card">
    <div class="card-header bg-primary text-white">
      <strong>NERACA SALDO</strong>
      <div class="float-end">
        <strong>Periode: <?php echo e(\Carbon\Carbon::parse($periode->periode.'-01')->isoFormat('MMMM YYYY')); ?></strong>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($periode->is_closed): ?>
          <span class="badge bg-success ms-2">Periode Ditutup</span>
        <?php else: ?>
          <span class="badge bg-warning ms-2">Periode Aktif</span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle" style="border: 2px solid #dee2e6;">
          <thead class="table-dark sticky-top">
            <tr>
              <th class="text-center" style="width:5%">No</th>
              <th style="width:10%">Kode Akun</th>
              <th style="width:25%">Nama Akun</th>
              <th class="text-end" style="width:15%">Saldo Awal</th>
              <th class="text-end" style="width:15%">Debit</th>
              <th class="text-end" style="width:15%">Kredit</th>
              <th class="text-end" style="width:15%">Saldo Akhir</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $totalSaldoAwal = 0;
              $totalDebit = 0; 
              $totalKredit = 0; 
              $totalSaldoAkhir = 0;
              
              // Group accounts by type
              $assetAccounts = [];
              $liabilityAccounts = [];
              $equityAccounts = [];
              $revenueAccounts = [];
              $expenseAccounts = [];
              
              foreach($coas as $coa) {
                $data = $totals[$coa->kode_akun] ?? ['saldo_awal' => 0, 'debit' => 0, 'kredit' => 0, 'saldo_akhir' => 0];
                $accountData = [
                  'coa' => $coa,
                  'data' => $data
                ];
                
                switch($coa->tipe_akun) {
                  case 'Asset':
                    $assetAccounts[] = $accountData;
                    break;
                  case 'Liability':
                    $liabilityAccounts[] = $accountData;
                    break;
                  case 'Equity':
                    $equityAccounts[] = $accountData;
                    break;
                  case 'Revenue':
                    $revenueAccounts[] = $accountData;
                    break;
                  case 'Expense':
                    $expenseAccounts[] = $accountData;
                    break;
                }
              }
            ?>
            
            <!-- ASSETS -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-building me-2"></i>AKTIVA
              </td>
            </tr>
            <?php $rowNumber = 1; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $assetAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php 
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];
                
                $totalSaldoAwal += $saldoAwal;
                $totalSaldoAkhir += $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              ?>
              <tr>
                <td class="text-center"><?php echo e($rowNumber++); ?></td>
                <td><strong><?php echo e($coa->kode_akun); ?></strong></td>
                <td><?php echo e($coa->nama_akun); ?></td>
                <td class="text-end"><?php echo e($saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end fw-bold"><?php echo e($saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-'); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <!-- LIABILITIES -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-credit-card me-2"></i>PASIVA
              </td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $liabilityAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php 
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];
                
                $totalSaldoAwal -= $saldoAwal;
                $totalSaldoAkhir -= $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              ?>
              <tr>
                <td class="text-center"><?php echo e($rowNumber++); ?></td>
                <td><strong><?php echo e($coa->kode_akun); ?></strong></td>
                <td><?php echo e($coa->nama_akun); ?></td>
                <td class="text-end"><?php echo e($saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end fw-bold"><?php echo e($saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-'); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <!-- EQUITY -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-wallet2 me-2"></i>EKUITAS
              </td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $equityAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php 
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];
                
                $totalSaldoAwal -= $saldoAwal;
                $totalSaldoAkhir -= $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              ?>
              <tr>
                <td class="text-center"><?php echo e($rowNumber++); ?></td>
                <td><strong><?php echo e($coa->kode_akun); ?></strong></td>
                <td><?php echo e($coa->nama_akun); ?></td>
                <td class="text-end"><?php echo e($saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end fw-bold"><?php echo e($saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-'); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <!-- REVENUE -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-graph-up me-2"></i>PENDAPATAN
              </td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $revenueAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php 
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];
                
                $totalSaldoAwal -= $saldoAwal;
                $totalSaldoAkhir -= $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              ?>
              <tr>
                <td class="text-center"><?php echo e($rowNumber++); ?></td>
                <td><strong><?php echo e($coa->kode_akun); ?></strong></td>
                <td><?php echo e($coa->nama_akun); ?></td>
                <td class="text-end"><?php echo e($saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end fw-bold"><?php echo e($saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-'); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <!-- EXPENSES -->
            <tr class="table-secondary">
              <td colspan="7" class="fw-bold text-center">
                <i class="bi bi-graph-down me-2"></i>BEBAN
              </td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $expenseAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php 
                $coa = $item['coa'];
                $data = $item['data'];
                $saldoAwal = $data['saldo_awal'];
                $debit = $data['debit'];
                $kredit = $data['kredit'];
                $saldoAkhir = $data['saldo_akhir'];
                
                $totalSaldoAwal += $saldoAwal;
                $totalSaldoAkhir += $saldoAkhir;
                $totalDebit += $debit;
                $totalKredit += $kredit;
              ?>
              <tr>
                <td class="text-center"><?php echo e($rowNumber++); ?></td>
                <td><strong><?php echo e($coa->kode_akun); ?></strong></td>
                <td>
                  <?php echo e($coa->nama_akun); ?>

                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coa->kode_akun === '5101'): ?>
                    <small class="badge bg-warning text-dark ms-2">HPP</small>
                  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <td class="text-end"><?php echo e($saldoAwal != 0 ? 'Rp '.number_format($saldoAwal, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($debit > 0 ? 'Rp '.number_format($debit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end"><?php echo e($kredit > 0 ? 'Rp '.number_format($kredit, 0, ',', '.') : '-'); ?></td>
                <td class="text-end fw-bold"><?php echo e($saldoAkhir != 0 ? 'Rp '.number_format($saldoAkhir, 0, ',', '.') : '-'); ?></td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tbody>
          <tfoot class="table-dark">
            <tr>
              <th colspan="3" class="text-end">TOTAL</th>
              <th class="text-end">Rp <?php echo e(number_format(abs($totalSaldoAwal), 0, ',', '.')); ?></th>
              <th class="text-end">Rp <?php echo e(number_format($totalDebit, 0, ',', '.')); ?></th>
              <th class="text-end">Rp <?php echo e(number_format($totalKredit, 0, ',', '.')); ?></th>
              <th class="text-end">Rp <?php echo e(number_format(abs($totalSaldoAkhir), 0, ',', '.')); ?></th>
            </tr>
            <tr>
              <th colspan="6" class="text-end">BALANCE CHECK:</th>
              <th class="text-end <?php echo e($totalDebit == $totalKredit ? 'text-success' : 'text-danger'); ?>">
                <?php echo e($totalDebit - $totalKredit); ?>

              </th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="row">
      <div class="col-md-6">
        <div class="alert alert-info">
          <strong><i class="bi bi-info-circle"></i> Informasi Neraca Saldo:</strong>
          <ul class="mb-0 mt-2">
            <li>Neraca saldo menunjukkan saldo semua akun per periode tertentu</li>
            <li>Aktiva = Pasiva + Ekuitas (Balance Sheet Equation)</li>
            <li>Total Debit = Total Kredit (Trial Balance)</li>
            <li>Saldo akhir periode ini menjadi saldo awal periode berikutnya</li>
          </ul>
        </div>
      </div>
      <div class="col-md-6">
        <div class="alert alert-warning">
          <strong><i class="bi bi-building"></i> Kategori Akun Manufaktur:</strong>
          <ul class="mb-0 mt-2">
            <li><strong>Aktiva:</strong> Kas, Bank, Persediaan (Bahan Baku, Barang Jadi, dll)</li>
            <li><strong>Pasiva:</strong> Hutang Usaha, Hutang Gaji, Hutang BOP</li>
            <li><strong>Ekuitas:</strong> Modal Pemilik, Laba Ditahan</li>
            <li><strong>Pendapatan:</strong> Penjualan Produk</li>
            <li><strong>Beban:</strong> HPP, Beban Gaji, Beban Listrik, BOP, dll</li>
          </ul>
        </div>
      </div>
    </div>
    
    <div class="alert alert-success">
      <strong><i class="bi bi-check-circle"></i> Standar Akuntansi Manufaktur:</strong>
      <div class="row mt-2">
        <div class="col-md-4">
          <h6>📦 Persediaan</h6>
          <ul class="mb-0">
            <li>Persediaan Bahan Baku (102)</li>
            <li>Persediaan Dalam Proses (1104)</li>
            <li>Persediaan Barang Jadi (1107)</li>
          </ul>
        </div>
        <div class="col-md-4">
          <h6>💰 HPP & COGS</h6>
          <ul class="mb-0">
            <li>Harga Pokok Penjualan (5101)</li>
            <li>Beban Overhead Pabrik (5102)</li>
            <li>Beban Penyusutan (5103)</li>
          </ul>
        </div>
        <div class="col-md-4">
          <h6>🏭 Produksi</h6>
          <ul class="mb-0">
            <li>Konsumsi Bahan ke WIP</li>
            <li>BTKL/BOP ke WIP</li>
            <li>Selesai Produksi</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/akuntansi/neraca-saldo.blade.php ENDPATH**/ ?>