

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-dark">
            <i class="fas fa-plus me-2"></i>Tambah Biaya Bahan
            <small class="text-muted fw-normal">- <?php echo e($produk->nama_produk); ?></small>
        </h2>
        <div class="btn-group">
            <a href="<?php echo e(route('master-data.biaya-bahan.index')); ?>" class="btn btn-secondary">
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
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('master-data.biaya-bahan.store', $produk->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <!-- Bahan Baku Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h6 class="mb-0">
                    <i class="fas fa-cube me-2"></i>1. Biaya Bahan Baku (BBB)
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
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Simpan Biaya Bahan
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
console.log('=== Biaya Bahan Create - Script loaded ===');

// Pastikan fungsi global tersedia
if (typeof window.addBahanBakuRow !== 'function') {
    console.error('addBahanBakuRow function not found!');
}

if (typeof window.addBahanPendukungRow !== 'function') {
    console.error('addBahanPendukungRow function not found!');
}

// Additional initialization untuk create page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Create page specific initialization...');
    
    // Tambah validasi form submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submit validation...');
            
            // Cek apakah ada minimal 1 item yang valid
            const validBB = document.querySelectorAll('#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none) .bahan-baku-select[value!=""]').length;
            const validBP = document.querySelectorAll('#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none) .bahan-pendukung-select[value!=""]').length;
            
            if (validBB === 0 && validBP === 0) {
                e.preventDefault();
                alert('Minimal harus ada 1 bahan baku atau bahan pendukung yang dipilih!');
                return false;
            }
            
            console.log(`Form validation passed: ${validBB} BB items, ${validBP} BP items`);
        });
    }
    
    // Auto-add first row jika belum ada
    setTimeout(() => {
        const existingBBRows = document.querySelectorAll('#bahanBakuTable tbody tr:not(#newBahanBakuRow):not(.d-none)').length;
        const existingBPRows = document.querySelectorAll('#bahanPendukungTable tbody tr:not(#newBahanPendukungRow):not(.d-none)').length;
        
        if (existingBBRows === 0 && existingBPRows === 0) {
            console.log('No existing rows found, adding first Bahan Baku row...');
            if (typeof window.addBahanBakuRow === 'function') {
                window.addBahanBakuRow();
            }
        }
    }, 500);
});
</script>

<script>
function attachEventListeners(row) {
    const targetRow = row || document;
    
    // Attach event listeners to selects
    targetRow.querySelectorAll('.bahan-baku-select, .bahan-pendukung-select').forEach(select => {
        select.addEventListener('change', function() {
            console.log('Select changed, calculating totals...');
            
            // Auto-fill satuan based on selected item
            const option = select.options[select.selectedIndex];
            if (option && option.dataset.satuan) {
                const satuanSelect = targetRow.querySelector('.satuan-select');
                if (satuanSelect) {
                    satuanSelect.value = option.dataset.satuan;
                    console.log('Auto-filled satuan:', option.dataset.satuan);
                }
            }
            
            calculateTotals();
        });
    });
    
    // Attach event listeners to quantity inputs
    targetRow.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', function() {
            console.log('Quantity changed, calculating totals...');
            calculateTotals();
        });
        
        // Also trigger on change for better compatibility
        input.addEventListener('change', function() {
            console.log('Quantity changed (change event), calculating totals...');
            calculateTotals();
        });
        
        // Also trigger on keyup for immediate response
        input.addEventListener('keyup', function() {
            console.log('Quantity changed (keyup event), calculating totals...');
            calculateTotals();
        });
    });
    
    // Attach event listeners to satuan selects
    targetRow.querySelectorAll('.satuan-select').forEach(select => {
        select.addEventListener('change', function() {
            console.log('Satuan changed, calculating totals...');
            calculateTotals();
        });
    });
}

function calculateSubtotal(qty, satuanDipilih, satuanBahan, hargaSatuan) {
    const satuanDipilihLower = satuanDipilih.toLowerCase().trim();
    const satuanBahanLower = satuanBahan.toLowerCase().trim();
    
    // Same satuan, no conversion needed
    if (satuanDipilihLower === satuanBahanLower) {
        return qty * hargaSatuan;
    }
    
    // Conversion to base unit (gram for mass, ml for volume)
    // This matches PHP UnitConverter logic
    const toBaseUnit = {
        // Mass (base: gram)
        'kg': 1000, 'kilogram': 1000,
        'g': 1, 'gram': 1, 'gr': 1,
        'mg': 0.001, 'miligram': 0.001,
        'ons': 100,
        // Volume (base: ml)
        'l': 1000, 'liter': 1000, 'ltr': 1000,
        'ml': 1, 'mililiter': 1, 'milliliter': 1,
        'sdt': 5, 'sendok_teh': 5,
        'sdm': 15, 'sendok_makan': 15,
        'cup': 240,
        // Count (base: pcs)
        'pcs': 1, 'buah': 1, 'butir': 1, 'biji': 1, 'unit': 1, 'pieces': 1
    };

    const fromFactor = toBaseUnit[satuanDipilihLower] || 1;
    const toFactor = toBaseUnit[satuanBahanLower] || 1;
    
    // Convert qty from satuanDipilih to satuanBahan
    // Example: 500 gram to kg = 500 * 1 / 1000 = 0.5 kg
    // Then: 0.5 kg * Rp 50.000/kg = Rp 25.000
    const qtyInSatuanBahan = qty * fromFactor / toFactor;
    return qtyInSatuanBahan * hargaSatuan;
}

