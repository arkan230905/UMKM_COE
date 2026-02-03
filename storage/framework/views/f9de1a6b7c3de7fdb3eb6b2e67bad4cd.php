<?php $__env->startSection('title', 'Laporan Stok'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📦 Laporan Stok</h2>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('laporan.stok')); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipe</label>
                    <select name="tipe" class="form-select" id="tipeSelect">
                        <option value="material" <?php echo e(request('tipe', 'material') == 'material' ? 'selected' : ''); ?>>Bahan Baku</option>
                        <option value="product" <?php echo e(request('tipe') == 'product' ? 'selected' : ''); ?>>Produk</option>
                        <option value="bahan_pendukung" <?php echo e(request('tipe') == 'bahan_pendukung' ? 'selected' : ''); ?>>Bahan Pendukung</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item (Opsional)</label>
                    <select name="item_id" class="form-select" id="itemSelect">
                        <option value="">Semua Item</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('tipe', 'material') == 'material'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m->id); ?>" <?php echo e(request('item_id') == $m->id ? 'selected' : ''); ?>>
                                    <?php echo e($m->nama_bahan); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php elseif(request('tipe') == 'product'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" <?php echo e(request('item_id') == $p->id ? 'selected' : ''); ?>>
                                    <?php echo e($p->nama_produk); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php elseif(request('tipe') == 'bahan_pendukung'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanPendukungs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($bp->id); ?>" <?php echo e(request('item_id') == $bp->id ? 'selected' : ''); ?>>
                                    <?php echo e($bp->nama_bahan); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="<?php echo e(request('from')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="<?php echo e(request('to')); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="<?php echo e(route('laporan.stok')); ?>" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('item_id')): ?>
        <!-- Kartu Stok Detail untuk Item Tertentu -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📋 Kartu Stok - 
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipe == 'material'): ?>
                        <?php echo e($materials->firstWhere('id', request('item_id'))->nama_bahan ?? 'Bahan Baku'); ?>

                    <?php elseif($tipe == 'product'): ?>
                        <?php echo e($products->firstWhere('id', request('item_id'))->nama_produk ?? 'Produk'); ?>

                    <?php elseif($tipe == 'bahan_pendukung'): ?>
                        <?php echo e($bahanPendukungs->firstWhere('id', request('item_id'))->nama_bahan ?? 'Bahan Pendukung'); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <!-- Saldo Awal -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($saldoAwalQty > 0 || $saldoAwalNilai > 0): ?>
                <div class="alert alert-info">
                    <?php
                        $desimalAwal = ($saldoAwalQty != floor($saldoAwalQty)) ? 2 : 0;
                    ?>
                    <strong>Saldo Awal (sebelum <?php echo e(request('from') ?? 'periode'); ?>):</strong> 
                    <?php echo e(number_format($saldoAwalQty, $desimalAwal, ',', '.')); ?> unit | 
                    Nilai: Rp <?php echo e(number_format($saldoAwalNilai, 0, ',', '.')); ?>

                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Tanggal</th>
                                <th>Referensi</th>
                                <th class="text-end">Masuk (Qty)</th>
                                <th class="text-end">Masuk (Nilai)</th>
                                <th class="text-end">Keluar (Qty)</th>
                                <th class="text-end">Keluar (Nilai)</th>
                                <th class="text-end">Saldo (Qty)</th>
                                <th class="text-end">Saldo (Nilai)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $running; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <?php
                                    $desimalIn = ($r['in_qty'] != floor($r['in_qty'])) ? 2 : 0;
                                    $desimalOut = ($r['out_qty'] != floor($r['out_qty'])) ? 2 : 0;
                                    $desimalSaldo = ($r['saldo_qty'] != floor($r['saldo_qty'])) ? 2 : 0;
                                ?>
                                <td><?php echo e(\Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y')); ?></td>
                                <td><?php echo e($r['ref']); ?></td>
                                <td class="text-end"><?php echo e($r['in_qty'] > 0 ? number_format($r['in_qty'], $desimalIn, ',', '.') : '-'); ?></td>
                                <td class="text-end"><?php echo e($r['in_nilai'] > 0 ? 'Rp '.number_format($r['in_nilai'], 0, ',', '.') : '-'); ?></td>
                                <td class="text-end"><?php echo e($r['out_qty'] > 0 ? number_format($r['out_qty'], $desimalOut, ',', '.') : '-'); ?></td>
                                <td class="text-end"><?php echo e($r['out_nilai'] > 0 ? 'Rp '.number_format($r['out_nilai'], 0, ',', '.') : '-'); ?></td>
                                <td class="text-end"><strong><?php echo e(number_format($r['saldo_qty'], $desimalSaldo, ',', '.')); ?></strong></td>
                                <td class="text-end"><strong>Rp <?php echo e(number_format($r['saldo_nilai'], 0, ',', '.')); ?></strong></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Tidak ada pergerakan stok dalam periode ini
                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Ringkasan Stok Per Item -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">📦 Ringkasan Stok 
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipe == 'material'): ?>
                        Bahan Baku
                    <?php elseif($tipe == 'product'): ?>
                        Produk
                    <?php elseif($tipe == 'bahan_pendukung'): ?>
                        Bahan Pendukung
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="40%">Nama Item</th>
                                <th width="20%" class="text-end">Stok Saat Ini</th>
                                <th width="15%">Satuan</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tipe == 'material'): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($m->nama_bahan); ?></td>
                                    <td class="text-end">
                                        <?php
                                            $stok = $saldoPerItem[$m->id] ?? $m->stok ?? 0;
                                            $desimal = ($stok != floor($stok)) ? 2 : 0;
                                        ?>
                                        <strong><?php echo e(number_format($stok, $desimal, ',', '.')); ?></strong>
                                    </td>
                                    <td><?php echo e($m->satuan->nama ?? 'KG'); ?></td>
                                    <td class="text-center">
                                        <a href="<?php echo e(route('laporan.stok', ['tipe' => 'material', 'item_id' => $m->id, 'from' => request('from'), 'to' => request('to')])); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat Kartu Stok
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Tidak ada data bahan baku
                                    </td>
                                </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php elseif($tipe == 'product'): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($p->nama_produk); ?></td>
                                    <td class="text-end">
                                        <?php
                                            $stok = $saldoPerItem[$p->id] ?? $p->stok ?? 0;
                                            $desimal = ($stok != floor($stok)) ? 2 : 0;
                                        ?>
                                        <strong><?php echo e(number_format($stok, $desimal, ',', '.')); ?></strong>
                                    </td>
                                    <td><?php echo e($p->satuan->nama ?? 'PCS'); ?></td>
                                    <td class="text-center">
                                        <a href="<?php echo e(route('laporan.stok', ['tipe' => 'product', 'item_id' => $p->id, 'from' => request('from'), 'to' => request('to')])); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat Kartu Stok
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Tidak ada data produk
                                    </td>
                                </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php elseif($tipe == 'bahan_pendukung'): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bahanPendukungs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $bp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($bp->nama_bahan); ?></td>
                                    <td class="text-end">
                                        <?php
                                            $stok = $saldoPerItem[$bp->id] ?? $bp->stok ?? 0;
                                            $desimal = ($stok != floor($stok)) ? 2 : 0;
                                        ?>
                                        <strong><?php echo e(number_format($stok, $desimal, ',', '.')); ?></strong>
                                    </td>
                                    <td><?php echo e($bp->satuanRelation->nama ?? 'UNIT'); ?></td>
                                    <td class="text-center">
                                        <a href="<?php echo e(route('laporan.stok', ['tipe' => 'bahan_pendukung', 'item_id' => $bp->id, 'from' => request('from'), 'to' => request('to')])); ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat Kartu Stok
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Tidak ada data bahan pendukung
                                    </td>
                                </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<script>
    // Auto-reload item dropdown when tipe changes
    document.getElementById('tipeSelect').addEventListener('change', function() {
        // Submit form to reload with new tipe
        this.form.submit();
    });
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/laporan/stok/index.blade.php ENDPATH**/ ?>