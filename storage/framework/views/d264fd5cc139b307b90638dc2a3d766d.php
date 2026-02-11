

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="fas fa-eye me-2"></i>Detail BOP Proses
    </h2>
    <div>
        <a href="<?php echo e(route('master-data.bop.edit-proses', $bopProses->id)); ?>" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit BOP Komponen
        </a>
        <a href="<?php echo e(route('master-data.bop.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke BOP
        </a>
    </div>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<div class="row g-4">
    <!-- Informasi Proses BTKL -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header" style="background-color: #8B7355; color: white;">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Proses BTKL</h6>
                <small>Data BTKL terkait dengan BOP proses produksi</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <strong class="text-muted">Kode Proses:</strong><br>
                            <span class="text-primary fw-bold fs-5"><?php echo e($bopProses->prosesProduksi->kode_proses); ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <strong class="text-muted">Nama Proses:</strong><br>
                            <span class="text-dark fw-semibold"><?php echo e($bopProses->prosesProduksi->nama_proses); ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <strong class="text-muted">Tarif BTKL:</strong><br>
                            <span class="text-success fw-bold fs-6">Rp <?php echo e(number_format($btkl->tarif_per_jam ?? 45000, 0, ',', '.')); ?>/jam</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <strong class="text-muted">Kapasitas:</strong><br>
                            <span class="badge bg-info fs-6"><?php echo e($bopProses->prosesProduksi->kapasitas_per_jam); ?> unit/jam</span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <strong class="text-muted">BTKL / pcs:</strong><br>
                            <span class="text-success fw-bold">Rp <?php echo e(number_format($btkl->biaya_per_produk ?? (($btkl->tarif_per_jam ?? 45000) / ($bopProses->prosesProduksi->kapasitas_per_jam ?? 1)), 0, ',', '.')); ?></span>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="mb-3">
                            <strong class="text-muted">Deskripsi:</strong><br>
                            <span class="text-muted"><?php echo e($btkl->deskripsi ?? 'Proses ' . strtolower($bopProses->prosesProduksi->nama_proses) . ' makanan'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Ringkasan BOP -->
        <div class="col-lg-6">
            <?php
                // Calculate variables needed for Ringkasan BOP
                $totalBop = $bopProses->total_bop_per_jam ?? 42000;
                $kapasitas = $bopProses->prosesProduksi->kapasitas_per_jam ?? 50;
            ?>
            
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background-color: #8B7355; color: white;">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Ringkasan BOP</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <strong class="text-muted">Total BOP per Jam:</strong>
                                </div>
                                <div>
                                    <span class="fs-4 text-primary fw-bold">Rp <?php echo e(number_format($totalBop, 0, ',', '.')); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <strong class="text-muted">BOP per Unit:</strong>
                                </div>
                                <div>
                                    <span class="fs-4 text-success fw-bold">Rp <?php echo e(number_format($totalBop / $kapasitas, 0, ',', '.')); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                <div>
                                    <strong class="text-muted">Efisiensi BOP:</strong>
                                </div>
                                <div>
                                    <span class="badge bg-info fs-6"><?php echo e($kapasitas); ?> unit per jam</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Komponen BOP per Jam -->
    <div class="card shadow-sm mt-4">
        <div class="card-header" style="background-color: #8B7355; color: white;">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Detail Komponen BOP per Jam</h6>
        </div>
        <div class="card-body">
            <?php
                // Get komponen BOP from the actual data structure
                $komponenBop = [];
                
                // First try to get from the raw database field
                $rawKomponen = null;
                if (isset($bopProses->attributes['komponen_bop'])) {
                    $rawKomponen = $bopProses->attributes['komponen_bop'];
                } else {
                    // Fallback to the casted attribute
                    $rawKomponen = $bopProses->getAttributes()['komponen_bop'] ?? null;
                }
                
                if (!$rawKomponen) {
                    // If no JSON data, get from individual fields (old structure)
                    $rawKomponen = [
                        'listrik_per_jam' => $bopProses->listrik_per_jam ?? 0,
                        'gas_bbm_per_jam' => $bopProses->gas_bbm_per_jam ?? 0,
                        'penyusutan_mesin_per_jam' => $bopProses->penyusutan_mesin_per_jam ?? 0,
                        'maintenance_per_jam' => $bopProses->maintenance_per_jam ?? 0,
                        'gaji_mandor_per_jam' => $bopProses->gaji_mandor_per_jam ?? 0,
                        'lain_lain_per_jam' => $bopProses->lain_lain_per_jam ?? 0,
                    ];
                }
                
                // Convert to array if it's JSON string
                if (is_string($rawKomponen)) {
                    $rawKomponen = json_decode($rawKomponen, true);
                }
                
                // Convert from old format to new format for display
                if (is_array($rawKomponen)) {
                    // Check if it's the old format (with keys like 'listrik_per_jam')
                    if (isset($rawKomponen['listrik_per_jam'])) {
                        $komponenBop = [
                            ['component' => 'Listrik Mesin', 'rate_per_hour' => floatval($rawKomponen['listrik_per_jam'] ?? 0)],
                            ['component' => 'Gas / BBM', 'rate_per_hour' => floatval($rawKomponen['gas_bbm_per_jam'] ?? 0)],
                            ['component' => 'Penyusutan Mesin', 'rate_per_hour' => floatval($rawKomponen['penyusutan_mesin_per_jam'] ?? 0)],
                            ['component' => 'Maintenance', 'rate_per_hour' => floatval($rawKomponen['maintenance_per_jam'] ?? 0)],
                            ['component' => 'Gaji Mandor', 'rate_per_hour' => floatval($rawKomponen['gaji_mandor_per_jam'] ?? 0)],
                            ['component' => 'Lain-lain', 'rate_per_hour' => floatval($rawKomponen['lain_lain_per_jam'] ?? 0)]
                        ];
                        // Filter out components with 0 value
                        $komponenBop = array_filter($komponenBop, function($item) {
                            return $item['rate_per_hour'] > 0;
                        });
                    } else {
                        // New format (with 'component' and 'rate_per_hour' keys)
                        $komponenBop = $rawKomponen;
                    }
                }
                
                // Default components if empty
                if (empty($komponenBop)) {
                    $komponenBop = [
                        ['component' => 'Listrik Mesin', 'rate_per_hour' => 5000],
                        ['component' => 'Gas / BBM', 'rate_per_hour' => 20000],
                        ['component' => 'Penyusutan Mesin', 'rate_per_hour' => 10000],
                        ['component' => 'Maintenance', 'rate_per_hour' => 5000],
                        ['component' => 'Lain-lain', 'rate_per_hour' => 2000]
                    ];
                }
                
                // Calculate total from components if total_bop_per_jam is 0 or incorrect
                $calculatedTotal = array_sum(array_column($komponenBop, 'rate_per_hour'));
                
                // Use the already defined variables from above, but recalculate if needed
                if ($totalBop == 0 || abs($totalBop - $calculatedTotal) > 1) {
                    $totalBop = $calculatedTotal;
                }
            ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%">Komponen BOP</th>
                            <th class="text-end" style="width: 20%">Biaya per Jam</th>
                            <th class="text-center" style="width: 25%">Persentase</th>
                            <th class="text-end" style="width: 25%">Biaya per Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $komponenBop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $component): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $biayaPerJam = $component['rate_per_hour'] ?? 0;
                                $percentage = $totalBop > 0 ? ($biayaPerJam / $totalBop) * 100 : 0;
                                $biayaPerUnit = $kapasitas > 0 ? $biayaPerJam / $kapasitas : 0;
                                
                                // Color scheme for progress bars
                                $colors = ['primary', 'danger', 'warning', 'info', 'success', 'secondary'];
                                $colorIndex = is_numeric($index) ? intval($index) : array_search($index, array_keys($komponenBop));
                                $colorIndex = $colorIndex === false ? 0 : $colorIndex;
                                $color = $colors[$colorIndex % count($colors)];
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-circle text-<?php echo e($color); ?> me-2"></i>
                                        <span class="fw-semibold"><?php echo e($component['component']); ?></span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-dark">Rp <?php echo e(number_format($biayaPerJam, 0, ',', '.')); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="progress mb-1" style="height: 20px;">
                                        <div class="progress-bar bg-<?php echo e($color); ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo e($percentage); ?>%"
                                             aria-valuenow="<?php echo e($percentage); ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo e(number_format($percentage, 1)); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="text-success fw-semibold">Rp <?php echo e(number_format($biayaPerUnit, 0, ',', '.')); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                    <tfoot class="table-warning">
                        <tr>
                            <th><strong>Total BOP</strong></th>
                            <th class="text-end"><strong>Rp <?php echo e(number_format($totalBop, 0, ',', '.')); ?></strong></th>
                            <th class="text-center"><strong>100%</strong></th>
                            <th class="text-end"><strong>Rp <?php echo e(number_format($totalBop / $kapasitas, 0, ',', '.')); ?></strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bop/show-proses.blade.php ENDPATH**/ ?>