function calculateTotals() {
    let totalBahanBaku = 0;
    let totalBahanPendukung = 0;

    // Calculate Bahan Baku
    document.querySelectorAll('#bahanBakuTable tbody tr:not(#newBahanBakuRow)').forEach(row => {
        if (row.classList.contains('d-none')) return;
        
        const select = row.querySelector('.bahan-baku-select');
        const qtyInput = row.querySelector('.qty-input');
        const satuanSelect = row.querySelector('.satuan-select');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        
        if (select && select.value && qtyInput && satuanSelect && satuanSelect.value) {
            const option = select.options[select.selectedIndex];
            const harga = parseFloat(option.dataset.harga) || 0;
            const satuanBahan = option.dataset.satuan || 'unit';
            const qty = parseFloat(qtyInput.value) || 0;
            const satuan = satuanSelect.value;
            
            const subtotal = calculateSubtotal(qty, satuan, satuanBahan, harga);
            
            if (subtotalDisplay) {
                subtotalDisplay.innerHTML = '<strong>Rp ' + Math.round(subtotal).toLocaleString('id-ID') + '</strong>';
            }
            
            totalBahanBaku += subtotal;
        }
    });

    // Calculate Bahan Pendukung
    document.querySelectorAll('#bahanPendukungTable tbody tr:not(#newBahanPendukungRow)').forEach(row => {
        if (row.classList.contains('d-none')) return;
        
        const select = row.querySelector('.bahan-pendukung-select');
        const qtyInput = row.querySelector('.qty-input');
        const satuanSelect = row.querySelector('.satuan-select');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        
        if (select && select.value && qtyInput && satuanSelect && satuanSelect.value) {
            const option = select.options[select.selectedIndex];
            const harga = parseFloat(option.dataset.harga) || 0;
            const satuanBahan = option.dataset.satuan || 'unit';
            const qty = parseFloat(qtyInput.value) || 0;
            const satuan = satuanSelect.value;
            
            const subtotal = calculateSubtotal(qty, satuan, satuanBahan, harga);
            
            if (subtotalDisplay) {
                subtotalDisplay.innerHTML = '<strong>Rp ' + Math.round(subtotal).toLocaleString('id-ID') + '</strong>';
            }
            
            totalBahanPendukung += subtotal;
        }
    });

    const totalBiaya = totalBahanBaku + totalBahanPendukung;

    document.getElementById('totalBahanBaku').textContent = 'Rp ' + Math.round(totalBahanBaku).toLocaleString('id-ID');
    document.getElementById('totalBahanPendukung').textContent = 'Rp ' + Math.round(totalBahanPendukung).toLocaleString('id-ID');
    document.getElementById('summaryBahanBaku').textContent = 'Rp ' + Math.round(totalBahanBaku).toLocaleString('id-ID');
    document.getElementById('summaryBahanPendukung').textContent = 'Rp ' + Math.round(totalBahanPendukung).toLocaleString('id-ID');
    document.getElementById('summaryTotalBiaya').textContent = 'Rp ' + Math.round(totalBiaya).toLocaleString('id-ID');
    
    console.log('Totals:', { baku: totalBahanBaku, pendukung: totalBahanPendukung, total: totalBiaya });
}

// Define global functions for onclick handlers
window.addBahanBakuRow = function() {
    console.log('=== window.addBahanBakuRow called ===');
    const newRow = document.getElementById('newBahanBakuRow');
    if (!newRow) {
        console.error('ERROR: newBahanBakuRow not found!');
        alert('Error: Template row tidak ditemukan!');
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove('d-none');
    clone.id = 'bahanBaku_' + Date.now();
    
    const timestamp = Date.now();
    clone.querySelectorAll('[name^="bahan_baku[new]"]').forEach(input => {
        const fieldName = input.name.match(/\[new\]\[(\w+)\]/)[1];
        input.name = `bahan_baku[${timestamp}][${fieldName}]`;
        input.value = '';
    });
    
    tbody.insertBefore(clone, newRow);
    console.log('Row inserted! ID:', clone.id);
    
    attachEventListeners(clone);
    calculateTotals();
    return false;
};

window.addBahanPendukungRow = function() {
    console.log('=== window.addBahanPendukungRow called ===');
    const newRow = document.getElementById('newBahanPendukungRow');
    if (!newRow) {
        console.error('ERROR: newBahanPendukungRow not found!');
        alert('Error: Template row tidak ditemukan!');
        return false;
    }
    
    const tbody = newRow.parentElement;
    const clone = newRow.cloneNode(true);
    clone.classList.remove('d-none');
    clone.id = 'bahanPendukung_' + Date.now();
    
    const timestamp = Date.now();
    clone.querySelectorAll('[name^="bahan_pendukung[new]"]').forEach(input => {
        const fieldName = input.name.match(/\[new\]\[(\w+)\]/)[1];
        input.name = `bahan_pendukung[${timestamp}][${fieldName}]`;
        input.value = '';
    });
    
    tbody.insertBefore(clone, newRow);
    console.log('Row inserted! ID:', clone.id);
    
    attachEventListeners(clone);
    calculateTotals();
    return false;
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM loaded, initializing... ===');
    
    // Initialize - attach to all existing rows
    console.log('Attaching event listeners to all rows...');
    attachEventListeners(null);
    
    calculateTotals();
    console.log('Initialization complete');
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/biaya-bahan/create.blade.php ENDPATH**/ ?>