<?php $__env->startSection('title', 'Daftar COA'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calculator me-2"></i>COA
        </h2>
        <div class="d-flex gap-2 align-items-end">
            <form method="get" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small mb-1">Pilih Periode</label>
                    <select name="period_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 180px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" <?php echo e($periode && $periode->id == $p->id ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::parse($p->periode.'-01')->isoFormat('MMMM YYYY')); ?>

                                <?php echo e($p->is_closed ? '✓' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            </form>
            <a href="<?php echo e(route('master-data.coa.create')); ?>" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus me-1"></i>Tambah COA
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo e(session('warning')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Daftar COA (Chart of Accounts)
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px">NO</th>
                            <th>Nama Akun</th>
                            <th>Kode Akun</th>
                            <th>Tipe</th>
                            <th class="text-center">Posisi</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-center"><?php echo e($key + 1); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-calculator text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo e($coa->nama_akun); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><code><?php echo e($coa->kode_akun); ?></code></td>
                                <td>
                                    <span class="badge <?php echo e($coa->tipe_akun == 'Asset' || $coa->tipe_akun == 'Aset' ? 'bg-success' : ($coa->tipe_akun == 'Liability' || $coa->tipe_akun == 'Kewajiban' ? 'bg-warning' : ($coa->tipe_akun == 'Equity' || $coa->tipe_akun == 'Modal' ? 'bg-info' : ($coa->tipe_akun == 'Revenue' || $coa->tipe_akun == 'Pendapatan' ? 'bg-primary' : 'bg-danger')))); ?>">
                                        <?php echo e($coa->tipe_akun); ?>

                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $posisi = $posisiAkun[$coa->id] ?? 'Unknown';
                                    ?>
                                    <span class="badge <?php echo e($posisi == 'Debit' ? 'bg-primary' : 'bg-success'); ?>">
                                        <?php echo e($posisi); ?>

                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php
                                        $saldo = $saldoPeriode[$coa->id] ?? 0;
                                        if ($saldo == floor($saldo)) {
                                            echo number_format($saldo, 0, ',', '.');
                                        } else {
                                            echo number_format($saldo, 2, ',', '.');
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('master-data.coa.edit', $coa->id)); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('master-data.coa.destroy', $coa->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/master-data/coa/index.blade.php ENDPATH**/ ?>