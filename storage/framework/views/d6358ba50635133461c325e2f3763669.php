

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">Ajukan Retur</h2>
        <a href="<?php echo e(route('pelanggan.returns.index')); ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo e(session('error')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></ul></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('pelanggan.returns.create')); ?>" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label text-dark">Pilih Pesanan</label>
                    <select name="order_id" class="form-select" required>
                        <option value="">-- Pilih Pesanan --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($o->id); ?>" <?php echo e(request('order_id') == $o->id ? 'selected' : ''); ?>>
                                #<?php echo e($o->nomor_order); ?> - Rp <?php echo e(number_format($o->total_amount, 0, ',', '.')); ?> (<?php echo e(ucfirst($o->status)); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-info w-100">Muat Item</button>
                </div>
            </form>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order): ?>
    <form action="<?php echo e(route('pelanggan.returns.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="order_id" value="<?php echo e($order->id); ?>">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-dark"><i class="bi bi-box-seam"></i> Item Pesanan yang Bisa Diretur</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty Dipesan</th>
                                <th width="160">Qty Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($it->produk->nama_produk ?? 'Produk'); ?></td>
                                <td>Rp <?php echo e(number_format($it->harga, 0, ',', '.')); ?></td>
                                <td><?php echo e($it->qty); ?></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="hidden" name="items[<?php echo e($loop->index); ?>][order_item_id]" value="<?php echo e($it->id); ?>">
                                        <input type="number" name="items[<?php echo e($loop->index); ?>][qty]" value="0" min="0" max="<?php echo e($it->qty); ?>" class="form-control">
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><h5 class="mb-0 text-dark"><i class="bi bi-clipboard-check"></i> Detail Pengajuan</h5></div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label text-dark">Kompensasi</label>
                    <select name="tipe_kompensasi" class="form-select" required>
                        <option value="barang">Tukar Barang</option>
                        <option value="uang">Refund Uang</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label text-dark">Alasan</label>
                    <textarea name="alasan" rows="3" class="form-control" placeholder="Tuliskan alasan retur (opsional)"></textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success px-4"><i class="bi bi-send"></i> Ajukan Retur</button>
        </div>
    </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.pelanggan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/pelanggan/returns/create.blade.php ENDPATH**/ ?>