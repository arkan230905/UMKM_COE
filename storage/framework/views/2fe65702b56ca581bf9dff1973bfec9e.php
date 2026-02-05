

<?php $__env->startSection('content'); ?>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-edit me-2"></i>Edit Perhitungan Biaya Bahan
            <small class="text-muted fw-normal">- <?php echo e($produk->nama_produk); ?></small>
        </h2>
        <div class="btn-group">
            <a href="<?php echo e(route('master-data.biaya-bahan.show', $produk->id)); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Product Info Card -->
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>Informasi Produk
            </h6>
        </div>
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1"><strong>Produk:</strong></p>
                    <p class="text-muted"><?php echo e($produk->nama_produk); ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Jumlah Produk yang Dibuat:</strong></p>
                    <p class="text-muted"><?php echo e(number_format($produk->stok, 0, ',', '.')); ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Total Biaya Bahan Saat Ini:</strong></p>
                    <p class="text-muted">Rp <?php echo e(number_format($produk->harga_bom, 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('master-data.biaya-bahan.update', $produk->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <!-- Bahan Baku Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h6 class="mb-0">
                    <i class="fas fa-cube me-2"></i>1. Biaya Bahan Baku (BBB)
                    <small>(<?php echo e(count($bomDetails)); ?> item)</small>
                </h6>
            </div>
            <div class="card-body" style="background-color: #f8f4ff;">
                <div class="table-responsive">
                    <table class="table table-sm" id="bahanBakuTable">
                        <thead style="background-color: #9f7aea; color: white;">
                            <tr>
                                <th>BAHAN BAKU</th>
                                <th class="text-center">JUMLAH</th>
                                <th class="text-center">SATUAN</th>
                                <th class="text-end">HARGA SATUAN</th>
                                <th class="text-end">SUB TOTAL</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bomDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <select name="bahan_baku[<?php echo e($index); ?>][id]" class="form-select form-select-sm bahan-baku-select">
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahanBaku): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $satuanBB = is_object($bahanBaku->satuan) ? $bahanBaku->satuan->nama : $bahanBaku->satuan;
                                                    
                                                    // Prepare sub satuan data
                                                    $subSatuanData = [];
                                                    if ($bahanBaku->subSatuan1) {
                                                        $subSatuanData[] = [
                                                            'id' => $bahanBaku->sub_satuan_1_id,
                                                            'nama' => $bahanBaku->subSatuan1->nama,
                                                            'konversi' => $bahanBaku->sub_satuan_1_konversi,
                                                            'nilai' => $bahanBaku->sub_satuan_1_nilai
                                                        ];
                                                    }
                                                    if ($bahanBaku->subSatuan2) {
                                                        $subSatuanData[] = [
                                                            'id' => $bahanBaku->sub_satuan_2_id,
                                                            'nama' => $bahanBaku->subSatuan2->nama,
                                                            'konversi' => $bahanBaku->sub_satuan_2_konversi,
                                                            'nilai' => $bahanBaku->sub_satuan_2_nilai
                                                        ];
                                                    }
                                                    if ($bahanBaku->subSatuan3) {
                                                        $subSatuanData[] = [
                                                            'id' => $bahanBaku->sub_satuan_3_id,
                                                            'nama' => $bahanBaku->subSatuan3->nama,
                                                            'konversi' => $bahanBaku->sub_satuan_3_konversi,
                                                            'nilai' => $bahanBaku->sub_satuan_3_nilai
                                                        ];
                                                    }
                                                ?>
                                                <option value="<?php echo e($bahanBaku->id); ?>" 
                                                        data-harga="<?php echo e($bahanBaku->harga_satuan); ?>"
                                                        data-satuan="<?php echo e($satuanBB); ?>"
                                                        data-sub-satuan="<?php echo e(json_encode($subSatuanData)); ?>"
                                                        <?php echo e($detail->bahan_baku_id == $bahanBaku->id ? 'selected' : ''); ?>>
                                                    <?php echo e($bahanBaku->nama_bahan); ?> - Rp <?php echo e(number_format($bahanBaku->harga_satuan, 0, ',', '.')); ?>/<?php echo e($satuanBB); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </td>
                                    <td style="width: 120px;">
                                        <input type="number" name="bahan_baku[<?php echo e($index); ?>][jumlah]" 
                                               class="form-control form-control-sm qty-input text-center" 
                                               value="<?php echo e($detail->jumlah); ?>" 
                                               step="0.01" min="0">
                                    </td>
                                    <td style="width: 120px;">
                                        <select name="bahan_baku[<?php echo e($index); ?>][satuan]" class="form-select form-select-sm satuan-select">
                                            <option value="">-- Satuan --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($satuan->nama); ?>" 
                                                        <?php echo e($detail->satuan == $satuan->nama ? 'selected' : ''); ?>>
                                                    <?php echo e($satuan->nama); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </td>
                                    <td class="text-end harga-display" style="width: 200px;">
                                        <div class="harga-utama">Rp <?php echo e(number_format($detail->harga_per_satuan, 0, ',', '.')); ?></div>
                                        <div class="harga-konversi mt-1" style="font-size: 0.75rem; color: #666;"></div>
                                    </td>
                                    <td class="text-end subtotal-display" style="width: 150px;">
                                        <strong>Rp <?php echo e(number_format($detail->total_harga, 0, ',', '.')); ?></strong>
                                    </td>
                                    <td class="text-center" style="width: 80px;">
                                        <button type="button" class="btn btn-sm btn-danger remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <tr id="newBahanBakuRow" class="d-none">
                                <td>
                                    <select name="bahan_baku[new][id]" class="form-select form-select-sm bahan-baku-select">
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanBakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahanBaku): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $satuanBB = is_object($bahanBaku->satuan) ? $bahanBaku->satuan->nama : $bahanBaku->satuan;
                                                
                                                // Prepare sub satuan data
                                                $subSatuanData = [];
                                                if ($bahanBaku->subSatuan1) {
                                                    $subSatuanData[] = [
                                                        'id' => $bahanBaku->sub_satuan_1_id,
                                                        'nama' => $bahanBaku->subSatuan1->nama,
                                                        'konversi' => $bahanBaku->sub_satuan_1_konversi,
                                                        'nilai' => $bahanBaku->sub_satuan_1_nilai
                                                    ];
                                                }
                                                if ($bahanBaku->subSatuan2) {
                                                    $subSatuanData[] = [
                                                        'id' => $bahanBaku->sub_satuan_2_id,
                                                        'nama' => $bahanBaku->subSatuan2->nama,
                                                        'konversi' => $bahanBaku->sub_satuan_2_konversi,
                                                        'nilai' => $bahanBaku->sub_satuan_2_nilai
                                                    ];
                                                }
                                                if ($bahanBaku->subSatuan3) {
                                                    $subSatuanData[] = [
                                                        'id' => $bahanBaku->sub_satuan_3_id,
                                                        'nama' => $bahanBaku->subSatuan3->nama,
                                                        'konversi' => $bahanBaku->sub_satuan_3_konversi,
                                                        'nilai' => $bahanBaku->sub_satuan_3_nilai
                                                    ];
                                                }
                                            ?>
                                            <option value="<?php echo e($bahanBaku->id); ?>" 
                                                    data-harga="<?php echo e($bahanBaku->harga_satuan); ?>"
                                                    data-satuan="<?php echo e($satuanBB); ?>"
                                                    data-sub-satuan="<?php echo e(json_encode($subSatuanData)); ?>">
                                                <?php echo e($bahanBaku->nama_bahan); ?> - Rp <?php echo e(number_format($bahanBaku->harga_satuan, 0, ',', '.')); ?>/<?php echo e($satuanBB); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </td>
                                <td style="width: 120px;">
                                    <input type="number" name="bahan_baku[new][jumlah]" 
                                           class="form-control form-control-sm qty-input text-center" 
                                           step="0.01" min="0" placeholder="0">
                                </td>
                                <td style="width: 120px;">
                                    <select name="bahan_baku[new][satuan]" class="form-select form-select-sm satuan-select">
                                        <option value="">-- Satuan --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($satuan->nama); ?>"><?php echo e($satuan->nama); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </td>
                                <td class="text-end harga-display" style="width: 200px;">
                                    <div class="harga-utama">-</div>
                                    <div class="harga-konversi mt-1" style="font-size: 0.75rem; color: #666;"></div>
                                </td>
                                <td class="text-end subtotal-display" style="width: 150px;">-</td>
                                <td class="text-center" style="width: 80px;">
                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot style="background-color: #fef3c7;">
                            <tr>
                                <th colspan="4" class="text-end">Total BBB</th>
                                <th class="text-end" id="totalBahanBaku">Rp 0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-primary mt-2" id="addBahanBaku" onclick="window.addBahanBakuRow(); return false;">
                    <i class="fas fa-plus"></i> Tambah Bahan Baku
                </button>
            </div>
        </div>

        <!-- Bahan Pendukung Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <h6 class="mb-0">
                    <i class="fas fa-flask me-2"></i>2. Bahan Pendukung/Penolong
                    <small>(<?php echo e(count($bomJobBahanPendukung)); ?> item)</small>
                </h6>
            </div>
            <div class="card-body" style="background-color: #ecfeff;">
                <div class="table-responsive">
                    <table class="table table-sm" id="bahanPendukungTable">
                        <thead style="background-color: #22d3ee; color: white;">
                            <tr>
                                <th>BAHAN PENOLONG</th>
                                <th class="text-center">JUMLAH</th>
                                <th class="text-center">SATUAN</th>
                                <th class="text-end">HARGA SATUAN</th>
                                <th class="text-end">SUB TOTAL</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bomJobBahanPendukung; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pendukung): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <select name="bahan_pendukung[<?php echo e($index); ?>][id]" class="form-select form-select-sm bahan-pendukung-select">
                                            <option value="">-- Pilih Bahan Pendukung --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanPendukungs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahanPendukung): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $satuanBP = is_object($bahanPendukung->satuan) ? $bahanPendukung->satuan->nama : $bahanPendukung->satuan;
                                                    
                                                    // Prepare sub satuan data
                                                    $subSatuanData = [];
                                                    if ($bahanPendukung->subSatuan1) {
                                                        $subSatuanData[] = [
                                                            'id' => $bahanPendukung->sub_satuan_1_id,
                                                            'nama' => $bahanPendukung->subSatuan1->nama,
                                                            'konversi' => $bahanPendukung->sub_satuan_1_konversi,
                                                            'nilai' => $bahanPendukung->sub_satuan_1_nilai
                                                        ];
                                                    }
                                                    if ($bahanPendukung->subSatuan2) {
                                                        $subSatuanData[] = [
                                                            'id' => $bahanPendukung->sub_satuan_2_id,
                                                            'nama' => $bahanPendukung->subSatuan2->nama,
                                                            'konversi' => $bahanPendukung->sub_satuan_2_konversi,
                                                            'nilai' => $bahanPendukung->sub_satuan_2_nilai
                                                        ];
                                                    }
                                                    if ($bahanPendukung->subSatuan3) {
                                                        $subSatuanData[] = [
                                                            'id' => $bahanPendukung->sub_satuan_3_id,
                                                            'nama' => $bahanPendukung->subSatuan3->nama,
                                                            'konversi' => $bahanPendukung->sub_satuan_3_konversi,
                                                            'nilai' => $bahanPendukung->sub_satuan_3_nilai
                                                        ];
                                                    }
                                                ?>
                                                <option value="<?php echo e($bahanPendukung->id); ?>" 
                                                        data-harga="<?php echo e($bahanPendukung->harga_satuan); ?>"
                                                        data-satuan="<?php echo e($satuanBP); ?>"
                                                        data-sub-satuan="<?php echo e(json_encode($subSatuanData)); ?>"
                                                        <?php echo e($pendukung->bahan_pendukung_id == $bahanPendukung->id ? 'selected' : ''); ?>>
                                                    <?php echo e($bahanPendukung->nama_bahan); ?> - Rp <?php echo e(number_format($bahanPendukung->harga_satuan, 0, ',', '.')); ?>/<?php echo e($satuanBP); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </td>
                                    <td style="width: 120px;">
                                        <input type="number" name="bahan_pendukung[<?php echo e($index); ?>][jumlah]" 
                                               class="form-control form-control-sm qty-input text-center" 
                                               value="<?php echo e($pendukung->jumlah); ?>" 
                                               step="0.01" min="0">
                                    </td>
                                    <td style="width: 120px;">
                                        <select name="bahan_pendukung[<?php echo e($index); ?>][satuan]" class="form-select form-select-sm satuan-select">
                                            <option value="">-- Satuan --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($satuan->nama); ?>" 
                                                        <?php echo e($pendukung->satuan == $satuan->nama ? 'selected' : ''); ?>>
                                                    <?php echo e($satuan->nama); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </td>
                                    <td class="text-end harga-display" style="width: 200px;">
                                        <div class="harga-utama">Rp <?php echo e(number_format($pendukung->harga_satuan, 0, ',', '.')); ?></div>
                                        <div class="harga-konversi mt-1" style="font-size: 0.75rem; color: #666;"></div>
                                    </td>
                                    <td class="text-end subtotal-display" style="width: 150px;">
                                        <strong>Rp <?php echo e(number_format($pendukung->subtotal, 0, ',', '.')); ?></strong>
                                    </td>
                                    <td class="text-center" style="width: 80px;">
                                        <button type="button" class="btn btn-sm btn-danger remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <tr id="newBahanPendukungRow" class="d-none">
                                <td>
                                    <select name="bahan_pendukung[new][id]" class="form-select form-select-sm bahan-pendukung-select">
                                        <option value="">-- Pilih Bahan Pendukung --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bahanPendukungs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahanPendukung): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $satuanBP = is_object($bahanPendukung->satuan) ? $bahanPendukung->satuan->nama : $bahanPendukung->satuan;
                                                
                                                // Prepare sub satuan data
                                                $subSatuanData = [];
                                                if ($bahanPendukung->subSatuan1) {
                                                    $subSatuanData[] = [
                                                        'id' => $bahanPendukung->sub_satuan_1_id,
                                                        'nama' => $bahanPendukung->subSatuan1->nama,
                                                        'konversi' => $bahanPendukung->sub_satuan_1_konversi,
                                                        'nilai' => $bahanPendukung->sub_satuan_1_nilai
                                                    ];
                                                }
                                                if ($bahanPendukung->subSatuan2) {
                                                    $subSatuanData[] = [
                                                        'id' => $bahanPendukung->sub_satuan_2_id,
                                                        'nama' => $bahanPendukung->subSatuan2->nama,
                                                        'konversi' => $bahanPendukung->sub_satuan_2_konversi,
                                                        'nilai' => $bahanPendukung->sub_satuan_2_nilai
                                                    ];
                                                }
                                                if ($bahanPendukung->subSatuan3) {
                                                    $subSatuanData[] = [
                                                        'id' => $bahanPendukung->sub_satuan_3_id,
                                                        'nama' => $bahanPendukung->subSatuan3->nama,
                                                        'konversi' => $bahanPendukung->sub_satuan_3_konversi,
                                                        'nilai' => $bahanPendukung->sub_satuan_3_nilai
                                                    ];
                                                }
                                            ?>
                                            <option value="<?php echo e($bahanPendukung->id); ?>" 
                                                    data-harga="<?php echo e($bahanPendukung->harga_satuan); ?>"
                                                    data-satuan="<?php echo e($satuanBP); ?>"
                                                    data-sub-satuan="<?php echo e(json_encode($subSatuanData)); ?>">
                                                <?php echo e($bahanPendukung->nama_bahan); ?> - Rp <?php echo e(number_format($bahanPendukung->harga_satuan, 0, ',', '.')); ?>/<?php echo e($satuanBP); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </td>
                                <td style="width: 120px;">
                                    <input type="number" name="bahan_pendukung[new][jumlah]" 
                                           class="form-control form-control-sm qty-input text-center" 
                                           step="0.01" min="0" placeholder="0">
                                </td>
                                <td style="width: 120px;">
                                    <select name="bahan_pendukung[new][satuan]" class="form-select form-select-sm satuan-select">
                                        <option value="">-- Satuan --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($satuan->nama); ?>"><?php echo e($satuan->nama); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </td>
                                <td class="text-end harga-display" style="width: 200px;">
                                    <div class="harga-utama">-</div>
                                    <div class="harga-konversi mt-1" style="font-size: 0.75rem; color: #666;"></div>
                                </td>
                                <td class="text-end subtotal-display" style="width: 150px;">-</td>
                                <td class="text-center" style="width: 80px;">
                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot style="background-color: #cffafe;">
                            <tr>
                                <th colspan="4" class="text-end">Total Bahan Pendukung</th>
                                <th class="text-end" id="totalBahanPendukung">Rp 0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-info mt-2" id="addBahanPendukung" onclick="window.addBahanPendukungRow(); return false;">
                    <i class="fas fa-plus"></i> Tambah Bahan Pendukung
                </button>
            </div>
        </div>

        <!-- Summary & Action Buttons -->
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- DEBUG TEST BUTTON -->
                <div class="mb-3 p-2 bg-light border rounded">
                    <small class="text-muted">Debug Test:</small>
                    <button type="button" class="btn btn-sm btn-warning ms-2" onclick="testConversionFunction()">
                        🧪 Test Conversion Function
                    </button>
                    <button type="button" class="btn btn-sm btn-info ms-2" onclick="testSubtotalCalculation()">
                        🧮 Test Subtotal Calculation
                    </button>
                    <button type="button" class="btn btn-sm btn-danger ms-2" onclick="emergencyDebug()">
                        🚨 Emergency Debug
                    </button>
                    <button type="button" class="btn btn-sm btn-success ms-2" onclick="forceEditInitialization()">
                        🔄 Force Init
                    </button>
                    <div id="testResult" class="mt-1 text-small"></div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Total Biaya Bahan: <span id="summaryTotalBiaya" class="text-success">Rp 0</span></h5>
                        <small class="text-muted">
                            BBB: <span id="summaryBahanBaku">Rp 0</span> | 
                            Bahan Pendukung: <span id="summaryBahanPendukung">Rp 0</span>
                        </small>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                        <a href="<?php echo e(route('master-data.biaya-bahan.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// BIAYA BAHAN EDIT FIXED VERSION - 2026-02-06 12:30:00
