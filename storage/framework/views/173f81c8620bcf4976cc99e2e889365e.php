<?php $__env->startSection('content'); ?>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pembayaran Beban</h3>
    <a href="<?php echo e(route('transaksi.pembayaran-beban.create')); ?>" class="btn btn-primary">Tambah</a>
  </div>

  <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>#</th><th>Tanggal</th><th>COA Beban</th><th>Nominal</th><th>Keterangan</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr>
        <td><?php echo e($loop->iteration); ?></td>
        <td><?php echo e($r->tanggal->format('d/m/Y')); ?></td>
        <td>
            <?php if($r->coaBeban): ?>
                <?php echo e($r->coaBeban->kode_akun); ?> - <?php echo e($r->coaBeban->nama_akun); ?>

            <?php else: ?>
                <span class="text-danger">Akun beban tidak ditemukan (<?php echo e($r->coa_beban_id); ?>)</span>
            <?php endif; ?>
        </td>
        <td>Rp <?php echo e(number_format($r->nominal, 0, ',', '.')); ?></td>
        <td><?php echo e($r->deskripsi); ?></td>
        <td>
          <div class="btn-group" role="group">
            <a href="<?php echo e(route('transaksi.pembayaran-beban.show', $r->id)); ?>" class="btn btn-info btn-sm" title="Invoice">
              <i class="fas fa-file-invoice"></i>
            </a>
            <a href="<?php echo e(route('transaksi.pembayaran-beban.edit', $r->id)); ?>" class="btn btn-warning btn-sm" title="Edit">
              <i class="fas fa-edit"></i>
            </a>
            <form action="<?php echo e(route('transaksi.pembayaran-beban.destroy', $r->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
              <?php echo csrf_field(); ?>
              <?php echo method_field('DELETE'); ?>
              <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr>
        <td colspan="6" class="text-center">Tidak ada data pembayaran beban</td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php echo e($rows->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/expense-payment/index.blade.php ENDPATH**/ ?>