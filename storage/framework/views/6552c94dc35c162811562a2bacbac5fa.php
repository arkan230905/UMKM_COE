<?php $__env->startPush('styles'); ?>
<style>
.product-card {
    border: none !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    background: white !important;
    position: relative !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
}

.product-card:hover {
    transform: translateY(-8px) scale(1.02) !important;
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15) !important;
}

.btn-favorite {
    background: rgba(255, 255, 255, 0.95) !important;
    border: none !important;
    width: 40px !important;
    height: 40px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    backdrop-filter: blur(15px) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
}

.btn-favorite:hover {
    background: white !important;
    transform: scale(1.15) rotate(5deg) !important;
    box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3) !important;
}

.card-img-top {
    height: 160px !important;
    object-fit: cover !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.product-card:hover .card-img-top {
    transform: scale(1.08) !important;
    filter: brightness(1.05) !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.9; }
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(".umkm-slide");
    let i = 0;

    function show(n) {
        slides.forEach(s => s.classList.remove("active"));
        slides[n].classList.add("active");
    }

    document.querySelector(".prev")?.addEventListener("click", () => {
        i = (i - 1 + slides.length) % slides.length;
        show(i);
    });

    document.querySelector(".next")?.addEventListener("click", () => {
        i = (i + 1) % slides.length;
        show(i);
    });

    show(0);
});
</script>