console.log("🚀 BIAYA BAHAN EDIT LOADED - " + new Date().toISOString());

// Global flag
window.biayaBahanReady = true;

// Helper functions (SAME AS CREATE)
function formatClean(num) {
    if (typeof num === 'string') {
        num = parseFloat(num);
    }
    return num === Math.floor(num) ? Math.floor(num).toString() : parseFloat(num.toFixed(4)).toString();
}

function formatRupiah(num) {
    if (typeof num === 'string') {
        num = parseFloat(num);
    }
    return "Rp " + Math.round(num).toLocaleString("id-ID");
}

// MAIN FUNCTION: Update conversion display (SAME AS CREATE)
function updateConversionDisplay(row, option) {
    console.log("📊 updateConversionDisplay called");
    
    const hargaKonversiDiv = row.querySelector(".harga-konversi");
    const satuanSelect = row.querySelector(".satuan-select");
    
    if (!hargaKonversiDiv || !option || !satuanSelect) {
        console.log("❌ Missing elements");
        return;
    }
    
    const hargaUtama = parseFloat(option.dataset.harga) || 0;
    const satuanUtama = option.dataset.satuan || "unit";
    const satuanDipilih = satuanSelect.value;
    
    // Parse sub satuan data with error handling
    let subSatuanData = [];
    try {
        const rawData = option.dataset.subSatuan || "[]";
        subSatuanData = JSON.parse(rawData);
        console.log("📋 Parsed sub satuan data:", subSatuanData);
    } catch (e) {
        console.error("❌ Error parsing sub satuan data:", e);
        subSatuanData = [];
    }
    
    console.log("📋 Data:", {
        harga: hargaUtama,
        satuanUtama: satuanUtama,
        satuanDipilih: satuanDipilih,
        subSatuanCount: subSatuanData.length
    });
    
    if (!satuanDipilih) {
        hargaKonversiDiv.innerHTML = '<small class="text-muted">Pilih satuan untuk konversi</small>';
        return;
    }
    
    // Use database sub satuan
    if (subSatuanData.length > 0) {
        console.log("✅ Using database sub satuan");
        
        // Find exact match
        const match = subSatuanData.find(sub => 
            sub.nama.toLowerCase().trim() === satuanDipilih.toLowerCase().trim()
        );
        
        if (match) {
            // Specific conversion - FIXED CALCULATION
            const konversi = parseFloat(match.konversi) || 1;
            const nilai = parseFloat(match.nilai) || 1;
            const hargaKonversi = (hargaUtama * konversi) / nilai;
            const konversiClean = formatClean(konversi);
            const nilaiClean = formatClean(nilai);
            
            console.log("🎯 Found match:", {
                match: match,
                konversi: konversi,
                nilai: nilai,
                hargaKonversi: hargaKonversi
            });
            
            hargaKonversiDiv.innerHTML = `
                <div class="text-info mb-2">
                    <strong>${formatRupiah(hargaKonversi)}/${satuanDipilih}</strong>
                </div>
                <div class="text-muted" style="font-size: 0.85rem; line-height: 1.3;">
                    <div class="fw-bold text-primary mb-1">📊 Rumus:</div>
                    <div>• ${konversiClean} ${satuanUtama} = ${nilaiClean} ${satuanDipilih}</div>
                    <div>• ${formatRupiah(hargaUtama)} × ${konversiClean} ÷ ${nilaiClean}</div>
                    <div class="text-success fw-bold">• = ${formatRupiah(hargaKonversi)}</div>
                </div>
            `;
            return;
        }
        
        // Show all conversions if same unit
        if (satuanDipilih === satuanUtama) {
            console.log("📋 Same unit - showing all conversions");
            
            let html = '<div class="text-success mb-2"><strong>Satuan sama, tidak perlu konversi</strong></div>';
            html += '<div class="text-muted mb-2"><strong>Konversi Tersedia:</strong></div>';
            
            subSatuanData.forEach(sub => {
                const konversi = parseFloat(sub.konversi) || 1;
                const nilai = parseFloat(sub.nilai) || 1;
                const hargaKonversi = (hargaUtama * konversi) / nilai;
                const konversiClean = formatClean(konversi);
                const nilaiClean = formatClean(nilai);
                
                html += `
                    <div class="border-start border-info ps-2 mb-1" style="font-size: 0.8rem;">
                        <div class="text-info"><strong>${formatRupiah(hargaKonversi)}/${sub.nama}</strong></div>
                        <div class="text-muted">${konversiClean} ${satuanUtama} = ${nilaiClean} ${sub.nama}</div>
                    </div>
                `;
            });
            
            hargaKonversiDiv.innerHTML = html;
            return;
        } else {
            // No match found
            console.log("⚠️ No conversion match found");
            hargaKonversiDiv.innerHTML = `
                <div class="text-warning mb-1">Konversi tidak ditemukan</div>
                <small class="text-muted">Dari ${satuanUtama} ke ${satuanDipilih}</small>
            `;
            return;
        }
    }
    
    // No sub satuan data
    if (satuanDipilih === satuanUtama) {
        hargaKonversiDiv.innerHTML = `
            <div class="text-success mb-1">
                <strong>${formatRupiah(hargaUtama)}/${satuanUtama}</strong>
            </div>
            <small class="text-muted">Satuan sama, tidak perlu konversi</small>
        `;
    } else {
        hargaKonversiDiv.innerHTML = `
            <div class="text-warning">Konversi tidak tersedia</div>
            <small class="text-muted">Dari ${satuanUtama} ke ${satuanDipilih}</small>
        `;
    }
}

