
<div class="container-fluid p-0">
    <?php
        $kapasitas = $bopProses->kapasitas_per_jam ?? 0;
        $totalBop = $bopProses->total_bop_per_jam ?? 0;
        $komponenBop = is_array($bopProses->komponen_bop) ? $bopProses->komponen_bop : json_decode($bopProses->komponen_bop, true);
        if (!is_array($komponenBop)) $komponenBop = [];
    ?>

    <div class="row g-3">
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user-hard-hat me-2"></i>Informasi Proses BTKL
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>Nama Proses:</strong>
                                <span><?php echo e($bopProses->prosesProduksi->nama_proses ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>Kode Proses:</strong>
                                <span class="badge bg-info"><?php echo e($bopProses->prosesProduksi->kode_proses ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>Kapasitas per Jam:</strong>
                                <span class="badge bg-success"><?php echo e($kapasitas); ?> unit/jam</span>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($btkl): ?>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>BTKL per Jam:</strong>
                                <span class="text-primary fw-bold">Rp <?php echo e(number_format($btkl->tarif_per_jam ?? 0, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>BTKL per Unit:</strong>
                                <span class="text-success fw-bold">Rp <?php echo e(number_format($kapasitas > 0 ? ($btkl->tarif_per_jam ?? 0) / $kapasitas : 0, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Ringkasan BOP
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>Total BOP per Jam:</strong>
                                <span class="text-primary fw-bold">Rp <?php echo e(number_format($totalBop, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>BOP per Unit:</strong>
                                <span class="text-success fw-bold">Rp <?php echo e(number_format($kapasitas > 0 ? $totalBop / $kapasitas : 0, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>Budget:</strong>
                                <span class="text-info fw-bold">Rp <?php echo e(number_format($bopProses->budget ?? 0, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>Aktual:</strong>
                                <span class="text-warning fw-bold">Rp <?php echo e(number_format($bopProses->aktual ?? 0, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <strong>Selisih:</strong>
                                <?php
                                    $selisih = ($bopProses->budget ?? 0) - ($bopProses->aktual ?? 0);
                                ?>
                                <span class="fw-bold <?php echo e($selisih >= 0 ? 'text-success' : 'text-danger'); ?>">
                                    Rp <?php echo e(number_format(abs($selisih), 0, ',', '.')); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selisih < 0): ?> (Over) <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($komponenBop)): ?>
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Detail Komponen BOP per Jam
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 50%">Komponen BOP</th>
                                    <th style="width: 20%" class="text-end">Rate per Jam</th>
                                    <th style="width: 25%" class="text-center">Kontribusi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $komponenBop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $komponen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $rate = floatval($komponen['rate_per_hour'] ?? 0);
                                        $percentage = $totalBop > 0 ? ($rate / $totalBop) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo e($index + 1); ?></td>
                                        <td>
                                            <div class="fw-semibold"><?php echo e($komponen['component'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary">Rp <?php echo e(number_format($rate, 0, ',', '.')); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                    <div class="progress-bar bg-info" 
                                                         role="progressbar" 
                                                         style="width: <?php echo e($percentage); ?>%"
                                                         aria-valuenow="<?php echo e($percentage); ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?php echo e(number_format($percentage, 1)); ?>%</small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="2" class="text-end">Total BOP per Jam:</th>
                                    <th class="text-end">
                                        <span class="fw-bold text-success">Rp <?php echo e(number_format($totalBop, 0, ',', '.')); ?></span>
                                    </th>
                                    <th class="text-center">
                                        <span class="badge bg-success">100%</span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                Belum ada komponen BOP yang didefinisikan untuk proses ini.
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="row mt-3">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo e(route('master-data.bop.show-proses', $bopProses->id)); ?>" 
                   class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i>Lihat Detail Lengkap
                </a>
                <a href="<?php echo e(route('master-data.bop.edit-proses', $bopProses->id)); ?>" 
                   class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit BOP
                </a>
            </div>
        </div>
    </div>
</div><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop/show-proses-modal.blade.php ENDPATH**/ ?>