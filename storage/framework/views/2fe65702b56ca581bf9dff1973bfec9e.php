

<?php $__env->startSection('content'); ?>
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
                                                ?>
                                                <option value="<?php echo e($bahanBaku->id); ?>" 
                                                        data-harga="<?php echo e($bahanBaku->harga_satuan); ?>"
                                                        data-satuan="<?php echo e($satuanBB); ?>"
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
                                    <td class="text-end harga-display" style="width: 150px;">
                                        Rp <?php echo e(number_format($detail->harga_per_satuan, 0, ',', '.')); ?>

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
                                            ?>
                                            <option value="<?php echo e($bahanBaku->id); ?>" 
                                                    data-harga="<?php echo e($bahanBaku->harga_satuan); ?>"
                                                    data-satuan="<?php echo e($satuanBB); ?>">
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
                                <td class="text-end harga-display" style="width: 150px;">-</td>
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
                                                ?>
                                                <option value="<?php echo e($bahanPendukung->id); ?>" 
                                                        data-harga="<?php echo e($bahanPendukung->harga_satuan); ?>"
                                                        data-satuan="<?php echo e($satuanBP); ?>"
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
                                    <td class="text-end harga-display" style="width: 150px;">
                                        Rp <?php echo e(number_format($pendukung->harga_satuan, 0, ',', '.')); ?>

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
                                            ?>
                                            <option value="<?php echo e($bahanPendukung->id); ?>" 
                                                    data-harga="<?php echo e($bahanPendukung->harga_satuan); ?>"
                                                    data-satuan="<?php echo e($satuanBP); ?>">
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
                                <td class="text-end harga-display" style="width: 150px;">-</td>
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
<script src="<?php echo e(asset('js/biaya-bahan-edit.js')); ?>"></script>
<script>
console.log('=== Biaya Bahan Edit - Script loaded ===');

// Additional initialization untuk edit page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Edit page specific initialization...');
    
    // Tambah validasi form submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMIT STARTED ===');
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            
            // Remove any existing alerts first
            document.querySelectorAll('.alert-danger').forEach(alert => alert.remove());
            
            // Cek apakah ada minimal 1 item yang valid
            const bbRows = document.querySelectorAll('#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)');
            const bpRows = document.querySelectorAll('#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)');
            
            console.log('Total BB rows:', bbRows.length);
            console.log('Total BP rows:', bpRows.length);
            
            let validBB = 0;
            let validBP = 0;
            
            bbRows.forEach((row, index) => {
                const select = row.querySelector('.bahan-baku-select');
                const qty = row.querySelector('.qty-input');
                const isSelected = select && select.value !== '';
                const hasQty = qty && qty.value && parseFloat(qty.value) > 0;
                
                console.log(`BB Row ${index}: selected=${isSelected}, qty=${hasQty}, value="${qty?.value}"`);
                
                if (isSelected && hasQty) {
                    validBB++;
                }
            });
            
            bpRows.forEach((row, index) => {
                const select = row.querySelector('.bahan-pendukung-select');
                const qty = row.querySelector('.qty-input');
                const isSelected = select && select.value !== '';
                const hasQty = qty && qty.value && parseFloat(qty.value) > 0;
                
                console.log(`BP Row ${index}: selected=${isSelected}, qty=${hasQty}, value="${qty?.value}"`);
                
                if (isSelected && hasQty) {
                    validBP++;
                }
            });
            
            console.log(`Valid items: ${validBB} BB, ${validBP} BP`);
            
            if (validBB === 0 && validBP === 0) {
                console.log('VALIDATION FAILED: No valid items');
                e.preventDefault();
                
                // Create and show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger alert-dismissible fade show';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Validation Error!</strong> Minimal harus ada 1 bahan baku atau bahan pendukung yang dipilih dan diisi dengan benar.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                // Insert at the top of the form
                form.parentNode.insertBefore(errorDiv, form);
                
                // Scroll to top to show the error
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                return false;
            }
            
            // Additional validation for quantities
            let hasInvalidQty = false;
            const qtyInputs = form.querySelectorAll('.qty-input');
            qtyInputs.forEach((input, index) => {
                const row = input.closest('tr');
                const select = row.querySelector('.bahan-baku-select, .bahan-pendukung-select');
                const isItemSelected = select && select.value !== '';
                
                if (isItemSelected && (!input.value || parseFloat(input.value) <= 0)) {
                    hasInvalidQty = true;
                    input.classList.add('is-invalid');
                    console.log(`Invalid qty at index ${index}: value="${input.value}", selected item="${select.value}"`);
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (hasInvalidQty) {
                console.log('VALIDATION FAILED: Invalid quantities');
                e.preventDefault();
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger alert-dismissible fade show';
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Validation Error!</strong> Semua jumlah bahan harus diisi dengan angka yang valid (lebih dari 0).
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                form.parentNode.insertBefore(errorDiv, form);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                return false;
            }
            
            console.log('=== VALIDATION PASSED ===');
            console.log(`Form will submit to: ${form.action}`);
            
            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';
                console.log('Submit button disabled and showing loading state');
            }
            
            // Let the form submit normally
            return true;
        });
    }
    
    // Initialize existing data
    setTimeout(() => {
        console.log('Initializing existing data...');
        
        // Trigger calculation untuk existing rows
        document.querySelectorAll('.bahan-baku-select, .bahan-pendukung-select').forEach(select => {
            if (select.value) {
                const event = new Event('change');
                select.dispatchEvent(event);
            }
        });
        
        // Final calculation
        if (typeof calculateTotals === 'function') {
            calculateTotals();
        }
    }, 500);
});
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