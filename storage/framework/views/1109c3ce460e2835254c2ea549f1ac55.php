<?php $__env->startSection('content'); ?>
<div class="container">
    <h3 class="mb-3">Detail Produksi</h3>

    <div class="card mb-3">
        <div class="card-body">
            <div><strong>Produk:</strong> <?php echo e($produksi->produk->nama_produk); ?></div>
            <div><strong>Tanggal:</strong> <?php echo e($produksi->tanggal); ?></div>
            <div><strong>Qty Produksi:</strong> <?php echo e(rtrim(rtrim(number_format($produksi->qty_produksi,4,',','.'),'0'),',')); ?></div>
            <div><strong>Total Bahan:</strong> Rp <?php echo e(number_format($produksi->total_bahan,0,',','.')); ?></div>
            <div><strong>BTKL:</strong> Rp <?php echo e(number_format($produksi->total_btkl,0,',','.')); ?></div>
            <div><strong>BOP:</strong> Rp <?php echo e(number_format($produksi->total_bop,0,',','.')); ?></div>
            <div><strong>Total Biaya:</strong> Rp <?php echo e(number_format($produksi->total_biaya,0,',','.')); ?></div>
        </div>
    </div>

    <h5>Bahan Terpakai</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Nama Bahan</th>
                <th>Resep (Total)</th>
                <th>Konversi ke Satuan Bahan</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $produksi->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($d->bahanBaku->nama_bahan); ?></td>
                    <td><?php echo e(rtrim(rtrim(number_format($d->qty_resep,4,',','.'),'0'),',')); ?> <?php echo e($d->satuan_resep); ?></td>
                    <td><?php echo e(rtrim(rtrim(number_format($d->qty_konversi,4,',','.'),'0'),',')); ?> <?php echo e($d->bahanBaku->satuan); ?></td>
                    <td>Rp <?php echo e(number_format($d->harga_satuan,0,',','.')); ?> / <?php echo e($d->bahanBaku->satuan); ?></td>
                    <td>Rp <?php echo e(number_format($d->subtotal,0,',','.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between">
        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'production_material', 'ref_id' => $produksi->id])); ?>" class="btn btn-outline-primary btn-sm">Lihat Jurnal (Material→WIP)</a>
        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'production_labor_overhead', 'ref_id' => $produksi->id])); ?>" class="btn btn-outline-primary btn-sm">Lihat Jurnal (BTKL/BOP→WIP)</a>
        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'production_finish', 'ref_id' => $produksi->id])); ?>" class="btn btn-outline-primary btn-sm">Lihat Jurnal (WIP→Barang Jadi)</a>
        <a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/transaksi/produksi/show.blade.php ENDPATH**/ ?>