// Get conversion factor for calculations (SAME AS CREATE)
function getConversionFactor(fromUnit, toUnit, subSatuanData = []) {
    if (fromUnit.toLowerCase() === toUnit.toLowerCase()) {
        return 1;
    }
    
    if (subSatuanData.length > 0) {
        const match = subSatuanData.find(sub => 
            sub.nama.toLowerCase().trim() === toUnit.toLowerCase().trim()
        );
        
        if (match) {
            const konversi = parseFloat(match.konversi) || 1;
            const nilai = parseFloat(match.nilai) || 1;
            return konversi / nilai;
        }
    }
    
    return 1; // Default fallback
}

// Calculate row subtotal - ENHANCED FOR EDIT PAGE
function calculateRowSubtotal(row) {
    console.log("🧮 calculateRowSubtotal called (EDIT PAGE)");
    
    const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
    const qtyInput = row.querySelector(".qty-input");
    const satuanSelect = row.querySelector(".satuan-select");
    const subtotalDisplay = row.querySelector(".subtotal-display");
    
    if (!bahanSelect || !qtyInput || !satuanSelect || !subtotalDisplay) {
        console.log("❌ Missing elements for calculation");
        return;
    }
    
    const option = bahanSelect.options[bahanSelect.selectedIndex];
    if (!option || !option.value) {
        subtotalDisplay.innerHTML = "-";
        return;
    }
    
    const harga = parseFloat(option.dataset.harga) || 0;
    const qty = parseFloat(qtyInput.value) || 0;
    const satuanUtama = option.dataset.satuan || "unit";
    const satuanDipilih = satuanSelect.value;
    
    // Parse sub satuan data
    let subSatuanData = [];
    try {
        subSatuanData = JSON.parse(option.dataset.subSatuan || "[]");
    } catch (e) {
        console.error("❌ Error parsing sub satuan for calculation:", e);
        subSatuanData = [];
    }
    
    console.log("💰 EDIT PAGE Calculation data:", {
        bahan: option.text,
        harga: harga,
        qty: qty,
        satuanUtama: satuanUtama,
        satuanDipilih: satuanDipilih,
        subSatuanCount: subSatuanData.length,
        currentSubtotal: subtotalDisplay.textContent
    });
    
    if (qty <= 0 || !satuanDipilih) {
        subtotalDisplay.innerHTML = "-";
        return;
    }
    
    let subtotal = harga * qty;
    
    // Apply conversion if different units
    if (satuanUtama !== satuanDipilih) {
        const factor = getConversionFactor(satuanUtama, satuanDipilih, subSatuanData);
        subtotal = (harga * factor) * qty;
        console.log("🔄 Applied conversion factor:", factor, "New subtotal:", subtotal);
    }
    
    // CRITICAL: Always update the display, even if it had a previous value
    subtotalDisplay.innerHTML = `<strong class="text-success">${formatRupiah(subtotal)}</strong>`;
    console.log("✅ EDIT PAGE Subtotal updated:", subtotal, "for", option.text);
    
    // Update totals immediately
    setTimeout(calculateTotals, 50);
}

