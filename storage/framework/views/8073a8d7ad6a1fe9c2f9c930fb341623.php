<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Data Penjualan
        </h2>
        <a href="<?php echo e(route('transaksi.penjualan.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Penjualan
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('transaksi.penjualan.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nomor Transaksi</label>
                        <input type="text" name="nomor_transaksi" class="form-control" 
                               value="<?php echo e(request('nomor_transaksi')); ?>" placeholder="Cari nomor transaksi...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" 
                               value="<?php echo e(request('tanggal_mulai')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" 
                               value="<?php echo e(request('tanggal_selesai')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Semua Metode</option>
                            <option value="cash" <?php echo e(request('payment_method') == 'cash' ? 'selected' : ''); ?>>Tunai</option>
                            <option value="transfer" <?php echo e(request('payment_method') == 'transfer' ? 'selected' : ''); ?>>Transfer</option>
                            <option value="credit" <?php echo e(request('payment_method') == 'credit' ? 'selected' : ''); ?>>Kredit</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="lunas" <?php echo e(request('status') == 'lunas' ? 'selected' : ''); ?>>Lunas</option>
                            <option value="belum_lunas" <?php echo e(request('status') == 'belum_lunas' ? 'selected' : ''); ?>>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="<?php echo e(route('transaksi.penjualan.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Riwayat Penjualan
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'payment_method', 'status'])): ?>
                    <small class="text-muted">(Filter Aktif)</small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Nomor Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pembayaran</th>
                            <th>Produk</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Harga/Satuan</th>
                            <th class="text-end">HPP</th>
                            <th class="text-end">Margin</th>
                            <th class="text-end">Diskon %</th>
                            <th class="text-end">Diskon (Rp)</th>
                            <th class="text-end">Total</th>
                            <th>Status Retur</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $penjualan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-center"><?php echo e($key + 1); ?></td>
                                <td><strong><?php echo e($penjualan->nomor_penjualan ?? '-'); ?></strong></td>
                                <td><?php echo e(optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal); ?></td>
                                <td>
                                    <span class="badge <?php echo e(($penjualan->payment_method ?? 'cash') === 'credit' ? 'bg-warning' : 'bg-success'); ?>">
                                        <?php echo e(($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai'); ?>

                                    </span>
                                </td>
                                <?php $detailCount = $penjualan->details->count(); ?>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailCount > 1): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div><?php echo e($d->produk->nama_produk ?? '-'); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($detailCount === 1): ?>
                                        <?php echo e($penjualan->details[0]->produk->nama_produk ?? '-'); ?>

                                    <?php else: ?>
                                        <?php echo e($penjualan->produk?->nama_produk ?? '-'); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailCount > 1): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div><?php echo e(rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',')); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($detailCount === 1): ?>
                                        <?php echo e(rtrim(rtrim(number_format($penjualan->details[0]->jumlah,4,',','.'),'0'),',')); ?>

                                    <?php else: ?>
                                        <?php echo e(rtrim(rtrim(number_format($penjualan->jumlah,4,',','.'),'0'),',')); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailCount > 1): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>Rp <?php echo e(number_format($d->harga_satuan ?? 0, 0, ',', '.')); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailCount > 1): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal); ?>
                                            <div>Rp <?php echo e(number_format($actualHPP, 0, ',', '.')); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($detailCount === 1): ?>
                                        <?php $actualHPP = $penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal); ?>
                                        Rp <?php echo e(number_format($actualHPP, 0, ',', '.')); ?>

                                    <?php else: ?>
                                        <?php $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0; ?>
                                        Rp <?php echo e(number_format($actualHPP, 0, ',', '.')); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailCount > 1): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $actualHPP = $d->produk->getHPPForSaleDate($penjualan->tanggal); $margin = ($d->harga_satuan - $actualHPP) * $d->jumlah; ?>
                                            <div class="<?php echo e($margin > 0 ? 'text-success' : 'text-danger'); ?>">Rp <?php echo e(number_format($margin, 0, ',', '.')); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($detailCount === 1): ?>
                                        <?php $actualHPP = $penjualan->details[0]->produk->getHPPForSaleDate($penjualan->tanggal); $margin = ($penjualan->details[0]->harga_satuan - $actualHPP) * $penjualan->details[0]->jumlah; ?>
                                        <div class="<?php echo e($margin > 0 ? 'text-success' : 'text-danger'); ?>">Rp <?php echo e(number_format($margin, 0, ',', '.')); ?></div>
                                    <?php else: ?>
                                        <?php $actualHPP = $penjualan->produk?->getHPPForSaleDate($penjualan->tanggal) ?? 0; $margin = ($hdrHarga - $actualHPP) * ($penjualan->jumlah ?? 0); ?>
                                        <div class="<?php echo e($margin > 0 ? 'text-success' : 'text-danger'); ?>">Rp <?php echo e(number_format($margin, 0, ',', '.')); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailCount > 1): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $sub = (float)$d->jumlah * (float)$d->harga_satuan; $disc = (float)($d->diskon_nominal ?? 0); $pct = $sub>0 ? ($disc/$sub*100) : 0; ?>
                                            <div><?php echo e(number_format($pct, 2, ',', '.')); ?>%</div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($detailCount === 1): ?>
                                        <?php $d=$penjualan->details[0]; $sub=(float)$d->jumlah*(float)$d->harga_satuan; $disc=(float)($d->diskon_nominal??0); $pct=$sub>0?($disc/$sub*100):0; ?>
                                        <?php echo e(number_format($pct, 2, ',', '.')); ?>%
                                    <?php else: ?>
                                        <?php $pct=0; if(($penjualan->jumlah??0)>0){ $hdrHarga=$penjualan->harga_satuan; if(is_null($hdrHarga)){ $hdrHarga=((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0))/(float)$penjualan->jumlah; } $subtotal=$penjualan->jumlah*$hdrHarga; $pct=$subtotal>0?(((float)($penjualan->diskon_nominal ?? 0))/$subtotal*100):0; } ?>
                                        <?php echo e(number_format($pct, 2, ',', '.')); ?>%
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailCount > 1): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $penjualan->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>Rp <?php echo e(number_format($d->diskon_nominal ?? 0, 0, ',', '.')); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($detailCount === 1): ?>
                                        Rp <?php echo e(number_format($penjualan->details[0]->diskon_nominal ?? 0, 0, ',', '.')); ?>

                                    <?php else: ?>
                                        Rp <?php echo e(number_format($penjualan->diskon_nominal ?? 0, 0, ',', '.')); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold">Rp <?php echo e(number_format($penjualan->total, 0, ',', '.')); ?></td>
                                <td>
                                    <?php
                                        // Cek apakah ada retur untuk penjualan ini
                                        $hasRetur = \App\Models\SalesReturn::where('penjualan_id', $penjualan->id)->exists();
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasRetur): ?>
                                        <span class="badge bg-danger">Ada Retur</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Tidak Ada Retur</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('transaksi.penjualan.edit', $penjualan->id)); ?>" class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo e(route('transaksi.retur-penjualan.create', ['penjualan_id' => $penjualan->id])); ?>" class="btn btn-outline-info">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id])); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-book"></i> Jurnal
                                        </a>
                                        <form action="<?php echo e(route('transaksi.penjualan.destroy', $penjualan->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/transaksi/penjualan/index.blade.php ENDPATH**/ ?>