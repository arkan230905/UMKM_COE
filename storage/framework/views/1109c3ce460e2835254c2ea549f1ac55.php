<?php $__env->startSection('content'); ?>
<div class="container">
    <h3 class="mb-3">Detail Produksi</h3>

    <div class="card mb-3">
        <div class="card-body">
            <div><strong>Produk:</strong> <?php echo e($produksi->produk->nama_produk); ?></div>
            <div><strong>Tanggal:</strong> <?php echo e($produksi->tanggal); ?></div>
            <div><strong>Qty Produksi:</strong> <?php echo e(number_format($produksi->qty_produksi, 0, ',', '.')); ?> unit</div>
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
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($d->bahan_baku_id): ?>
                            <?php echo e($d->bahanBaku->nama_bahan); ?>

                            <small class="text-muted">(Bahan Baku)</small>
                        <?php elseif($d->bahan_pendukung_id): ?>
                            <?php echo e($d->bahanPendukung->nama_bahan); ?>

                            <small class="text-muted">(Bahan Pendukung)</small>
                        <?php else: ?>
                            -
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td><?php echo e(rtrim(rtrim(number_format($d->qty_resep,4,',','.'),'0'),',')); ?> <?php echo e($d->satuan_resep); ?></td>
                    <td><?php echo e(rtrim(rtrim(number_format($d->qty_konversi,4,',','.'),'0'),',')); ?> 
                    <?php
                        // Logic satuan yang sama dengan pegawai-pembelian
                        $satuanItem = 'unit';
                        
                        // Jika item diinput sebagai bahan baku (berdasarkan relation yang ada)
                        if ($d->bahan_baku_id && $d->bahanBaku) {
                            // Prioritas: detail->satuan, lalu relation->satuan->nama
                            $satuanItem = $d->satuan ?: ($d->bahanBaku->satuan->nama ?? $d->bahanBaku->satuan ?? 'unit');
                        }
                        // Jika item diinput sebagai bahan pendukung
                        elseif ($d->bahan_pendukung_id && $d->bahanPendukung) {
                            $satuanItem = $d->satuan ?: ($d->bahanPendukung->satuan->nama ?? $d->bahanPendukung->satuan ?? 'unit');
                        }
                        // Fallback jika relation tidak ada
                        else {
                            $satuanItem = $d->satuan ?: 'unit';
                        }
                    ?>
                    <?php echo e($satuanItem); ?></td>
                    <td>Rp <?php echo e(number_format($d->harga_satuan,0,',','.')); ?> / 
                    <?php
                        // Logic satuan yang sama untuk harga satuan
                        $satuanHarga = 'unit';
                        
                        if ($d->bahan_baku_id && $d->bahanBaku) {
                            $satuanHarga = $d->satuan ?: ($d->bahanBaku->satuan->nama ?? $d->bahanBaku->satuan ?? 'unit');
                        }
                        elseif ($d->bahan_pendukung_id && $d->bahanPendukung) {
                            $satuanHarga = $d->satuan ?: ($d->bahanPendukung->satuan->nama ?? $d->bahanPendukung->satuan ?? 'unit');
                        }
                        else {
                            $satuanHarga = $d->satuan ?: 'unit';
                        }
                    ?>
                    <?php echo e($satuanHarga); ?></td>
                    <td>Rp <?php echo e(number_format($d->subtotal,0,',','.')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    <!-- Detail BTKL -->
    <h5 class="mt-4">Total BTKL yang Bekerja</h5>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="text-primary">Rp <?php echo e(number_format($produksi->total_btkl, 0, ',', '.')); ?></h6>
                    <small class="text-muted">Total biaya tenaga kerja langsung untuk <?php echo e(number_format($produksi->qty_produksi, 0, ',', '.')); ?> unit produksi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail BOP -->
    <h5 class="mt-4">Total BOP yang Dijalankan</h5>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="text-warning">Rp <?php echo e(number_format($produksi->total_bop, 0, ',', '.')); ?></h6>
                    <small class="text-muted">Total biaya overhead pabrik untuk <?php echo e(number_format($produksi->qty_produksi, 0, ',', '.')); ?> unit produksi</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'production_material', 'ref_id' => $produksi->id])); ?>" class="btn btn-outline-primary btn-sm">Lihat Jurnal (Material→WIP)</a>
        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'production_labor_overhead', 'ref_id' => $produksi->id])); ?>" class="btn btn-outline-primary btn-sm">Lihat Jurnal (BTKL/BOP→WIP)</a>
        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'production_finish', 'ref_id' => $produksi->id])); ?>" class="btn btn-outline-primary btn-sm">Lihat Jurnal (WIP→Barang Jadi)</a>
        <a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/transaksi/produksi/show.blade.php ENDPATH**/ ?>