// Calculate all totals - ENHANCED FOR EDIT DEBUG
function calculateTotals() {
    console.log("📊 === CALCULATING TOTALS (EDIT PAGE) ===");
    
    let totalBB = 0;
    let totalBP = 0;
    let debugInfo = [];
    
    // Bahan Baku - ENHANCED PARSING
    const bbRows = document.querySelectorAll("#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)");
    debugInfo.push(`Found ${bbRows.length} BB rows`);
    
    bbRows.forEach((row, index) => {
        const subtotalDisplay = row.querySelector(".subtotal-display");
        let subtotal = 0;
        
        if (subtotalDisplay) {
            const subtotalText = subtotalDisplay.textContent || subtotalDisplay.innerText || "";
            
            // Enhanced parsing - handle both formatted and unformatted numbers
            if (subtotalText.includes("Rp")) {
                // Parse formatted currency (Rp 45,000)
                const cleanText = subtotalText.replace(/[^\d]/g, "");
                subtotal = parseFloat(cleanText) || 0;
            } else if (subtotalText.trim() !== "-" && subtotalText.trim() !== "") {
                // Parse plain number
                const cleanText = subtotalText.replace(/[^\d.]/g, "");
                subtotal = parseFloat(cleanText) || 0;
            }
        }
        
        const bahanSelect = row.querySelector(".bahan-baku-select");
        const bahanName = bahanSelect?.options[bahanSelect.selectedIndex]?.text || 'Unknown';
        const qtyInput = row.querySelector(".qty-input");
        const satuanSelect = row.querySelector(".satuan-select");
        
        debugInfo.push(`BB Row ${index}: ${bahanName}`);
        debugInfo.push(`  Qty: ${qtyInput?.value || 'none'}, Satuan: ${satuanSelect?.value || 'none'}`);
        debugInfo.push(`  Subtotal text: "${subtotalText}", Parsed: ${subtotal}`);
        
        totalBB += subtotal;
    });
    
    // Bahan Pendukung - ENHANCED PARSING
    const bpRows = document.querySelectorAll("#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)");
    debugInfo.push(`Found ${bpRows.length} BP rows`);
    
    bpRows.forEach((row, index) => {
        const subtotalDisplay = row.querySelector(".subtotal-display");
        let subtotal = 0;
        
        if (subtotalDisplay) {
            const subtotalText = subtotalDisplay.textContent || subtotalDisplay.innerText || "";
            
            // Enhanced parsing - handle both formatted and unformatted numbers
            if (subtotalText.includes("Rp")) {
                // Parse formatted currency (Rp 28,000)
                const cleanText = subtotalText.replace(/[^\d]/g, "");
                subtotal = parseFloat(cleanText) || 0;
            } else if (subtotalText.trim() !== "-" && subtotalText.trim() !== "") {
                // Parse plain number
                const cleanText = subtotalText.replace(/[^\d.]/g, "");
                subtotal = parseFloat(cleanText) || 0;
            }
        }
        
        const bahanSelect = row.querySelector(".bahan-pendukung-select");
        const bahanName = bahanSelect?.options[bahanSelect.selectedIndex]?.text || 'Unknown';
        const qtyInput = row.querySelector(".qty-input");
        const satuanSelect = row.querySelector(".satuan-select");
        
        debugInfo.push(`BP Row ${index}: ${bahanName}`);
        debugInfo.push(`  Qty: ${qtyInput?.value || 'none'}, Satuan: ${satuanSelect?.value || 'none'}`);
        debugInfo.push(`  Subtotal text: "${subtotalText}", Parsed: ${subtotal}`);
        
        totalBP += subtotal;
    });
    
    const total = totalBB + totalBP;
    
    console.log("📊 TOTALS CALCULATED:", { 
        bb: totalBB, 
        bp: totalBP, 
        total: total,
        debug: debugInfo
    });
    
    // Update displays
    const elements = {
        totalBahanBaku: document.getElementById("totalBahanBaku"),
        totalBahanPendukung: document.getElementById("totalBahanPendukung"),
        summaryBahanBaku: document.getElementById("summaryBahanBaku"),
        summaryBahanPendukung: document.getElementById("summaryBahanPendukung"),
        summaryTotalBiaya: document.getElementById("summaryTotalBiaya")
    };
    
    if (elements.totalBahanBaku) {
        elements.totalBahanBaku.textContent = formatRupiah(totalBB);
        console.log("✅ Updated totalBahanBaku:", formatRupiah(totalBB));
    }
    if (elements.totalBahanPendukung) {
        elements.totalBahanPendukung.textContent = formatRupiah(totalBP);
        console.log("✅ Updated totalBahanPendukung:", formatRupiah(totalBP));
    }
    if (elements.summaryBahanBaku) {
        elements.summaryBahanBaku.textContent = formatRupiah(totalBB);
        console.log("✅ Updated summaryBahanBaku:", formatRupiah(totalBB));
    }
    if (elements.summaryBahanPendukung) {
        elements.summaryBahanPendukung.textContent = formatRupiah(totalBP);
        console.log("✅ Updated summaryBahanPendukung:", formatRupiah(totalBP));
    }
    if (elements.summaryTotalBiaya) {
        elements.summaryTotalBiaya.textContent = formatRupiah(total);
        console.log("✅ Updated summaryTotalBiaya:", formatRupiah(total));
    }
    
    console.log("📊 === TOTALS UPDATE COMPLETE ===");
}