<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<!-- HERO AESTHETIC BARU -->
<div class="hero-umkm-wrapper mb-4">
    <div class="hero-umkm">

    <!-- Decorative Bubbles -->
    <div class="umkm-bubble bubble-1"></div>
    <div class="umkm-bubble bubble-2"></div>

    <div class="container py-4">
        <div class="row align-items-center">

            <!-- TEKS KIRI -->
            <div class="col-lg-7">
                <span class="umkm-pill">UMKM COE ✨</span>

                <h1 class="umkm-title">
                    Belanja asik, harga <span>ramah dompet</span>
                </h1>

                <p class="umkm-subtext">
                    Temukan produk UMKM favoritmu dengan tampilan baru yang lebih cantik dan nyaman dilihat 💜
                </p>

                <!-- Stats -->
                <div class="row g-2 mt-3">
                    <div class="col-4">
                        <div class="umkm-stat">
                            <strong><?php echo e($produks->total()); ?></strong>
                            <span>Produk</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="umkm-stat">
                            <strong>1000+</strong>
                            <span>Terjual</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="umkm-stat">
                            <strong>⭐</strong>
                            <span>Best Seller</span>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <form action="<?php echo e(route('pelanggan.dashboard')); ?>" method="GET" class="umkm-search mt-3">
                    <input type="text" name="q" value="<?php echo e($search); ?>" placeholder="Cari produk..." />
                    <button type="submit">Cari</button>
                </form>

                <!-- Flash Sale -->
                <div class="flash-row mt-3">
                    <a href="#best-seller" class="flash-btn">⚡ Flash Sale</a>
                    <span class="flash-hint">Diskon manis setiap hari 🌈</span>
                </div>

            </div>

            <!-- SLIDER KANAN -->
            <div class="col-lg-5">
                <div class="umkm-slider-box">

                    <div class="umkm-slider" id="umkmSlider">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bestSellers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="umkm-slide <?php echo e($index == 0 ? 'active' : ''); ?>">
                            
                            <div class="slide-tag">Best Seller</div>

                            <div class="slide-photo">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($b->foto): ?>
                                <img src="<?php echo e(asset('storage/'.$b->foto)); ?>">
                                <?php else: ?>
                                <div class="slide-photo-ph">🧺</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="slide-info">
                                <h4><?php echo e($b->nama_produk); ?></h4>
                                <p class="slide-price">Rp <?php echo e(number_format($b->harga_jual,0,',','.')); ?></p>
                                <p class="slide-meta">
                                    ⭐ <?php echo e(number_format($b->avg_rating, 1)); ?> • <?php echo e($b->total_terjual ?? 0); ?> terjual
                                </p>
                            </div>

                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bestSellers->count() > 1): ?>
                        <button class="slide-nav prev">‹</button>
                        <button class="slide-nav next">›</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container">

    <h2 class="produk-heading mb-4 mt-4">Produk</h2>

    <div class="row">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $produks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $produk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm position-relative">
                <form action="<?php echo e(route('pelanggan.favorites.toggle')); ?>" method="POST" class="position-absolute" style="top:8px; right:8px; z-index:2;">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="produk_id" value="<?php echo e($produk->id); ?>">
                    <?php
                        $isFav = in_array($produk->id, $favoriteIds ?? []);
                    ?>
                    <button type="submit" class="btn btn-light rounded-circle" style="width: 45px; height: 45px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFav): ?>
                        <i class="bi bi-heart-fill text-danger" style="font-size: 1.2rem;"></i>
                        <?php else: ?>
                        <i class="bi bi-heart text-danger" style="font-size: 1.2rem;"></i>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                </form>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($produk->foto): ?>
                <img src="<?php echo e(asset('storage/' . $produk->foto)); ?>" class="card-img-top" alt="<?php echo e($produk->nama_produk); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="bg-secondary text-white text-center d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title text-dark"><?php echo e($produk->nama_produk); ?></h5>
                    <p class="card-text text-muted small"><?php echo e(Str::limit($produk->deskripsi ?? 'Produk berkualitas', 80)); ?></p>
                    <p class="fw-bold text-primary fs-5">Rp <?php echo e(number_format($produk->harga_jual, 0, ',', '.')); ?></p>
                    <?php
                        $ratingPd = $produk->avg_rating ?? 0;
                        $countRatingPd = $produk->rating_count ?? 0;
                        $filledPd = floor($ratingPd);
                        $halfPd = ($ratingPd - $filledPd) >= 0.5 ? 1 : 0;
                        $emptyPd = 5 - $filledPd - $halfPd;
                    ?>
                    <?php
                        $filledPd = $filledPd ?? 0;
                        $halfPd = $halfPd ?? 0;
                        $emptyPd = $emptyPd ?? (5 - $filledPd - $halfPd);
                    ?>
                    <div class="text-warning small mb-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i=0;$i<$filledPd;$i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i=0;$i<$halfPd;$i++): ?><i class="bi bi-star-half"></i><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i=0;$i<$emptyPd;$i++): ?><i class="bi bi-star"></i><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="text-muted"><?php echo e(number_format($ratingPd,1)); ?><?php echo e($countRatingPd ? ' ('.$countRatingPd.')' : ''); ?></span>
                    </div>
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
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada produk tersedia saat ini.
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="d-flex justify-content-center">
        <?php echo e($produks->links()); ?>

    </div>

    <div class="mt-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="mb-2 text-dark">Contact Us</h5>
                <p class="text-muted mb-3">Ada pertanyaan tentang produk atau pesanan? Hubungi kami melalui WhatsApp.</p>
                <?php
                    $wa = $whatsappNumber ?? '';
                    $wa = preg_replace('/[^0-9]/', '', $wa);
                    $waLink = $wa ? 'https://wa.me/'.$wa : null;
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($waLink): ?>
                <a href="<?php echo e($waLink); ?>" target="_blank" class="btn btn-success">
                    <i class="bi bi-whatsapp"></i> Chat via WhatsApp
                </a>
                <?php else: ?>
                <div class="alert alert-warning p-2 d-inline-block">Nomor WhatsApp belum dikonfigurasi.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<style>
/* ==== HERO WRAPPER ==== */
.hero-umkm-wrapper {
    max-width: 100%; /* kamu bisa kecilkan jika mau */
    margin: 0 auto; /* bikin hero di tengah */
    padding: 0 1.5rem; /* jarak kiri kanan */
}

/* HERO FLOATING CARD */
.hero-umkm {
    background: #ffffff;
    border-radius: 25px;
    padding: 40px 35px;
    box-shadow:
        0 12px 28px rgba(0,0,0,0.06),
        0 0 40px rgba(180,200,255,0.28);

    border: 1px solid #e9edff;
    position: relative;
}

.hero-umkm:hover {
    transform: translateY(-3px);
    transition: 0.3s ease;
    box-shadow:
        0 16px 34px rgba(0,0,0,0.08),
        0 0 55px rgba(160,185,255,0.35);
}

/* ==== DECORATION HALUS ==== */
.umkm-bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(180, 195, 255, 0.25);
    filter: blur(12px);
    z-index: 0;
}

.bubble-1 {
    width: 130px;
    height: 130px;
    top: -30px;
    right: 80px;
}
.bubble-2 {
    width: 90px;
    height: 90px;
    bottom: -20px;
    left: 120px;
}

