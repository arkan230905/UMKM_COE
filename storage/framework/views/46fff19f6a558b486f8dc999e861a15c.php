<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Pembelian</h3>
        <a href="<?php echo e(route('transaksi.pembelian.index')); ?>" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Tanggal:</strong> <?php echo e($pembelian->tanggal?->format('d-m-Y')); ?></div>
                <div class="col-md-4"><strong>Vendor:</strong> <?php echo e($pembelian->vendor->nama_vendor ?? '-'); ?></div>
                <div class="col-md-4"><strong>Total:</strong> Rp <?php echo e(number_format($pembelian->total,0,',','.')); ?></div>
                <div class="col-md-4"><strong>Pembayaran:</strong> <?php echo e(($pembelian->payment_method ?? 'cash')==='credit' ? 'Kredit' : 'Tunai'); ?></div>
            </div>
        </div>
    </div>

    <h5 class="mb-2">Rincian Barang</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Nama Bahan</th>
                    <th class="text-end">Kuantitas</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga per Satuan</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = ($pembelian->details ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i+1); ?></td>
                    <td><?php echo e($d->bahanBaku->nama_bahan ?? '-'); ?></td>
                    <td class="text-end"><?php echo e(rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',')); ?></td>
                    <td><?php echo e($d->satuan ?: ($d->bahanBaku->satuan ?? '-')); ?></td>
                    <td class="text-end">Rp <?php echo e(number_format($d->harga_satuan,0,',','.')); ?></td>
                    <td class="text-end">Rp <?php echo e(number_format($d->subtotal,0,',','.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-3">
        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'purchase', 'ref_id' => $pembelian->id])); ?>" class="btn btn-outline-primary btn-sm">Lihat Jurnal (Pembelian)</a>
        <a href="<?php echo e(route('transaksi.pembelian.index')); ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/pembelian/show.blade.php ENDPATH**/ ?>