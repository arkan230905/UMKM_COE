<?php $__env->startSection('title', 'Pelunasan Utang'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-hand-holding-usd me-2"></i>Pelunasan Utang
        </h2>
        <a href="<?php echo e(route('transaksi.pelunasan-utang.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Pelunasan
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
            <form method="GET" action="<?php echo e(route('transaksi.pelunasan-utang.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode Transaksi</label>
                        <input type="text" name="kode_transaksi" class="form-control" 
                               value="<?php echo e(request('kode_transaksi')); ?>" placeholder="Cari kode transaksi...">
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
                            <a href="<?php echo e(route('transaksi.pelunasan-utang.index')); ?>" class="btn btn-outline-secondary">
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
                <i class="fas fa-list me-2"></i>Riwayat Pelunasan Utang
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['kode_transaksi', 'tanggal_mulai', 'tanggal_selesai', 'vendor_id', 'status'])): ?>
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
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pembelian</th>
                            <th>Vendor</th>
                            <th>COA Pelunasan</th>
                            <th class="text-end">Jumlah</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pelunasanUtang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($key + 1); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-receipt text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($item->kode_transaksi); ?></div>
                                            <small class="text-muted">ID: <?php echo e($item->id); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e(\Carbon\Carbon::parse($item->tanggal)->format('d-m-Y')); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-shopping-cart text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->pembelian && $item->pembelian->details->count() > 0): ?>
                                                    <?php
                                                        $details = $item->pembelian->details;
                                                        $items = $details->map(function($detail) {
                                                            return $detail->nama_bahan;
                                                        })->filter()->toArray();
                                                        $count = count($items);
                                                        $noTransaksi = $item->pembelian->nomor_pembelian;
                                                    ?>
                                                    
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count == 1): ?>
                                                        <?php echo e($items[0]); ?> (<?php echo e($noTransaksi); ?>)
                                                    <?php elseif($count == 2): ?>
                                                        <?php echo e($items[0]); ?>, <?php echo e($items[1]); ?> (<?php echo e($noTransaksi); ?>)
                                                    <?php else: ?>
                                                        <?php echo e($items[0]); ?>, <?php echo e($items[1]); ?> +<?php echo e($count - 2); ?> item (<?php echo e($noTransaksi); ?>)
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php else: ?>
                                                    <?php echo e($item->pembelian->nomor_pembelian ?? '-'); ?>

                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <small class="text-muted">Pembelian</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-truck text-info"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($item->pembelian->vendor->nama_vendor ?? '-'); ?></div>
                                            <small class="text-muted">Vendor</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->coaPelunasan): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                                <i class="fas fa-chart-line text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo e($item->coaPelunasan->kode_akun); ?></div>
                                                <small class="text-muted"><?php echo e($item->coaPelunasan->nama_akun); ?></small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold">Rp <?php echo e(number_format($item->jumlah, 0, ',', '.')); ?></td>
                                <td><?php echo $item->status_badge; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('transaksi.pelunasan-utang.show', $item->id)); ?>" class="btn btn-outline-primary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('akuntansi.jurnal-umum')); ?>?ref_type=debt_payment&ref_id=<?php echo e($item->id); ?>" class="btn btn-outline-success" title="Lihat Jurnal">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="<?php echo e(route('transaksi.pelunasan-utang.print', $item->id)); ?>" class="btn btn-outline-warning" target="_blank" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <form action="<?php echo e(route('transaksi.pelunasan-utang.destroy', $item->id)); ?>" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data pelunasan utang</p>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/pelunasan-utang/index.blade.php ENDPATH**/ ?>