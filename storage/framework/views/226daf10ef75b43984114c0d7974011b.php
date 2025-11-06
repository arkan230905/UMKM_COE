<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <h4 class="mb-4">Data Penjualan</h4>

    <a href="<?php echo e(route('transaksi.penjualan.create')); ?>" class="btn btn-primary mb-3">Tambah Penjualan</a>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th style="width:6%">ID</th>
                <th style="width:12%">Tanggal</th>
                <th style="width:10%">Pembayaran</th>
                <th>Produk</th>
                <th class="text-end" style="width:10%">Qty</th>
                <th class="text-end" style="width:12%">Harga/Satuan</th>
                <th class="text-end" style="width:10%">Diskon %</th>
                <th class="text-end" style="width:12%">Diskon (Rp)</th>
                <th class="text-end" style="width:12%">Total</th>
                <th style="width:14%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $penjualans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penjualan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($penjualan->id); ?></td>
                <td><?php echo e(optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal); ?></td>
                <td><?php echo e(($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai'); ?></td>
                <?php $detailCount = $penjualan->details->count(); ?>
                <td>
                    <?php if($detailCount > 1): ?>
                        <?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div><?php echo e($d->produk->nama_produk ?? '-'); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif($detailCount === 1): ?>
                        <?php echo e($penjualan->details[0]->produk->nama_produk ?? '-'); ?>

                    <?php else: ?>
                        <?php echo e($penjualan->produk?->nama_produk ?? '-'); ?>

                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <?php if($detailCount > 1): ?>
                        <?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div><?php echo e(rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',')); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif($detailCount === 1): ?>
                        <?php echo e(rtrim(rtrim(number_format($penjualan->details[0]->jumlah,4,',','.'),'0'),',')); ?>

                    <?php else: ?>
                        <?php echo e(rtrim(rtrim(number_format($penjualan->jumlah,4,',','.'),'0'),',')); ?>

                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <?php if($detailCount > 1): ?>
                        <?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>Rp <?php echo e(number_format($d->harga_satuan ?? 0, 0, ',', '.')); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif($detailCount === 1): ?>
                        Rp <?php echo e(number_format($penjualan->details[0]->harga_satuan ?? 0, 0, ',', '.')); ?>

                    <?php else: ?>
                        <?php
                            $hdrHarga = $penjualan->harga_satuan;
                            if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                            }
                        ?>
                        Rp <?php echo e(number_format($hdrHarga ?? 0, 0, ',', '.')); ?>

                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <?php if($detailCount > 1): ?>
                        <?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $sub = (float)$d->jumlah * (float)$d->harga_satuan; $disc = (float)($d->diskon_nominal ?? 0); $pct = $sub>0 ? ($disc/$sub*100) : 0; ?>
                            <div><?php echo e(number_format($pct, 2, ',', '.')); ?>%</div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif($detailCount === 1): ?>
                        <?php $d=$penjualan->details[0]; $sub=(float)$d->jumlah*(float)$d->harga_satuan; $disc=(float)($d->diskon_nominal??0); $pct=$sub>0?($disc/$sub*100):0; ?>
                        <?php echo e(number_format($pct, 2, ',', '.')); ?>%
                    <?php else: ?>
                        <?php $pct=0; if(($penjualan->jumlah??0)>0){ $hdrHarga=$penjualan->harga_satuan; if(is_null($hdrHarga)){ $hdrHarga=((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0))/(float)$penjualan->jumlah; } $subtotal=$penjualan->jumlah*$hdrHarga; $pct=$subtotal>0?(((float)($penjualan->diskon_nominal ?? 0))/$subtotal*100):0; } ?>
                        <?php echo e(number_format($pct, 2, ',', '.')); ?>%
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <?php if($detailCount > 1): ?>
                        <?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>Rp <?php echo e(number_format($d->diskon_nominal ?? 0, 0, ',', '.')); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif($detailCount === 1): ?>
                        Rp <?php echo e(number_format($penjualan->details[0]->diskon_nominal ?? 0, 0, ',', '.')); ?>

                    <?php else: ?>
                        Rp <?php echo e(number_format($penjualan->diskon_nominal ?? 0, 0, ',', '.')); ?>

                    <?php endif; ?>
                </td>
                <td class="text-end">Rp <?php echo e(number_format($penjualan->total, 0, ',', '.')); ?></td>
                <td>
                    <a href="<?php echo e(route('transaksi.penjualan.edit', $penjualan->id)); ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id])); ?>" class="btn btn-outline-primary btn-sm">Jurnal</a>
                    <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'sale_cogs', 'ref_id' => $penjualan->id])); ?>" class="btn btn-outline-secondary btn-sm">Jurnal HPP</a>
                    <form action="<?php echo e(route('transaksi.penjualan.destroy', $penjualan->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\COE_EADT_UMKM_COMPLETE\resources\views/transaksi/penjualan/index.blade.php ENDPATH**/ ?>