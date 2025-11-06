<?php $__env->startSection('content'); ?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pembayaran Beban</h3>
    <a href="<?php echo e(route('transaksi.expense-payment.create')); ?>" class="btn btn-primary">Tambah</a>
  </div>

  <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>#</th><th>Tanggal</th><th>COA Beban</th><th>Nominal</th><th>Keterangan</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e($loop->iteration); ?></td>
        <td><?php echo e($r->tanggal); ?></td>
        <td><?php echo e($r->coa->kode_akun); ?> - <?php echo e($r->coa->nama_akun); ?></td>
        <td>Rp <?php echo e(number_format($r->nominal,0,',','.')); ?></td>
        <td><?php echo e($r->deskripsi); ?></td>
        <td><a href="<?php echo e(route('transaksi.expense-payment.show', $r->id)); ?>" class="btn btn-info btn-sm">Invoice</a></td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>

  <?php echo e($rows->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/expense-payment/index.blade.php ENDPATH**/ ?>