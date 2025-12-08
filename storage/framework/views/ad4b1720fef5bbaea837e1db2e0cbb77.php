<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Data COA</h1>
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
            <div>
                <label class="form-label">&nbsp;</label>
                <a href="<?php echo e(route('master-data.coa.create')); ?>" class="btn btn-primary d-block">Tambah COA</a>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <div class="alert alert-info">
        <strong><i class="bi bi-info-circle"></i> Periode: <?php echo e(\Carbon\Carbon::parse($periode->periode.'-01')->isoFormat('MMMM YYYY')); ?></strong>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($periode->is_closed): ?>
            <span class="badge bg-success ms-2">Periode Ditutup</span>
        <?php else: ?>
            <span class="badge bg-warning ms-2">Periode Aktif</span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <p class="mb-0 mt-2 small">Saldo awal yang ditampilkan adalah saldo untuk periode yang dipilih.</p>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th>Kategori Akun</th>
                    <th>Kode Induk</th>
                    <th>Saldo Normal</th>
                    <th>Saldo Awal</th>
                    <th class="col-keterangan">Keterangan</th>
                    <th style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($coa->id); ?></td>
                    <td><?php echo e($coa->kode_akun); ?></td>
                    <td><?php echo e($coa->nama_akun); ?></td>
                    <td><?php echo e($coa->kategori_akun); ?></td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coa->kode_induk): ?>
                            <?php echo e($coa->kode_induk); ?> - <?php echo e(\App\Models\Coa::where('kode_akun', $coa->kode_induk)->value('nama_akun')); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="text-capitalize"><?php echo e($coa->saldo_normal); ?></td>
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
                    <td class="col-keterangan"><small class="text-muted"><?php echo e($coa->keterangan); ?></small></td>
                    <td>
                        <a href="<?php echo e(route('master-data.coa.edit', $coa->kode_akun)); ?>" class="btn btn-warning btn-sm">Edit</a>
                        <form action="<?php echo e(route('master-data.coa.destroy', $coa->kode_akun)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<style>
    .table-responsive { overflow-x: auto; }
    .col-keterangan { white-space: normal; min-width: 200px; max-width: 300px; }
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
</style>

<!-- No custom JS: rely on native horizontal scrollbar/trackpad like tabel Pegawai -->
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/coa/index.blade.php ENDPATH**/ ?>