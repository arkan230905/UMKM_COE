<?php $__env->startSection('title', 'Kelola Proses Produksi'); ?>

<?php $__env->startSection('head'); ?>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-tasks me-2"></i>Kelola Proses Produksi
        </h2>
        <a href="<?php echo e(route('transaksi.produksi.index')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Info Produksi -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Informasi Produksi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="fw-bold">Produk:</label>
                    <p><?php echo e($produksi->produk->nama_produk); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Tanggal:</label>
                    <p><?php echo e(\Carbon\Carbon::parse($produksi->tanggal)->format('d/m/Y')); ?></p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Qty Produksi:</label>
                    <p><?php echo e(number_format($produksi->qty_produksi, 0, ',', '.')); ?> pcs</p>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold">Status:</label>
                    <p><?php echo $produksi->status_badge; ?></p>
                </div>
            </div>
            
            <!-- Current Time and Progress Bar -->
            <div class="mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <label class="fw-bold">Progress Produksi:</label>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?php echo e($produksi->progress_percentage); ?>%"
                                 aria-valuenow="<?php echo e($produksi->progress_percentage); ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo e($produksi->actual_proses_selesai); ?>/<?php echo e($produksi->total_proses); ?> Proses (<?php echo e($produksi->progress_percentage); ?>%)
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Waktu Saat Ini:</label>
                        <p class="mb-0">
                            <span id="current-time" class="badge bg-info fs-6"><?php echo e(now()->format('d/m/Y H:i:s')); ?></span>
                        </p>
                        <small class="text-muted">Timezone: Asia/Jakarta</small>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Proses -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Tahapan Proses Produksi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px">Urutan</th>
                            <th>Nama Proses</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th class="text-center" style="width: 200px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $produksi->proses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="<?php echo e($proses->status === 'sedang_dikerjakan' ? 'table-primary' : ''); ?>">
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?php echo e($proses->urutan); ?></span>
                                </td>
                                <td>
                                    <strong><?php echo e($proses->nama_proses); ?></strong>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->catatan): ?>
                                        <br><small class="text-muted"><?php echo e($proses->catatan); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php echo $proses->status_badge; ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->waktu_mulai): ?>
                                        <small>Mulai: <?php echo e($proses->waktu_mulai->format('d/m/Y H:i:s')); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->waktu_selesai): ?>
                                        <br><small>Selesai: <?php echo e($proses->waktu_selesai->format('d/m/Y H:i:s')); ?></small>
                                        <br><small class="text-success">Durasi: <?php echo e($proses->formatted_duration); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proses->status === 'pending'): ?>
                                        <?php
                                            // Check if any other process is currently running
                                            $hasRunningProcess = $produksi->proses->where('status', 'sedang_dikerjakan')->count() > 0;
                                        ?>
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasRunningProcess): ?>
                                            <form action="<?php echo e(route('transaksi.produksi.proses.mulai', $proses->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-play"></i> Mulai
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="fas fa-hourglass-start"></i> Menunggu
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($proses->status === 'sedang_dikerjakan'): ?>
                                        <form action="<?php echo e(route('transaksi.produksi.proses.selesai', $proses->id)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Selesaikan
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Selesai
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh page every 30 seconds to ensure latest data
setTimeout(function() {
    location.reload();
}, 30000);

// Show current time for reference (server already corrected)
function updateCurrentTime() {
    const now = new Date();
    // No additional correction needed - server already provides correct time
    
    const timeString = now.toLocaleString('id-ID', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    // Update any element with id 'current-time' if exists
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

// Update time every second
setInterval(updateCurrentTime, 1000);
updateCurrentTime();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/transaksi/produksi/proses.blade.php ENDPATH**/ ?>