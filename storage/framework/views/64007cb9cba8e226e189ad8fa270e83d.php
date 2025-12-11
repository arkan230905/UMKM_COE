

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark fw-bold mb-0" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; letter-spacing: 0.5px;">
    <i class="bi bi-heart-fill me-2 text-danger"></i>Favorit Saya
</h2>
    </div>

    <div class="row">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $favorites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fav): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php ($produk = $fav->produk); ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm position-relative">
                <form action="<?php echo e(route('pelanggan.favorites.toggle')); ?>" method="POST" class="position-absolute" style="top:8px; right:8px; z-index:2;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="produk_id" value="<?php echo e($produk->id); ?>">
                    <button type="submit" class="btn btn-light btn-sm rounded-circle">
                        <i class="bi bi-heart-fill text-danger"></i>
                    </button>
                </form>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk && $produk->foto): ?>
                <img src="<?php echo e(asset('storage/' . $produk->foto)); ?>" class="card-img-top" alt="<?php echo e($produk->nama_produk); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="bg-secondary text-white text-center d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title text-dark"><?php echo e($produk->nama_produk); ?></h5>
                    <p class="fw-bold text-primary fs-5">Rp <?php echo e(number_format($produk->harga_jual, 0, ',', '.')); ?></p>
                    <p class="small text-white">
                        <span class="badge bg-light text-secondary me-1">Stok :</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->stok > 10): ?>
                        <span class="badge bg-success"><?php echo e(number_format($produk->stok, 0, ',', '.')); ?></span>
                        <?php elseif($produk->stok > 0): ?>
                        <span class="badge bg-warning"><?php echo e(number_format($produk->stok, 0, ',', '.')); ?></span>
                        <?php else: ?>
                        <span class="badge bg-danger">Habis</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->stok > 0): ?>
                    <form action="<?php echo e(route('pelanggan.cart.store')); ?>" method="POST" class="d-grid gap-2">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="produk_id" value="<?php echo e($produk->id); ?>">
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary" type="button" onclick="const i=this.nextElementSibling; i.stepDown();">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="qty" value="1" min="1" max="<?php echo e($produk->stok); ?>" class="form-control text-center text-dark">
                            <button class="btn btn-outline-secondary" type="button" onclick="const i=this.previousElementSibling; const m=parseInt(i.max)||1; i.value=Math.min(parseInt(i.value||1)+1,m);">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>Stok Habis</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="alert alert-info">Belum ada produk favorit.</div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.pelanggan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/pelanggan/favorites.blade.php ENDPATH**/ ?>