<?php $__env->startSection('content'); ?>
<div class="container">
    <h3 class="mb-3">Laporan Stok</h3>

    <form method="GET" class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">Tipe Stok</label>
            <select name="tipe" class="form-select" onchange="this.form.submit()">
                <option value="material" <?php echo e(($tipe ?? 'material') === 'material' ? 'selected' : ''); ?>>Bahan Baku</option>
                <option value="product" <?php echo e(($tipe ?? '') === 'product' ? 'selected' : ''); ?>>Produk</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Item</label>
            <?php if(($tipe ?? 'material')==='material'): ?>
                <select name="item_id" class="form-select">
                    <option value="">-- Semua Bahan --</option>
                    <?php $__currentLoopData = ($materials ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>" <?php echo e(($itemId ?? '')==$m->id ? 'selected' : ''); ?>><?php echo e($m->nama_bahan); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php else: ?>
                <select name="item_id" class="form-select">
                    <option value="">-- Semua Produk --</option>
                    <?php $__currentLoopData = ($products ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>" <?php echo e(($itemId ?? '')==$p->id ? 'selected' : ''); ?>><?php echo e($p->nama_produk); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php endif; ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="from" value="<?php echo e($from ?? ''); ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="to" value="<?php echo e($to ?? ''); ?>" class="form-control">
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-primary" type="submit">Terapkan</button>
        </div>
    </form>

    <?php if(!empty($itemId)): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Jumlah Stok Awal</h6>
                <div>
                    <span class="me-3"><strong>Qty:</strong> <?php echo e(rtrim(rtrim(number_format($saldoAwalQty ?? 0,4,',','.'),'0'),',')); ?></span>
                    <span><strong>Nilai:</strong> Rp <?php echo e(number_format($saldoAwalNilai ?? 0, 0, ',', '.')); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="mb-2">Kartu Stok</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:12%">Tanggal</th>
                                <th>Referensi</th>
                                <th class="text-end">Masuk (Qty)</th>
                                <th class="text-end">Masuk (Rp)</th>
                                <th class="text-end">Keluar (Qty)</th>
                                <th class="text-end">Keluar (Rp)</th>
                                <th class="text-end">Jumlah Stok (Qty)</th>
                                <th class="text-end">Jumlah Stok (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = ($running ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($row['tanggal']); ?></td>
                                    <td><?php echo e($row['ref']); ?></td>
                                    <td class="text-end"><?php echo e(rtrim(rtrim(number_format($row['in_qty'],4,',','.'),'0'),',')); ?></td>
                                    <td class="text-end"><?php echo e($row['in_nilai']>0 ? 'Rp '.number_format($row['in_nilai'],0,',','.') : '-'); ?></td>
                                    <td class="text-end"><?php echo e(rtrim(rtrim(number_format($row['out_qty'],4,',','.'),'0'),',')); ?></td>
                                    <td class="text-end"><?php echo e($row['out_nilai']>0 ? 'Rp '.number_format($row['out_nilai'],0,',','.') : '-'); ?></td>
                                    <td class="text-end"><?php echo e(rtrim(rtrim(number_format($row['saldo_qty'],4,',','.'),'0'),',')); ?></td>
                                    <td class="text-end">Rp <?php echo e(number_format($row['saldo_nilai'],0,',','.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">Jumlah Stok per Item</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Nama</th>
                                <th class="text-end">Jumlah Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=1; ?>
                            <?php if(($tipe ?? 'material')==='material'): ?>
                                <?php $__currentLoopData = ($materials ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($i++); ?></td>
                                        <td><?php echo e($m->nama_bahan); ?></td>
                                        <td class="text-end">
                                            
                                            <?php if(isset($saldoPerItem[$m->id])): ?>
                                                <?php echo e(number_format($saldoPerItem[$m->id], 0, ',', '.')); ?>

                                            <?php else: ?>
                                                0
                                            <?php endif; ?>
                                            
                                            
                                            <?php
                                                $satuan = $m->satuan;
                                                
                                                // Jika satuan adalah string yang berisi JSON
                                                if (is_string($satuan) && strpos($satuan, '{') === 0) {
                                                    $decoded = json_decode($satuan, true);
                                                    if (json_last_error() === JSON_ERROR_NONE) {
                                                        echo isset($decoded['nama']) ? ' ' . $decoded['nama'] : '';
                                                    } else {
                                                        echo ' ' . $satuan;
                                                    }
                                                } 
                                                // Jika satuan adalah object atau array
                                                elseif (is_object($satuan) || is_array($satuan)) {
                                                    $satuan = (array) $satuan;
                                                    echo ' ' . ($satuan['nama'] ?? '');
                                                }
                                                // Jika satuan adalah string biasa
                                                else {
                                                    echo ' ' . $satuan;
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <?php $__currentLoopData = ($products ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($i++); ?></td>
                                        <td><?php echo e($p->nama_produk); ?></td>
                                        <td class="text-end">
                                            <?php if(isset($saldoPerItem[$p->id])): ?>
                                                <?php echo e(number_format($saldoPerItem[$p->id], 0, ',', '.')); ?>

                                            <?php else: ?>
                                                0
                                            <?php endif; ?>
                                            pcs
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/laporan/stok/index.blade.php ENDPATH**/ ?>