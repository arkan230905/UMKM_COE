<?php $__env->startSection('content'); ?>
<div class="container-fluid py-5" style="background-color: #1e1e2f; min-height: 100vh;">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6">
            <div class="card shadow-lg" style="background-color: #2c2c3e; border: none; border-radius: 15px;">
                <div class="card-body text-center p-5">
                    <!-- Error Icon -->
                    <div class="mb-4">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px; background: linear-gradient(135deg, #f5365c 0%, #f56036 100%);">
                            <i class="bi bi-shield-lock fs-1 text-white"></i>
                        </div>
                    </div>

                    <!-- Error Title -->
                    <h1 class="text-white mb-3" style="font-size: 3rem; font-weight: 700;">403</h1>
                    <h4 class="text-white mb-4">Akses Ditolak</h4>

                    <!-- Error Message -->
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger text-danger mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
                    </div>

                    <!-- Role Information -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php
                            $user = auth()->user();
                            $userRole = $user->role ?? 'tidak ada';
                            $requiredRoles = request()->route() ? request()->route()->action['middleware'] ?? [] : [];
                            
                            // Extract roles from middleware if available
                            $rolesList = [];
                            foreach ($requiredRoles as $middleware) {
                                if (is_string($middleware) && str_starts_with($middleware, 'role:')) {
                                    $rolesList = explode(',', str_replace('role:', '', $middleware));
                                    break;
                                }
                            }
                            
                            // Translate roles to Indonesian
                            $roleTranslations = [
                                'admin' => 'Administrator',
                                'owner' => 'Pemilik',
                                'pelanggan' => 'Pelanggan',
                                'pegawai_pembelian' => 'Pegawai Pembelian',
                            ];
                        ?>

                        <div class="card mb-4" style="background-color: #1e1e2f; border: 1px solid rgba(255,255,255,0.1);">
                            <div class="card-body">
                                <div class="row text-start">
                                    <div class="col-6">
                                        <small class="text-white-50 d-block mb-1">Role Anda:</small>
                                        <span class="badge bg-info bg-opacity-25 text-info px-3 py-2">
                                            <i class="bi bi-person-badge me-1"></i>
                                            <?php echo e($roleTranslations[$userRole] ?? ucfirst($userRole)); ?>

                                        </span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($rolesList)): ?>
                                    <div class="col-6">
                                        <small class="text-white-50 d-block mb-1">Role yang Dibutuhkan:</small>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rolesList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge bg-success bg-opacity-25 text-success px-3 py-2 me-1">
                                                <i class="bi bi-check-circle me-1"></i>
                                                <?php echo e($roleTranslations[trim($role)] ?? ucfirst(trim($role))); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Options -->
                        <div class="d-flex flex-column gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->role === 'pelanggan'): ?>
                                <a href="<?php echo e(route('pelanggan.dashboard')); ?>" class="btn btn-primary btn-lg">
                                    <i class="bi bi-house-door me-2"></i>
                                    Kembali ke Dashboard Pelanggan
                                </a>
                            <?php elseif($user->role === 'pegawai_pembelian'): ?>
                                <a href="<?php echo e(route('pegawai-pembelian.dashboard')); ?>" class="btn btn-primary btn-lg">
                                    <i class="bi bi-house-door me-2"></i>
                                    Kembali ke Dashboard Pembelian
                                </a>
                            <?php elseif(in_array($user->role, ['admin', 'owner'])): ?>
                                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary btn-lg">
                                    <i class="bi bi-house-door me-2"></i>
                                    Kembali ke Dashboard
                                </a>
                            <?php else: ?>
                                <a href="<?php echo e(url('/')); ?>" class="btn btn-primary btn-lg">
                                    <i class="bi bi-house-door me-2"></i>
                                    Kembali ke Beranda
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <a href="javascript:history.back()" class="btn btn-outline-light">
                                <i class="bi bi-arrow-left me-2"></i>
                                Kembali ke Halaman Sebelumnya
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Not Authenticated -->
                        <div class="alert alert-warning bg-warning bg-opacity-10 border-warning text-warning mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            Anda perlu login untuk mengakses halaman ini.
                        </div>

                        <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Login
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Help Text -->
                    <div class="mt-4">
                        <small class="text-white-50">
                            Jika Anda merasa ini adalah kesalahan, silakan hubungi administrator sistem.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.badge {
    font-weight: 500;
    font-size: 0.875rem;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/errors/403.blade.php ENDPATH**/ ?>