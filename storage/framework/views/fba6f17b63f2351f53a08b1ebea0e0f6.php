<?php $__env->startSection('title', 'Daftar Pembelian'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Daftar Pembelian
        </h2>
        <a href="<?php echo e(route('transaksi.pembelian.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Pembelian
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Transaksi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('transaksi.pembelian.index')); ?>">
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
                        <label class="form-label">Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">Semua Vendor</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vendors ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vendor->id); ?>" <?php echo e(request('vendor_id') == $vendor->id ? 'selected' : ''); ?>>
                                    <?php echo e($vendor->nama_vendor); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
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
                            <a href="<?php echo e(route('transaksi.pembelian.index')); ?>" class="btn btn-outline-secondary">
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
                <i class="fas fa-list me-2"></i>Riwayat Pembelian
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'payment_method', 'status'])): ?>
                    <small class="text-muted">(Filter Aktif)</small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">No</th>
                            <th>Nomor Transaksi</th>
                            <th>Nomor Faktur</th>
                            <th>Tanggal</th>
                            <th>Vendor</th>
                            <th>Item Dibeli</th>
                            <th>Pembayaran</th>
                            <th>Total Harga</th>
                            <th>Status Retur</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pembelians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $pembelian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($key + 1); ?></td>
                                <td style="color: #000; font-weight: bold;"><?php echo e($pembelian->nomor_pembelian ?? 'KOSONG'); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembelian->nomor_faktur): ?>
                                        <span class="badge bg-info"><?php echo e($pembelian->nomor_faktur); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php echo e($pembelian->tanggal->format('d-m-Y')); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-store text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($pembelian->vendor->nama_vendor ?? '-'); ?></div>
                                            <small class="text-muted">ID: <?php echo e($pembelian->vendor->id ?? '-'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembelian->details && $pembelian->details->count() > 0): ?>
                                        <small>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pembelian->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->bahan_baku_id && $detail->bahanBaku): ?>
                                                    • <span class="badge bg-primary">BB</span> <?php echo e($detail->bahanBaku->nama_bahan); ?> 
                                                    (<?php echo e(number_format($detail->jumlah, 0, '.', '')); ?> <?php echo e($detail->bahanBaku->satuan->nama ?? 'unit'); ?>)
                                                <?php elseif($detail->bahan_pendukung_id && $detail->bahanPendukung): ?>
                                                    • <span class="badge bg-info">BP</span> <?php echo e($detail->bahanPendukung->nama_bahan); ?> 
                                                    (<?php echo e(number_format($detail->jumlah, 0, '.', '')); ?> <?php echo e($detail->bahanPendukung->satuanRelation->nama ?? 'unit'); ?>)
                                                <?php else: ?>
                                                    • -
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                @ Rp <?php echo e(number_format($detail->harga_satuan ?? 0, 0, ',', '.')); ?>

                                                = <strong>Rp <?php echo e(number_format(($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0), 0, ',', '.')); ?></strong>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $paymentMethod = $pembelian->payment_method ?? 'cash';
                                        if ($paymentMethod === 'credit') {
                                            $badgeClass = 'bg-warning';
                                            $paymentText = 'Kredit';
                                        } elseif ($paymentMethod === 'transfer') {
                                            $badgeClass = 'bg-info';
                                            $paymentText = 'Transfer';
                                        } else {
                                            $badgeClass = 'bg-success';
                                            $paymentText = 'Tunai';
                                        }
                                    ?>
                                    <span class="badge <?php echo e($badgeClass); ?>">
                                        <?php echo e($paymentText); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php
                                        // Hitung total dari details untuk konsistensi
                                        $totalPembelian = 0;
                                        if ($pembelian->details && $pembelian->details->count() > 0) {
                                            $totalPembelian = $pembelian->details->sum(function($detail) {
                                                return ($detail->jumlah ?? 0) * ($detail->harga_satuan ?? 0);
                                            });
                                        }
                                        
                                        // Jika ada total_harga di database, gunakan yang lebih besar
                                        if ($pembelian->total_harga > $totalPembelian) {
                                            $totalPembelian = $pembelian->total_harga;
                                        }
                                    ?>
                                    <span class="fw-semibold">Rp <?php echo e(number_format($totalPembelian, 0, ',', '.')); ?></span>
                                </td>
                                <td>
                                    <?php
                                        // Cek apakah ada retur untuk pembelian ini
                                        $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $pembelian->id)->exists();
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasRetur): ?>
                                        <span class="badge bg-danger">Ada Retur</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Tidak Ada Retur</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('transaksi.pembelian.edit', $pembelian->id)); ?>" class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo e(route('transaksi.pembelian.show', $pembelian->id)); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('transaksi.retur-pembelian.create', ['pembelian_id' => $pembelian->id])); ?>" class="btn btn-outline-info">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                        <a href="<?php echo e(route('akuntansi.jurnal-umum', ['ref_type' => 'purchase', 'ref_id' => $pembelian->id])); ?>" class="btn btn-outline-secondary">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <form action="<?php echo e(route('transaksi.pembelian.destroy', $pembelian->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pembelian</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/transaksi/pembelian/index.blade.php ENDPATH**/ ?>