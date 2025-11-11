<?php $__env->startSection('content'); ?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pelunasan Utang</h3>
  </div>

  <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-header bg-warning text-dark">
      <strong>Pembelian Kredit Belum Lunas</strong>
    </div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tanggal</th>
            <th>Vendor</th>
            <th>Item Dibeli</th>
            <th class="text-end">Total Utang</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $openPurchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e($loop->iteration); ?></td>
            <td><?php echo e(optional($p->tanggal)->format('d/m/Y') ?? $p->tanggal); ?></td>
            <td><?php echo e($p->vendor->nama_vendor ?? '-'); ?></td>
            <td>
              <?php if($p->details && $p->details->count() > 0): ?>
                <small>
                <?php $__currentLoopData = $p->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php echo e($detail->bahanBaku->nama_bahan ?? '-'); ?> 
                  (<?php echo e(number_format($detail->jumlah ?? 0, 0, ',', '.')); ?>)
                  <?php if(!$loop->last): ?>, <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </small>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
            <td class="text-end">
              <?php
                // Ambil total_harga dari database atau hitung dari details
                $totalHarga = $p->total_harga ?? 0;
                
                // Jika total_harga masih 0, hitung dari details
                if ($totalHarga == 0 && $p->details && $p->details->count() > 0) {
                    $totalHarga = $p->details->sum(function($detail) {
                        return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                    });
                }
                
                // Hitung sisa utang
                $terbayar = $p->terbayar ?? 0;
                $sisaUtang = $totalHarga - $terbayar;
              ?>
              <strong class="text-danger">Rp <?php echo e(number_format($sisaUtang, 0, ',', '.')); ?></strong>
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="<?php echo e(route('transaksi.ap-settlement.create', ['pembelian_id'=>$p->id])); ?>">
                <i class="fas fa-money-bill-wave"></i> Lunasi
              </a>
            </td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-3">
              <i class="fas fa-check-circle fa-2x mb-2"></i>
              <p class="mb-0">Tidak ada utang yang perlu dilunasi</p>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <h5>Riwayat Pelunasan</h5>
  <table class="table table-bordered">
    <thead class="table-dark"><tr><th>#</th><th>Tanggal</th><th>Vendor</th><th>Pembelian</th><th>Dibayar</th><th>Diskon</th><th>Denda</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e($loop->iteration); ?></td>
        <td><?php echo e($r->tanggal); ?></td>
        <td><?php echo e($r->pembelian->vendor->nama_vendor ?? '-'); ?></td>
        <td>#<?php echo e($r->pembelian_id); ?></td>
        <td>Rp <?php echo e(number_format($r->dibayar_bersih,0,',','.')); ?></td>
        <td>Rp <?php echo e(number_format($r->diskon,0,',','.')); ?></td>
        <td>Rp <?php echo e(number_format($r->denda_bunga,0,',','.')); ?></td>
        <td><a href="<?php echo e(route('transaksi.ap-settlement.show', $r->id)); ?>" class="btn btn-info btn-sm">Invoice</a></td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
  <?php echo e($rows->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/ap-settlement/index.blade.php ENDPATH**/ ?>