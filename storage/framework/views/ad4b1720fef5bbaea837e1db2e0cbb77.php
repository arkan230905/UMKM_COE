<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calculator me-2"></i>COA
        </h2>
        <div class="d-flex gap-2">
            <form method="get" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label">Pilih Periode</label>
                    <select name="period_id" class="form-select" onchange="this.form.submit()" style="min-width: 200px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" <?php echo e($periode && $periode->id == $p->id ? 'selected' : ''); ?>>
                                <?php echo e(\Carbon\Carbon::parse($p->periode.'-01')->isoFormat('MMMM YYYY')); ?>

                                <?php echo e($p->is_closed ? '✓' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            </form>
            <a href="<?php echo e(route('master-data.coa.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah COA
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

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
                            <th class="text-center" style="width: 50px">#</th>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <th>Kategori Akun</th>
                            <th>Kode Induk</th>
                            <th>Saldo Normal</th>
                            <th>Saldo Awal</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-center"><?php echo e($key + 1); ?></td>
                                <td><code><?php echo e($coa->kode_akun); ?></code></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                            <i class="fas fa-calculator text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                <?php echo e($coa->nama_akun); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coa->is_akun_header): ?>
                                                    <span class="badge bg-secondary ms-1">Header</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <small class="text-muted">ID: <?php echo e($coa->id); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($coa->kategori_akun); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coa->kode_induk): ?>
                                        <span class="badge bg-secondary"><?php echo e($coa->kode_induk); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-capitalize">
                                    <?php
                                        $saldoNormal = strtolower($coa->saldo_normal);
                                    ?>
                                    <span class="badge <?php echo e($saldoNormal == 'debit' ? 'bg-success' : 'bg-warning'); ?>">
                                        <?php echo e($saldoNormal == 'debit' ? 'debit' : 'credit'); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $saldo = $saldoPeriode[$coa->kode_akun] ?? 0;
                                    ?>
                                    <span class="<?php echo e($saldo != ($coa->saldo_awal ?? 0) ? 'text-primary fw-bold' : ''); ?>">
                                        Rp <?php echo e(number_format((float)$saldo, 0, ',', '.')); ?>

                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($saldo != ($coa->saldo_awal ?? 0)): ?>
                                        <small class="text-muted d-block">(Default: Rp <?php echo e(number_format((float)($coa->saldo_awal ?? 0), 0, ',', '.')); ?>)</small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?php echo e($coa->keterangan); ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('master-data.coa.edit', $coa->kode_akun)); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('master-data.coa.destroy', $coa->kode_akun)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/coa/index.blade.php ENDPATH**/ ?>