// Add event listeners to row - ENHANCED VERSION (SAME AS CREATE)
function addRowEventListeners(row) {
    const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
    const qtyInput = row.querySelector(".qty-input");
    const satuanSelect = row.querySelector(".satuan-select");
    const removeBtn = row.querySelector(".remove-item");
    
    if (bahanSelect) {
        bahanSelect.addEventListener("change", function() {
            console.log("🔄 Bahan changed:", this.value);
            const option = this.options[this.selectedIndex];
            if (option && option.dataset.harga) {
                // Auto-fill satuan utama (only for new rows)
                if (option.dataset.satuan && satuanSelect && !satuanSelect.value) {
                    satuanSelect.value = option.dataset.satuan;
                    console.log("✅ Auto-filled satuan:", option.dataset.satuan);
                }
                
                // Auto-set quantity to 1 (only for new rows)
                if (qtyInput && (!qtyInput.value || qtyInput.value === "0")) {
                    qtyInput.value = "1";
                    console.log("✅ Auto-set quantity to 1");
                }
                
                // Update harga display
                const hargaDisplay = row.querySelector(".harga-utama");
                if (hargaDisplay) {
                    const harga = parseInt(option.dataset.harga);
                    hargaDisplay.innerHTML = `<strong>${formatRupiah(harga)}</strong>`;
                    console.log("✅ Updated harga display:", harga);
                }
                
                // Show conversion immediately
                updateConversionDisplay(row, option);
                
                // Calculate subtotal
                calculateRowSubtotal(row);
            } else {
                // Clear displays if no selection
                const hargaDisplay = row.querySelector(".harga-utama");
                const hargaKonversiDiv = row.querySelector(".harga-konversi");
                const subtotalDisplay = row.querySelector(".subtotal-display");
                
                if (hargaDisplay) hargaDisplay.innerHTML = "-";
                if (hargaKonversiDiv) hargaKonversiDiv.innerHTML = "";
                if (subtotalDisplay) subtotalDisplay.innerHTML = "-";
                
                calculateTotals();
            }
        });
    }
    
    if (qtyInput) {
        qtyInput.addEventListener("input", function() {
            console.log("🔄 Quantity changed:", this.value);
            calculateRowSubtotal(row);
        });
    }
    
    if (satuanSelect) {
        satuanSelect.addEventListener("change", function() {
            console.log("🔄 Satuan changed:", this.value);
            const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
            if (bahanSelect && bahanSelect.value) {
                const option = bahanSelect.options[bahanSelect.selectedIndex];
                updateConversionDisplay(row, option);
                calculateRowSubtotal(row);
            }
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener("click", function() {
            if (confirm("Hapus baris ini?")) {
                row.remove();
                calculateTotals();
            }
        });
    }
}urn false;
// Add new row functions (SAME AS CREATE)
function addBahanBakuRow() {
    console.log("➕ Adding Bahan Baku row");
    
    const newRow = document.getElementById("newBahanBakuRow");
    if (!newRow) {
        console.error("❌ Template row not found");
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove("d-none");
    clone.id = "bahanBaku_" + Date.now();
    
    // Update name attributes
    const timestamp = Date.now();
    clone.querySelectorAll('[name^="bahan_baku[new]"]').forEach(input => {
        const fieldName = input.name.match(/\[new\]\[(\w+)\]/)[1];
        input.name = `bahan_baku[${timestamp}][${fieldName}]`;
        input.value = "";
    });
    
    tbody.insertBefore(clone, newRow);
    addRowEventListeners(clone);
    
    console.log("✅ Bahan Baku row added");
    return false;
}

function addBahanPendukungRow() {
    console.log("➕ Adding Bahan Pendukung row");
    
    const newRow = document.getElementById("newBahanPendukungRow");
    if (!newRow) {
        console.error("❌ Template row not found");
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove("d-none");
    clone.id = "bahanPendukung_" + Date.now();
    
    // Update name attributes
    const timestamp = Date.now();
    clone.querySelectorAll('[name^="bahan_pendukung[new]"]').forEach(input => {
        const fieldName = input.name.match(/\[new\]\[(\w+)\]/)[1];
        input.name = `bahan_pendukung[${timestamp}][${fieldName}]`;
        input.value = "";
    });
    
    tbody.insertBefore(clone, newRow);
    addRowEventListeners(clone);
    
    console.log("✅ Bahan Pendukung row added");
    return false;
}

// Force initialization function - EDIT PAGE SPECIFIC
function forceEditInitialization() {
    console.log("🚨 FORCE EDIT INITIALIZATION");
    
    let processed = 0;
    
    // Find all rows with data
    const allRows = document.querySelectorAll("#bahanBakuTable tbody tr, #bahanPendukungTable tbody tr");
    
    allRows.forEach((row, index) => {
        if (row.id === 'newBahanBakuRow' || row.id === 'newBahanPendukungRow') return;
        
        const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
        const qtyInput = row.querySelector(".qty-input");
        const satuanSelect = row.querySelector(".satuan-select");
        
        if (bahanSelect && bahanSelect.value && qtyInput && qtyInput.value && satuanSelect && satuanSelect.value) {
            console.log(`🔄 Force processing row ${index}:`, {
                bahan: bahanSelect.value,
                qty: qtyInput.value,
                satuan: satuanSelect.value
            });
            
            const option = bahanSelect.options[bahanSelect.selectedIndex];
            if (option && option.dataset.harga) {
                // Force update harga display
                const hargaDisplay = row.querySelector(".harga-utama");
                if (hargaDisplay) {
                    const harga = parseInt(option.dataset.harga);
                    hargaDisplay.innerHTML = `<strong>${formatRupiah(harga)}</strong>`;
                }
                
                // Force conversion display
                updateConversionDisplay(row, option);
                
                // Force subtotal calculation
                calculateRowSubtotal(row);
                
                processed++;
            }
        }
    });
    
    console.log(`✅ Force processed ${processed} rows`);
    
    // Force totals calculation
    setTimeout(() => {
        calculateTotals();
        console.log("✅ Force totals calculation completed");
    }, 200);
}

// Test functions for debugging (ENHANCED FOR EDIT)
function testConversionFunction() {
    console.log("🧪 Testing conversion function (EDIT PAGE)");
    const testResult = document.getElementById("testResult");
    
    try {
        // Test data
        const testData = [
            {
                "nama": "Kilogram",
                "konversi": "1.0000",
                "nilai": "1.5000"
            },
            {
                "nama": "Potong", 
                "konversi": "1.0000",
                "nilai": "6.0000"
            }
        ];
        
        const hargaUtama = 45000;
        const satuanUtama = "Ekor";
        
        let results = [];
        results.push(`Harga Utama: ${formatRupiah(hargaUtama)}/${satuanUtama}`);
        
        testData.forEach(sub => {
            const konversi = parseFloat(sub.konversi);
            const nilai = parseFloat(sub.nilai);
            const hargaKonversi = (hargaUtama * konversi) / nilai;
            
            results.push(`${sub.nama}: ${formatRupiah(hargaKonversi)} (${konversi}÷${nilai})`);
        });
        
        // Also test force initialization
        results.push("--- TESTING FORCE INITIALIZATION ---");
        forceEditInitialization();
        results.push("Force initialization triggered");
        
        if (testResult) {
            testResult.innerHTML = `<div class="alert alert-success">✅ Test Results:<br>${results.join('<br>')}</div>`;
        }
        
        console.log("✅ Conversion test passed");
        
    } catch (error) {
        if (testResult) {
            testResult.innerHTML = `<div class="alert alert-danger">❌ Test Error: ${error.message}</div>`;
        }
        console.error("❌ Conversion test failed:", error);
    }
}

function testSubtotalCalculation() {
    console.log("🧮 Testing subtotal calculation");
    const testResult = document.getElementById("testResult");
    
    try {
        const firstRow = document.querySelector("#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)");
        
        if (!firstRow) {
            if (testResult) {
                testResult.innerHTML = `<div class="alert alert-warning">⚠️ No rows found to test</div>`;
            }
            return;
        }
        
        const bahanSelect = firstRow.querySelector(".bahan-baku-select");
        const qtyInput = firstRow.querySelector(".qty-input");
        const satuanSelect = firstRow.querySelector(".satuan-select");
        
        let info = [];
        info.push(`Bahan: ${bahanSelect?.value || 'none'}`);
        info.push(`Quantity: ${qtyInput?.value || 'none'}`);
        info.push(`Satuan: ${satuanSelect?.value || 'none'}`);
        
        if (bahanSelect && bahanSelect.value) {
            const option = bahanSelect.options[bahanSelect.selectedIndex];
            info.push(`Harga: ${option?.dataset?.harga || 'none'}`);
            info.push(`Sub Satuan: ${option?.dataset?.subSatuan ? 'available' : 'none'}`);
            
            // Force calculation
            calculateRowSubtotal(firstRow);
            info.push(`Calculation triggered`);
        }
        
        if (testResult) {
            testResult.innerHTML = `<div class="alert alert-info">📊 Subtotal Test:<br>${info.join('<br>')}</div>`;
        }
        
        console.log("✅ Subtotal test completed");
        
    } catch (error) {
        if (testResult) {
            testResult.innerHTML = `<div class="alert alert-danger">❌ Subtotal Test Error: ${error.message}</div>`;
        }
        console.error("❌ Subtotal test failed:", error);
    }
}

// Emergency debug function - ENHANCED FOR EDIT PAGE
function emergencyDebug() {
    console.log("🚨 EMERGENCY DEBUG - EDIT PAGE");
    const testResult = document.getElementById("testResult");
    
    let info = [];
    info.push("=== EDIT PAGE DEBUG ===");
    info.push(`Functions loaded: ${typeof updateConversionDisplay !== 'undefined' ? '✅' : '❌'}`);
    
    info.push("=== DOM ELEMENTS ===");
    const tables = document.querySelectorAll("table");
    info.push(`Tables found: ${tables.length}`);
    
    const bbRows = document.querySelectorAll("#bahanBakuTable tbody tr:not(.d-none)");
    const bpRows = document.querySelectorAll("#bahanPendukungTable tbody tr:not(.d-none)");
    info.push(`BB rows: ${bbRows.length}, BP rows: ${bpRows.length}`);
    
    info.push("=== EXISTING DATA ANALYSIS ===");
    
    // Check BB rows
    bbRows.forEach((row, index) => {
        if (row.id === 'newBahanBakuRow') return;
        
        const bahanSelect = row.querySelector(".bahan-baku-select");
        const qtyInput = row.querySelector(".qty-input");
        const satuanSelect = row.querySelector(".satuan-select");
        const subtotalDisplay = row.querySelector(".subtotal-display");
        const hargaDisplay = row.querySelector(".harga-utama");
        const hargaKonversiDiv = row.querySelector(".harga-konversi");
        
        info.push(`BB Row ${index}:`);
        info.push(`  Bahan: ${bahanSelect?.value || 'none'} (${bahanSelect?.options[bahanSelect?.selectedIndex]?.text || 'none'})`);
        info.push(`  Qty: ${qtyInput?.value || 'none'}`);
        info.push(`  Satuan: ${satuanSelect?.value || 'none'}`);
        info.push(`  Harga Display: ${hargaDisplay?.innerHTML || 'none'}`);
        info.push(`  Konversi Display: ${hargaKonversiDiv?.innerHTML ? 'has content' : 'empty'}`);
        info.push(`  Subtotal: ${subtotalDisplay?.textContent || 'none'}`);
        
        if (bahanSelect && bahanSelect.value) {
            const option = bahanSelect.options[bahanSelect.selectedIndex];
            info.push(`  Harga data: ${option?.dataset?.harga || 'missing'}`);
            info.push(`  Satuan data: ${option?.dataset?.satuan || 'missing'}`);
            info.push(`  Sub satuan: ${option?.dataset?.subSatuan ? 'available' : 'missing'}`);
            
            // Try manual trigger
            try {
                updateConversionDisplay(row, option);
                calculateRowSubtotal(row);
                info.push(`  Manual trigger: ✅ SUCCESS`);
            } catch (error) {
                info.push(`  Manual trigger: ❌ ERROR - ${error.message}`);
            }
        }
    });
    
    // Check BP rows
    bpRows.forEach((row, index) => {
        if (row.id === 'newBahanPendukungRow') return;
        
        const bahanSelect = row.querySelector(".bahan-pendukung-select");
        const qtyInput = row.querySelector(".qty-input");
        const satuanSelect = row.querySelector(".satuan-select");
        const subtotalDisplay = row.querySelector(".subtotal-display");
        const hargaDisplay = row.querySelector(".harga-utama");
        const hargaKonversiDiv = row.querySelector(".harga-konversi");
        
        info.push(`BP Row ${index}:`);
        info.push(`  Bahan: ${bahanSelect?.value || 'none'} (${bahanSelect?.options[bahanSelect?.selectedIndex]?.text || 'none'})`);
        info.push(`  Qty: ${qtyInput?.value || 'none'}`);
        info.push(`  Satuan: ${satuanSelect?.value || 'none'}`);
        info.push(`  Harga Display: ${hargaDisplay?.innerHTML || 'none'}`);
        info.push(`  Konversi Display: ${hargaKonversiDiv?.innerHTML ? 'has content' : 'empty'}`);
        info.push(`  Subtotal: ${subtotalDisplay?.textContent || 'none'}`);
        
        if (bahanSelect && bahanSelect.value) {
            const option = bahanSelect.options[bahanSelect.selectedIndex];
            info.push(`  Harga data: ${option?.dataset?.harga || 'missing'}`);
            info.push(`  Satuan data: ${option?.dataset?.satuan || 'missing'}`);
            info.push(`  Sub satuan: ${option?.dataset?.subSatuan ? 'available' : 'missing'}`);
            
            // Try manual trigger
            try {
                updateConversionDisplay(row, option);
                calculateRowSubtotal(row);
                info.push(`  Manual trigger: ✅ SUCCESS`);
            } catch (error) {
                info.push(`  Manual trigger: ❌ ERROR - ${error.message}`);
            }
        }
    });
    
    // Force recalculate totals
    info.push("=== FORCING RECALCULATION ===");
    try {
        calculateTotals();
        info.push("✅ Totals recalculated");
        
        // Check final totals
        const totalBBElement = document.getElementById("totalBahanBaku");
        const totalBPElement = document.getElementById("totalBahanPendukung");
        const summaryTotalElement = document.getElementById("summaryTotalBiaya");
        
        info.push(`Final BB Total: ${totalBBElement?.textContent || 'missing'}`);
        info.push(`Final BP Total: ${totalBPElement?.textContent || 'missing'}`);
        info.push(`Final Summary Total: ${summaryTotalElement?.textContent || 'missing'}`);
        
    } catch (error) {
        info.push(`❌ Totals error: ${error.message}`);
    }
    
    if (testResult) {
        testResult.innerHTML = `<div class="alert alert-info" style="font-family: monospace; font-size: 11px; max-height: 400px; overflow-y: auto;">${info.join('<br>')}</div>`;
    }
    
    console.log("🚨 EDIT DEBUG INFO:", info);
}

// Make functions global
window.addBahanBakuRow = addBahanBakuRow;
window.addBahanPendukungRow = addBahanPendukungRow;
window.emergencyDebug = emergencyDebug;
window.testConversionFunction = testConversionFunction;
window.testSubtotalCalculation = testSubtotalCalculation;
window.updateConversionDisplay = updateConversionDisplay;
window.calculateRowSubtotal = calculateRowSubtotal;
window.calculateTotals = calculateTotals;
window.forceEditInitialization = forceEditInitialization;

// Initialize when DOM ready - ENHANCED FOR EDIT
document.addEventListener("DOMContentLoaded", function() {
    console.log("🎯 DOM Ready - Initializing EDIT PAGE");
    
    // Attach button listeners
    const addBBBtn = document.getElementById("addBahanBaku");
    const addBPBtn = document.getElementById("addBahanPendukung");
    
    if (addBBBtn) {
        addBBBtn.addEventListener("click", function(e) {
            e.preventDefault();
            addBahanBakuRow();
        });
        console.log("✅ BB button attached");
    }
    
    if (addBPBtn) {
        addBPBtn.addEventListener("click", function(e) {
            e.preventDefault();
            addBahanPendukungRow();
        });
        console.log("✅ BP button attached");
    }
    
    // Initialize existing rows with event listeners FIRST
    document.querySelectorAll("#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)").forEach(row => {
        addRowEventListeners(row);
        console.log("✅ Initialized existing BB row");
    });
    
    document.querySelectorAll("#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)").forEach(row => {
        addRowEventListeners(row);
        console.log("✅ Initialized existing BP row");
    });
    
    // Initialize existing data - CRITICAL FIX FOR EDIT PAGE
    setTimeout(() => {
        console.log("🔄 EDIT PAGE: Initializing existing data conversions...");
        
        let processedRows = 0;
        let totalProcessed = 0;
        
        // Process ALL rows (both BB and BP) with comprehensive initialization
        const allRows = document.querySelectorAll("#bahanBakuTable tbody tr:not(#newBahanBakuRow), #bahanPendukungTable tbody tr:not(#newBahanPendukungRow)");
        
        console.log(`🔍 Found ${allRows.length} total rows to process`);
        
        allRows.forEach((row, index) => {
            const bahanSelect = row.querySelector(".bahan-baku-select, .bahan-pendukung-select");
            const qtyInput = row.querySelector(".qty-input");
            const satuanSelect = row.querySelector(".satuan-select");
            const hargaDisplay = row.querySelector(".harga-utama");
            const subtotalDisplay = row.querySelector(".subtotal-display");
            
            console.log(`🔍 Row ${index}:`, {
                hasSelect: !!bahanSelect,
                selectValue: bahanSelect?.value || 'none',
                hasQty: !!qtyInput,
                qtyValue: qtyInput?.value || 'none',
                hasSatuan: !!satuanSelect,
                satuanValue: satuanSelect?.value || 'none'
            });
            
            // Process rows with selected bahan
            if (bahanSelect && bahanSelect.value && qtyInput && satuanSelect) {
                const option = bahanSelect.options[bahanSelect.selectedIndex];
                
                if (option && option.dataset.harga) {
                    console.log("✅ Processing existing row:", {
                        bahan: option.text,
                        bahanId: bahanSelect.value,
                        qty: qtyInput.value,
                        satuan: satuanSelect.value,
                        harga: option.dataset.harga,
                        subSatuan: option.dataset.subSatuan ? 'available' : 'missing'
                    });
                    
                    // 1. Update harga display
                    if (hargaDisplay) {
                        const harga = parseInt(option.dataset.harga);
                        hargaDisplay.innerHTML = `<strong>${formatRupiah(harga)}</strong>`;
                        console.log("✅ Updated harga display:", formatRupiah(harga));
                    }
                    
                    // 2. Trigger conversion display
                    updateConversionDisplay(row, option);
                    
                    // 3. Calculate subtotal - CRITICAL
                    calculateRowSubtotal(row);
                    
                    processedRows++;
                } else {
                    console.log("⚠️ Row has select but missing data:", {
                        hasOption: !!option,
                        hasHarga: !!(option?.dataset?.harga)
                    });
                }
            } else {
                console.log("⚠️ Row missing required elements:", {
                    hasSelect: !!bahanSelect,
                    selectValue: bahanSelect?.value || 'none',
                    hasQty: !!qtyInput,
                    hasSatuan: !!satuanSelect
                });
            }
            
            totalProcessed++;
        });
        
        console.log(`✅ EDIT INIT COMPLETE: Processed ${processedRows}/${totalProcessed} rows`);
        
        // Force calculate totals after all rows processed
        setTimeout(() => {
            console.log("🧮 EDIT PAGE: Force calculating totals...");
            calculateTotals();
            
            // Verify final state
            const totalBBElement = document.getElementById("totalBahanBaku");
            const totalBPElement = document.getElementById("totalBahanPendukung");
            const summaryTotalElement = document.getElementById("summaryTotalBiaya");
            
            console.log("📊 EDIT PAGE Final totals:", {
                totalBB: totalBBElement?.textContent || 'missing',
                totalBP: totalBPElement?.textContent || 'missing',
                summaryTotal: summaryTotalElement?.textContent || 'missing'
            });
            
            // If totals are still 0, force emergency recalculation
            if (summaryTotalElement?.textContent?.includes('Rp 0')) {
                console.log("🚨 EDIT PAGE: Totals still 0, running emergency recalculation...");
                setTimeout(() => {
                    emergencyDebug();
                }, 100);
            }
            
        }, 400);
        
    }, 800);
    
    console.log("✅ EDIT PAGE Initialization complete");
});

console.log("🎉 BIAYA BAHAN EDIT SCRIPT LOADED SUCCESSFULLY");
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.btn-group-sm .btn {
    margin: 0 2px;
}

.form-control-sm {
    font-size: 0.875rem;
}

.subtotal-display {
    font-weight: 600;
    color: #28a745;
}

#summaryTotalBiaya, #summaryHargaJual {
    font-size: 1.1rem;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/biaya-bahan/edit.blade.php ENDPATH**/ ?>