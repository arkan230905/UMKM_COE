<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h2 class="mb-4 text-dark">Pesanan Saya</h2>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->isEmpty()): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #6c757d;"></i>
            <h4 class="mt-3 text-dark">Belum Ada Pesanan</h4>
            <p class="text-muted">Anda belum memiliki pesanan</p>
            <a href="<?php echo e(route('pelanggan.dashboard')); ?>" class="btn btn-primary">
                <i class="bi bi-shop"></i> Mulai Belanja
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-12 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <small class="text-muted">Nomor Pesanan</small>
                            <h6 class="mb-0 text-dark"><?php echo e($order->nomor_order); ?></h6>
                            <small class="text-muted"><?php echo e($order->created_at->format('d M Y')); ?></small>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Total</small>
                            <h6 class="mb-0 text-primary">Rp <?php echo e(number_format($order->total_amount, 0, ',', '.')); ?></h6>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Status Pesanan</small>
                            <div><?php echo $order->status_badge; ?></div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Pembayaran</small>
                            <div>
                                <span class="badge bg-<?php echo e($order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning')); ?>">
                                    <?php echo e(ucfirst($order->payment_status)); ?>

                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Metode</small>
                            <div class="text-dark small"><?php echo e($order->payment_method_label); ?></div>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="<?php echo e(route('pelanggan.orders.show', $order)); ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->payment_status === 'pending'): ?>
                            <a href="<?php echo e(route('pelanggan.orders.show', $order)); ?>" class="btn btn-sm btn-success mt-1">
                                <i class="bi bi-credit-card"></i> Bayar
                            </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'completed' || $order->payment_status === 'paid'): ?>
                            <a href="#" class="btn btn-sm btn-outline-warning mt-1" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo e($order->id); ?>">
                                <i class="bi bi-star"></i> Review
                            </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="d-flex justify-content-center">
        <?php echo e($orders->links()); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'completed' || $order->payment_status === 'paid'): ?>
<!-- Modal Review Order -->
<div class="modal fade" id="reviewModal<?php echo e($order->id); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('pelanggan.reviews.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="order_id" value="<?php echo e($order->id); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Beri Review: <?php echo e($order->nomor_order); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="btn-group" role="group">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" class="btn-check" name="rating" id="rating<?php echo e($i); ?>_<?php echo e($order->id); ?>" value="<?php echo e($i); ?>" required>
                            <label class="btn btn-outline-warning" for="rating<?php echo e($i); ?>_<?php echo e($order->id); ?>">
                                <i class="bi bi-star-fill"></i> <?php echo e($i); ?>

                            </label>
                            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="review_<?php echo e($order->id); ?>" class="form-label">Komentar (opsional)</label>
                        <textarea class="form-control" id="review_<?php echo e($order->id); ?>" name="comment" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.pelanggan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/pelanggan/orders.blade.php ENDPATH**/ ?>