<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Transaksi
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('transaksi.pembelian.index')); ?>">
            <input type="hidden" name="tab" value="pembelian">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">No Transaksi</label>
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
                <div class="col-md-3">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select">
                        <option value="">Semua Status Pembayaran</option>
                        <option value="lunas" <?php echo e(request('status_pembayaran') == 'lunas' ? 'selected' : ''); ?>>Lunas</option>
                        <option value="belum_lunas" <?php echo e(request('status_pembayaran') == 'belum_lunas' ? 'selected' : ''); ?>>Belum Lunas</option>
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

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-list me-2"></i>
                <span>Riwayat Pembelian</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['nomor_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'payment_method', 'status', 'status_pembayaran'])): ?>
                    <small class="text-muted ms-3">(Filter Aktif)</small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 1200px;">
                <thead class="table-light">
                    <tr>

                        <th class="text-center" style="width: 50px">No</th>
                        <th class="nowrap">No. Transaksi</th>
                        <th class="nowrap">No. Faktur</th>
                        <th class="nowrap">Bukti Faktur</th>
                        <th class="nowrap">Tanggal</th>
                        <th class="nowrap">Vendor</th>
                        <th class="nowrap">Item</th>
                        <th class="nowrap">Satuan Pembelian</th>
                        <th class="nowrap">Pembayaran</th>
                        <th class="nowrap">Status Pembayaran</th>
                        <th class="nowrap">Total Harga</th>
                        <th class="nowrap">Status Retur</th>
                        <th class="text-center" style="width: 180px">Aksi</th>
</tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pembelians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $pembelian): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e($key + 1); ?></td>
                            <td class="text-center nowrap" style="color: #000; font-weight: bold;"><?php echo e($pembelian->nomor_pembelian ?? 'KOSONG'); ?></td>
                            <td class="text-center nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembelian->nomor_faktur): ?>
                                    <?php echo e($pembelian->nomor_faktur); ?>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>

                            <td class="nowrap text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembelian->bukti_faktur): ?>
                                    <?php
                                        // Extract ID and filename from bukti_faktur path
                                        // Format: bukti_faktur/{id}/{filename}
                                        $parts = explode('/', $pembelian->bukti_faktur);
                                        $userId = $parts[1] ?? '';
                                        $filename = $parts[2] ?? '';
                                    ?>
                                    <a href="<?php echo e(url('/storage/' . $pembelian->bukti_faktur)); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Bukti Faktur">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="nowrap"><?php echo e($pembelian->tanggal->format('d-m-Y')); ?></td>
                            <td class="nowrap">
                                <div class="d-flex align-items-center">
<div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-store text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo e($pembelian->vendor->nama_vendor ?? '-'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembelian->details && $pembelian->details->count() > 0): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pembelian->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="mb-1">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->bahan_baku_id && $detail->bahanBaku): ?>
                                                BB - <?php echo e($detail->bahanBaku->nama_bahan); ?>

                                            <?php elseif($detail->bahan_pendukung_id && $detail->bahanPendukung): ?>
                                                BP - <?php echo e($detail->bahanPendukung->nama_bahan); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Item tidak diketahui</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pembelian->details && $pembelian->details->count() > 0): ?>
                                    <small>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pembelian->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div>
                                            <?php echo e($detail->satuan_nama); ?>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center nowrap">
                                <?php
                                    $paymentMethod = $pembelian->payment_method ?? 'cash';
                                    if ($paymentMethod === 'credit') {
                                        $paymentText = 'Kredit';
                                    } elseif ($paymentMethod === 'transfer') {
                                        $paymentText = 'Transfer';
                                    } else {
                                        $paymentText = 'Tunai';
                                    }
                                ?>
                                <?php echo e($paymentText); ?>

                            </td>
                            <td class="text-center nowrap">
                                <?php
                                    $statusPembayaran = $pembelian->status_pembayaran;
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusPembayaran === 'Lunas'): ?>
                                    <span class="text-success fw-semibold">Lunas</span>
                                <?php else: ?>
                                    <span class="text-warning fw-semibold">Belum Lunas</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center nowrap fw-semibold">
                                Rp <?php echo e(number_format($pembelian->total_harga ?? 0, 0, ',', '.')); ?>

                            </td>
                            <td class="text-center nowrap">
                                <?php
                                    // Cek apakah ada retur untuk pembelian ini
                                    $hasRetur = \App\Models\PurchaseReturn::where('pembelian_id', $pembelian->id)->exists();
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasRetur): ?>
                                    Ada Retur
                                <?php else: ?>
                                    Tidak Ada Retur
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-grid" style="grid-template-columns: repeat(2, 1fr); gap: 5px;">
                                    <!-- Row 1: Detail | Edit -->
                                    <a href="<?php echo e(route('transaksi.pembelian.show', $pembelian->id)); ?>" class="btn btn-sm btn-outline-success w-100" title="Detail Transaksi">
                                        Detail
                                    </a>
                                    <a href="<?php echo e(route('transaksi.pembelian.edit', $pembelian->id)); ?>" class="btn btn-sm btn-outline-warning w-100" title="Edit Transaksi">
                                        Edit
                                    </a>
                                    
                                    <!-- Row 2: Jurnal | Retur -->
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary w-100" 
                                            title="Lihat Jurnal"
                                            onclick="loadJournal(<?php echo e($pembelian->id); ?>, '<?php echo e($pembelian->nomor_pembelian); ?>')"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#journalModal">
                                        Jurnal
                                    </button>
                                    <a href="<?php echo e(route('transaksi.retur-pembelian.create', ['pembelian_id' => $pembelian->id])); ?>" class="btn btn-sm btn-outline-info w-100" title="Proses Retur">
                                        Retur
                                    </a>
                                    
                                    <!-- Row 3: Cetak -->
                                    <a href="<?php echo e(route('transaksi.pembelian.preview-faktur', $pembelian->id)); ?>" class="btn btn-sm btn-outline-info w-100" title="Cetak Faktur" target="_blank">
                                        Cetak
                                    </a>
                                    
                                    <!-- Row 4: Hapus -->
                                    <form action="<?php echo e(route('transaksi.pembelian.destroy', $pembelian->id)); ?>" method="POST" class="d-inline w-100" onsubmit="return confirm('Yakin ingin hapus?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Hapus Transaksi">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="13" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data pembelian</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/pembelian/partials/pembelian-content.blade.php ENDPATH**/ ?>