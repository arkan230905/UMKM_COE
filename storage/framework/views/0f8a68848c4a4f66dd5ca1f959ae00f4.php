<?php $__env->startSection('content'); ?>
<div class="container">
  <h3>Invoice Pelunasan Utang #<?php echo e($row->id); ?></h3>
  <p>Tanggal: <?php echo e($row->tanggal); ?></p>
  <p>Pembelian: #<?php echo e($row->pembelian_id); ?> - Vendor: <?php echo e($row->pembelian->vendor->nama_vendor ?? '-'); ?></p>
  <p>Total Tagihan: Rp <?php echo e(number_format($row->total_tagihan,0,',','.')); ?></p>
  <p>Diskon: Rp <?php echo e(number_format($row->diskon,0,',','.')); ?></p>
  <p>Denda/Bunga: Rp <?php echo e(number_format($row->denda_bunga,0,',','.')); ?></p>
  <p>Dibayar Bersih: Rp <?php echo e(number_format($row->dibayar_bersih,0,',','.')); ?></p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/ap-settlement/show.blade.php ENDPATH**/ ?>