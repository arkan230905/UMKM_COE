<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calendar-check me-2"></i>Data Presensi
        </h2>
        <a href="<?php echo e(route('transaksi.presensi.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Presensi
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filter Presensi
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('transaksi.presensi.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nama Pegawai</label>
                        <input type="text" name="nama_pegawai" class="form-control" 
                               value="<?php echo e(request('nama_pegawai')); ?>" placeholder="Cari nama pegawai...">
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
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Hadir" <?php echo e(request('status') == 'Hadir' ? 'selected' : ''); ?>>Hadir</option>
                            <option value="Sakit" <?php echo e(request('status') == 'Sakit' ? 'selected' : ''); ?>>Sakit</option>
                            <option value="Izin" <?php echo e(request('status') == 'Izin' ? 'selected' : ''); ?>>Izin</option>
                            <option value="Cuti" <?php echo e(request('status') == 'Cuti' ? 'selected' : ''); ?>>Cuti</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="<?php echo e(route('transaksi.presensi.index')); ?>" class="btn btn-outline-secondary">
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
                <i class="fas fa-list me-2"></i>Riwayat Presensi
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->hasAny(['nama_pegawai', 'tanggal_mulai', 'tanggal_selesai', 'status'])): ?>
                    <small class="text-muted">(Filter Aktif)</small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Pegawai</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th class="text-center">Total Jam</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $presensi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $presensi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($key + 1); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($presensi->pegawai->nama_display ?? $presensi->pegawai->nama ?? 'Tidak Diketahui'); ?></div>
                                            <small class="text-muted">NIP: <?php echo e($presensi->pegawai->kode_pegawai ?? '-'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e(\Carbon\Carbon::parse($presensi->tgl_presensi)->isoFormat('dddd, D MMMM YYYY')); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($presensi->status === 'Hadir'): ?>
                                        <?php echo e(\Carbon\Carbon::parse($presensi->jam_masuk)->format('H:i')); ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($presensi->jam_keluar): ?>
                                        <?php echo e(\Carbon\Carbon::parse($presensi->jam_keluar)->format('H:i')); ?>

                                    <?php else: ?>
                                        -
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($presensi->status):
                                        case ('Hadir'): ?>
                                            <span class="badge bg-success">Hadir</span>
                                            <?php break; ?>
                                        <?php case ('Sakit'): ?>
                                            <span class="badge bg-warning">Sakit</span>
                                            <?php break; ?>
                                        <?php case ('Izin'): ?>
                                            <span class="badge bg-info">Izin</span>
                                            <?php break; ?>
                                        <?php case ('Cuti'): ?>
                                            <span class="badge bg-primary">Cuti</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge bg-secondary"><?php echo e($presensi->status); ?></span>
                                    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-center fw-semibold"><?php echo e($presensi->jumlah_jam ?? 0); ?> jam</td>
                                <td><?php echo e($presensi->keterangan ?? '-'); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('transaksi.presensi.edit', $presensi->id)); ?>" class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('transaksi.presensi.destroy', $presensi->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data presensi ini?')">
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
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada data presensi</p>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/presensi/index.blade.php ENDPATH**/ ?>