/* ==== TEXT ==== */
.umkm-pill {
    background: #eef1ff;
    padding: 5px 14px;
    border-radius: 20px;
    color: #3f4a6b;
    font-weight: 600;
    font-size: 0.85rem;
    position: relative;
    z-index: 1;
}

.umkm-title {
    font-size: 2.1rem;
    font-weight: 800;
    color: #2a2f45;
    margin-top: 10px;
    position: relative;
    z-index: 1;
}
.umkm-title span {
    color: #ffdd8b;
}

.umkm-subtext {
    font-size: 0.95rem;
    color: #657088;
    margin-bottom: 15px;
    position: relative;
    z-index: 1;
}

/* ==== STATS ==== */
.umkm-stat {
    background: #f7f8ff;
    border: 1px solid #e4e7ff;
    padding: 12px;
    border-radius: 14px;
    text-align: center;
}
.umkm-stat strong {
    font-size: 1.15rem;
    color: #2d3550;
}
.umkm-stat span {
    font-size: 0.8rem;
    color: #5d6784;
}

/* ==== SEARCH BAR ==== */
.umkm-search {
    max-width: 380px;
    display: flex;
    align-items: center;
    background: #f6f7ff;
    border: 1px solid #dfe4ff;
    padding: 6px;
    border-radius: 40px;
    position: relative;
    z-index: 1;
}
.umkm-search input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 10px 10px;
    font-size: 0.95rem;
    color: #333;
}
.umkm-search input::placeholder {
    color: #9aa3c5;
}
.umkm-search button {
    background: #ffd86f;
    border: none;
    border-radius: 30px;
    padding: 8px 18px;
    font-weight: 600;
    color: #4a3d15;
    cursor: pointer;
}

/* ==== FLASH SALE ==== */
.flash-btn {
    background: #ff5c78;
    padding: 6px 14px;
    border-radius: 40px;
    color: white;
    text-decoration: none;
    font-weight: 600;
}
.flash-hint {
    margin-left: 10px;
    color: #5f6881;
}

/* === SLIDER BOX === */
.umkm-slider-box {
    background: #eef3ff; /* biru muda */
    border: 1px solid #d5ddff;
    border-radius: 22px;
    padding: 18px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    position: relative;
}

/* === SLIDE CARD === */
.umkm-slide {
    display: none;
    background: white;
    border-radius: 18px;
    padding: 14px;
    border: 1px solid #e5e8ff;
    align-items: center;
    gap: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}

.umkm-slide.active {
    display: flex;
}

/* === FIXED PHOTO BOX === */
.slide-photo {
    width: 42%;
    height: 165px;
    border-radius: 14px;
    overflow: hidden;
    background: #dfe6ff;
}
.slide-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* === INFO AREA === */
.slide-info h4 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #2e3245;
    margin-bottom: 4px;
}

.slide-price {
    font-weight: 700;
    color: #ff4b6f;
    margin-bottom: 6px;
}

.slide-meta {
    color: #636d8b;
    font-size: 0.85rem;
}

/* === NEW BEST SELLER BADGE === */
.slide-tag {
    position: absolute;
    top: 12px;
    left: 12px;
    
    background: #ffd5e8;   /* pastel pink muted */
    color: #b1365a;        /* pink gelap elegan */
    
    padding: 4px 12px;
    border-radius: 10px;
    
    font-size: 0.72rem;
    font-weight: 600;
    
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    border: 1px solid #f7b2d0;
}

/* === SLIDER NAV BUTTONS === */
.slide-nav {
    width: 34px;
    height: 34px;
    background: white;
    border: 1px solid #d8ddff;
    border-radius: 50%;
    box-shadow: 0 3px 10px rgba(0,0,0,0.07);
    cursor: pointer;
    font-size: 1.3rem;
}

.prev { left: -14px; }
.next { right: -14px; }

/* ==== PRODUK HEADING STYLING ==== */
.produk-heading {
    font-size: 2rem;
    font-weight: 800;
    color: #2a2f45;
    background: linear-gradient(135deg, #2a2f45, #667eea);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    position: relative;
    padding-bottom: 10px;
}

.produk-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 2px;
}

</style>


<?php echo $__env->make('layouts.pelanggan', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/pelanggan/dashboard.blade.php ENDPATH**/ ?>