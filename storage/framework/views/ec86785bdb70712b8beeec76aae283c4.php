<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Transaksi Produksi</h3>
        <a href="<?php echo e(route('transaksi.produksi.create')); ?>" class="btn btn-primary">Tambah Produksi</a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Qty Produksi</th>
                <th>Total Biaya</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $produksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($p->tanggal); ?></td>
                    <td><?php echo e($p->produk->nama_produk); ?></td>
                    <td><?php echo e(rtrim(rtrim(number_format($p->qty_produksi,4,',','.'),'0'),',')); ?></td>
                    <td>Rp <?php echo e(number_format($p->total_biaya,0,',','.')); ?></td>
                    <td>
                        <a href="<?php echo e(route('transaksi.produksi.show', $p->id)); ?>" class="btn btn-info btn-sm">Detail</a>
                        <form action="<?php echo e(route('transaksi.produksi.destroy', $p->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus transaksi produksi ini? Data jurnal terkait juga akan dihapus.')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php echo e($produksis->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/transaksi/produksi/index.blade.php ENDPATH**/ ?>