<?php $__env->startSection('content'); ?>
<div class="container">
  <h3>Invoice Pembayaran Beban #<?php echo e($row->id); ?></h3>
  <p>Tanggal: <?php echo e($row->tanggal); ?></p>
  <p>Beban: <?php echo e($row->coa->kode_akun); ?> - <?php echo e($row->coa->nama_akun); ?></p>
  <p>Nominal: Rp <?php echo e(number_format($row->nominal,0,',','.')); ?></p>
  <p>Keterangan: <?php echo e($row->deskripsi); ?></p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/expense-payment/show.blade.php ENDPATH**/ ?>