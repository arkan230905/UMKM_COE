<?php $__env->startSection('content'); ?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pelunasan Utang</h3>
  </div>

  <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-header">Pembelian Kredit Belum Lunas</div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>#</th><th>Tanggal</th><th>Vendor</th><th>Total</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php $__currentLoopData = $openPurchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td><?php echo e($loop->iteration); ?></td>
            <td><?php echo e($p->tanggal); ?></td>
            <td><?php echo e($p->vendor->nama_vendor ?? '-'); ?></td>
            <td>Rp <?php echo e(number_format($p->total,0,',','.')); ?></td>
            <td><a class="btn btn-primary btn-sm" href="<?php echo e(route('transaksi.ap-settlement.create', ['pembelian_id'=>$p->id])); ?>">Lunasi</a></td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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