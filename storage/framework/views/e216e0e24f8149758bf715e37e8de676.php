<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-edit me-2"></i>Edit BOM: <?php echo e($bom->produk->nama_produk); ?></h3>
        <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <form action="<?php echo e(route('master-data.bom.update', $bom->id)); ?>" method="POST" id="bomForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="produk_id" value="<?php echo e($bom->produk_id); ?>">
        
        <!-- Informasi Produk -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>Informasi Produk</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Produk</label>
                        <input type="text" class="form-control" value="<?php echo e($bom->produk->nama_produk); ?>" readonly>
                        <small class="text-muted">Produk yang sedang diedit BOM-nya</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Biaya Bahan -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>1. Biaya Bahan (Read-Only)</h5>
                <small>Data mutlak dari halaman Biaya Bahan - tidak dapat diedit</small>
            </div>
            <div class="card-body">
                <div class="alert alert-primary">
                    <i class="fas fa-lock me-2"></i>
                    <strong>Data Mutlak:</strong> Biaya bahan diambil langsung dari perhitungan di halaman Biaya Bahan. 
                    Data ini tidak dapat diedit dan merupakan hasil perhitungan final dari sistem biaya bahan.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="biayaBahanTable">
                        <thead class="table-light">
                            <tr>
                                <th width="30%">Nama Bahan</th>
                                <th width="15%">Kategori</th>
                                <th width="15%">Jumlah</th>
                                <th width="10%">Satuan</th>
                                <th width="15%">Harga Satuan</th>
                                <th width="15%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="biayaBahanTableBody">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $biayaBahan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bahan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo e($bahan['nama']); ?></div>
                                    <small class="text-muted"><?php echo e($bahan['kode']); ?></small>
                                    <input type="hidden" name="bahan_id[]" value="<?php echo e($bahan['id']); ?>">
                                </td>
                                <td>
                                    <span class="badge <?php echo e($bahan['kategori'] === 'Bahan Baku' ? 'bg-primary' : 'bg-info'); ?>">
                                        <?php echo e($bahan['kategori']); ?>

                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?php echo e(number_format($bahan['jumlah'] ?? 1, 2)); ?></span>
                                    <input type="hidden" name="bahan_jumlah[]" value="<?php echo e($bahan['jumlah'] ?? 1); ?>">
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo e($bahan['satuan']); ?></span>
                                </td>
                                <td>
                                    <span class="text-success">Rp <?php echo e(number_format($bahan['harga'], 0, ',', '.')); ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary">
                                        Rp <?php echo e(number_format($bahan['harga'] * ($bahan['jumlah'] ?? 1), 0, ',', '.')); ?>

                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="5" class="text-end">Total Biaya Bahan (Mutlak):</th>
                                <th>
                                    <?php
                                        $totalBiayaBahan = $biayaBahan->sum(function($bahan) {
                                            return $bahan['harga'] * ($bahan['jumlah'] ?? 1);
                                        });
                                    ?>
                                    <span id="totalBiayaBahan">Rp <?php echo e(number_format($totalBiayaBahan, 0, ',', '.')); ?></span>
                                    <input type="hidden" name="total_biaya_bahan" id="totalBiayaBahanInput" value="<?php echo e($totalBiayaBahan); ?>">
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($biayaBahan->isEmpty()): ?>
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="text-warning">Belum Ada Data Biaya Bahan</h5>
                    <p class="text-muted">Silakan lengkapi data di halaman Biaya Bahan terlebih dahulu</p>
                    <a href="<?php echo e(route('master-data.biaya-bahan.index')); ?>" class="btn btn-warning">
                        <i class="fas fa-arrow-right me-2"></i>Ke Halaman Biaya Bahan
                    </a>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- Section 2: BTKL (Biaya Tenaga Kerja Langsung) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>2. BTKL (Biaya Tenaga Kerja Langsung)</h5>
                <small>Biaya per produk dihitung otomatis berdasarkan jam proses</small>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Informasi:</strong> Nominal biaya per produk untuk 1 jam proses sudah dihitung otomatis. 
                    Anda hanya perlu memasukkan berapa jam yang dibutuhkan untuk setiap proses.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="btklTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <button type="button" class="btn btn-success btn-sm" onclick="addBtklRow()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </th>
                                <th width="25%">Nama Proses</th>
                                <th width="15%">Biaya per Jam</th>
                                <th width="15%">Jam Dibutuhkan</th>
                                <th width="15%">Kapasitas per Jam</th>
                                <th width="15%">Biaya per Produk</th>
                                <th width="10%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="btklTableBody">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bom->proses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeBtklRow(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                <td>
                                    <select name="proses_id[]" class="form-select proses-select" onchange="updateProsesData(this)" required>
                                        <option value="">-- Pilih Proses --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $prosesProduksis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prosesData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($prosesData->id); ?>" 
                                                data-tarif="<?php echo e($prosesData->tarif_per_jam); ?>" 
                                                data-kapasitas="<?php echo e($prosesData->kapasitas_per_jam); ?>"
                                                <?php echo e($proses->proses_produksi_id == $prosesData->id ? 'selected' : ''); ?>>
                                                <?php echo e($prosesData->nama_proses); ?> (<?php echo e($prosesData->kode_proses); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </td>
                                <td>
                                    <span class="biaya-per-jam-text">Rp <?php echo e(number_format($proses->prosesProduksi->tarif_per_jam ?? 0, 0, ',', '.')); ?></span>
                                </td>
                                <td>
                                    <input type="number" name="jam_dibutuhkan[]" class="form-control jam-input" 
                                           step="0.1" min="0" value="<?php echo e($proses->durasi); ?>" 
                                           onchange="calculateBtklSubtotal(this)" required>
                                </td>
                                <td>
                                    <span class="kapasitas-text"><?php echo e($proses->kapasitas_per_jam ?? 0); ?> unit/jam</span>
                                </td>
                                <td>
                                    <span class="biaya-per-produk-text">Rp <?php echo e(number_format($proses->biaya_btkl, 0, ',', '.')); ?></span>
                                </td>
                                <td>
                                    <span class="subtotal-btkl-text">Rp <?php echo e(number_format($proses->biaya_btkl, 0, ',', '.')); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-success">
                                <th colspan="6" class="text-end">Total BTKL:</th>
                                <th>
                                    <span id="totalBtkl">Rp <?php echo e(number_format($bom->total_btkl, 0, ',', '.')); ?></span>
                                    <input type="hidden" name="total_btkl" id="totalBtklInput" value="<?php echo e($bom->total_btkl); ?>">
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 3: BOP (Biaya Overhead Pabrik) -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-industry me-2"></i>3. BOP (Biaya Overhead Pabrik)</h5>
                <small>Input manual sementara (halaman BOP masih dalam pengembangan)</small>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Catatan:</strong> Untuk sementara, BOP diinput manual karena halaman BOP masih dalam tahap penyempurnaan.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="bopTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="addBopRow()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </th>
                                <th width="30%">Nama BOP</th>
                                <th width="20%">Biaya per Unit</th>
                                <th width="15%">Jumlah Unit</th>
                                <th width="30%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="bopTableBody">
                            <!-- BOP data will be loaded from existing data or added manually -->
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bom->total_bop > 0): ?>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeBopRow(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                <td>
                                    <input type="text" name="bop_nama[]" class="form-control" value="BOP Existing" required>
                                </td>
                                <td>
                                    <input type="number" name="bop_biaya_per_unit[]" class="form-control biaya-per-unit-input" 
                                           step="0.01" min="0" value="<?php echo e($bom->total_bop); ?>" 
                                           onchange="calculateBopSubtotal(this)" required>
                                </td>
                                <td>
                                    <input type="number" name="bop_jumlah_unit[]" class="form-control jumlah-unit-input" 
                                           step="0.01" min="0" value="1" 
                                           onchange="calculateBopSubtotal(this)" required>
                                </td>
                                <td>
                                    <span class="subtotal-bop-text">Rp <?php echo e(number_format($bom->total_bop, 0, ',', '.')); ?></span>
                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-warning">
                                <th colspan="4" class="text-end">Total BOP:</th>
                                <th>
                                    <span id="totalBop">Rp <?php echo e(number_format($bom->total_bop, 0, ',', '.')); ?></span>
                                    <input type="hidden" name="total_bop" id="totalBopInput" value="<?php echo e($bom->total_bop); ?>">
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Ringkasan HPP (Harga Pokok Produksi)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-primary text-white rounded">
                            <h6>Biaya Bahan</h6>
                            <h4 id="summaryBiayaBahan">Rp <?php echo e(number_format($bom->total_bbb, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-success text-white rounded">
                            <h6>Total BTKL</h6>
                            <h4 id="summaryBtkl">Rp <?php echo e(number_format($bom->total_btkl, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-warning text-dark rounded">
                            <h6>Total BOP</h6>
                            <h4 id="summaryBop">Rp <?php echo e(number_format($bom->total_bop, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-dark text-white rounded">
                            <h6>Total HPP</h6>
                            <h4 id="summaryHpp">Rp <?php echo e(number_format($bom->total_hpp, 0, ',', '.')); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end gap-2">
            <a href="<?php echo e(route('master-data.bom.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Update BOM
            </button>
        </div>
    </form>
</div>

<!-- Data untuk JavaScript -->
<script>
    const biayaBahanData = <?php echo json_encode($biayaBahan, 15, 512) ?>;
    const prosesProduksiData = <?php echo json_encode($prosesProduksis, 15, 512) ?>;
</script>

<script>
let btklRowIndex = <?php echo e($bom->proses->count()); ?>;
let bopRowIndex = <?php echo e($bom->total_bop > 0 ? 1 : 0); ?>;

// Add BTKL Row
function addBtklRow() {
    const tbody = document.getElementById('btklTableBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeBtklRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
        <td>
            <select name="proses_id[]" class="form-select proses-select" onchange="updateProsesData(this)" required>
                <option value="">-- Pilih Proses --</option>
                ${prosesProduksiData.map(proses => 
                    `<option value="${proses.id}" data-tarif="${proses.tarif_per_jam}" data-kapasitas="${proses.kapasitas_per_jam}">
                        ${proses.nama_proses} (${proses.kode_proses})
                    </option>`
                ).join('')}
            </select>
        </td>
        <td>
            <span class="biaya-per-jam-text">Rp 0</span>
        </td>
        <td>
            <input type="number" name="jam_dibutuhkan[]" class="form-control jam-input" step="0.1" min="0" onchange="calculateBtklSubtotal(this)" required>
        </td>
        <td>
            <span class="kapasitas-text">0 unit/jam</span>
        </td>
        <td>
            <span class="biaya-per-produk-text">Rp 0</span>
        </td>
        <td>
            <span class="subtotal-btkl-text">Rp 0</span>
        </td>
    `;
    tbody.appendChild(row);
    btklRowIndex++;
}

// Add BOP Row
function addBopRow() {
    const tbody = document.getElementById('bopTableBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeBopRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
        <td>
            <input type="text" name="bop_nama[]" class="form-control" placeholder="Nama BOP" required>
        </td>
        <td>
            <input type="number" name="bop_biaya_per_unit[]" class="form-control biaya-per-unit-input" step="0.01" min="0" onchange="calculateBopSubtotal(this)" required>
        </td>
        <td>
            <input type="number" name="bop_jumlah_unit[]" class="form-control jumlah-unit-input" step="0.01" min="0" onchange="calculateBopSubtotal(this)" required>
        </td>
        <td>
            <span class="subtotal-bop-text">Rp 0</span>
        </td>
    `;
    tbody.appendChild(row);
    bopRowIndex++;
}

// Remove functions
function removeBtklRow(button) {
    button.closest('tr').remove();
    calculateTotalBtkl();
    updateSummary();
}

function removeBopRow(button) {
    button.closest('tr').remove();
    calculateTotalBop();
    updateSummary();
}

// Update functions
function updateProsesData(select) {
    const row = select.closest('tr');
    const option = select.selectedOptions[0];
    
    if (option.value) {
        const tarif = parseFloat(option.dataset.tarif);
        const kapasitas = parseInt(option.dataset.kapasitas);
        
        row.querySelector('.biaya-per-jam-text').textContent = formatRupiah(tarif);
        row.querySelector('.kapasitas-text').textContent = kapasitas + ' unit/jam';
        
        calculateBtklSubtotal(row.querySelector('.jam-input'));
    }
}

// Calculate functions
function calculateBtklSubtotal(input) {
    const row = input.closest('tr');
    const select = row.querySelector('.proses-select');
    const option = select.selectedOptions[0];
    
    if (option && option.value) {
        const tarif = parseFloat(option.dataset.tarif);
        const kapasitas = parseInt(option.dataset.kapasitas);
        const jam = parseFloat(input.value) || 0;
        
        // Biaya per produk = (jam * tarif) / kapasitas
        const biayaPerProduk = kapasitas > 0 ? (jam * tarif) / kapasitas : 0;
        const subtotal = biayaPerProduk;
        
        row.querySelector('.biaya-per-produk-text').textContent = formatRupiah(biayaPerProduk);
        row.querySelector('.subtotal-btkl-text').textContent = formatRupiah(subtotal);
        
        calculateTotalBtkl();
        updateSummary();
    }
}

function calculateBopSubtotal(input) {
    const row = input.closest('tr');
    const biayaPerUnit = parseFloat(row.querySelector('.biaya-per-unit-input').value) || 0;
    const jumlahUnit = parseFloat(row.querySelector('.jumlah-unit-input').value) || 0;
    const subtotal = biayaPerUnit * jumlahUnit;
    
    row.querySelector('.subtotal-bop-text').textContent = formatRupiah(subtotal);
    calculateTotalBop();
    updateSummary();
}

// Total calculations
function calculateTotalBtkl() {
    let total = 0;
    document.querySelectorAll('#btklTableBody .subtotal-btkl-text').forEach(span => {
        const value = span.textContent.replace(/[^\d]/g, '');
        total += parseFloat(value) || 0;
    });
    
    document.getElementById('totalBtkl').textContent = formatRupiah(total);
    document.getElementById('totalBtklInput').value = total;
}

function calculateTotalBop() {
    let total = 0;
    document.querySelectorAll('#bopTableBody .subtotal-bop-text').forEach(span => {
        const value = span.textContent.replace(/[^\d]/g, '');
        total += parseFloat(value) || 0;
    });
    
    document.getElementById('totalBop').textContent = formatRupiah(total);
    document.getElementById('totalBopInput').value = total;
}

function updateSummary() {
    const biayaBahan = parseFloat(document.getElementById('totalBiayaBahanInput').value) || 0;
    const btkl = parseFloat(document.getElementById('totalBtklInput').value) || 0;
    const bop = parseFloat(document.getElementById('totalBopInput').value) || 0;
    const hpp = biayaBahan + btkl + bop;
    
    document.getElementById('summaryBiayaBahan').textContent = formatRupiah(biayaBahan);
    document.getElementById('summaryBtkl').textContent = formatRupiah(btkl);
    document.getElementById('summaryBop').textContent = formatRupiah(bop);
    document.getElementById('summaryHpp').textContent = formatRupiah(hpp);
}

function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotalBtkl();
    calculateTotalBop();
    updateSummary();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\UMKM_COE\resources\views/master-data/bom/edit.blade.php ENDPATH**/ ?>