<?php $__env->startSection('content'); ?>
<!-- Hero Section -->
<div class="hero-section mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12">
                <div class="hero-content text-center">
                    <h1 class="display-4 fw-bold text-white mb-3">
                        <i class="bi bi-box-seam me-3"></i>Pesanan Saya
                    </h1>
                    <p class="lead text-white-50 mb-0">Lacak dan kelola semua pesananmu</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">

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

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 80px 0 60px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,133.3C960,128,1056,96,1152,90.7C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
}

/* Order Cards */
.order-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border-left: 4px solid #667eea;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.order-number {
    font-size: 1.1rem;
    font-weight: bold;
    color: #2c3e50;
}

.order-date {
    color: #7f8c8d;
    font-size: 0.9rem;
}

/* Status Badges */
.status-badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-shipped {
    background: #e2e3e5;
    color: #383d41;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Order Items */
.order-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f8f9fa;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item img {
    border-radius: 8px;
    margin-right: 15px;
}

.order-item-details {
    flex: 1;
}

.order-item-name {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 5px;
}

.order-item-price {
    color: #7f8c8d;
    font-size: 0.9rem;
}

/* Order Total */
.order-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid #667eea;
}

.order-total-label {
    font-weight: bold;
    color: #2c3e50;
}

.order-total-amount {
    font-size: 1.2rem;
    font-weight: bold;
    color: #e74c3c;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-success {
    background: #28a745;
    border: none;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: #218838;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.empty-state i {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-section {
        padding: 60px 0 40px;
    }
    
    .hero-section h1 {
        font-size: 2.5rem;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .order-item img {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>

<?php echo $__env->make('layouts.pelanggan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/pelanggan/orders.blade.php ENDPATH**/ ?>