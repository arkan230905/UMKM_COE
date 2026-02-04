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
                        <label class="form-label fw-bold">Produk yang Diedit</label>
                        <div class="form-control-plaintext bg-light p-3 rounded border">
                            <div class="d-flex align-items-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bom->produk->foto): ?>
                                    <img src="<?php echo e(Storage::url($bom->produk->foto)); ?>" 
                                         alt="<?php echo e($bom->produk->nama_produk); ?>" 
                                         class="rounded me-3"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-box text-white"></i>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div>
                                    <div class="fw-bold fs-5"><?php echo e($bom->produk->nama_produk); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bom->produk->barcode): ?>
                                        <small class="text-muted"><?php echo e($bom->produk->barcode); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <small class="text-info">
                            <i class="fas fa-lock"></i> Produk tidak dapat diubah saat edit BOM
                        </small>
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
                                    <span class="fw-semibold"><?php echo e(number_format($bahan['jumlah'], 3)); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($bahan['jumlah_base']) && $bahan['jumlah'] != $bahan['jumlah_base']): ?>
                                        <br><small class="text-muted">Base: <?php echo e(number_format($bahan['jumlah_base'], 3)); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <input type="hidden" name="bahan_jumlah[]" value="<?php echo e($bahan['jumlah']); ?>">
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo e($bahan['satuan']); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($bahan['satuan_base']) && $bahan['satuan'] != $bahan['satuan_base']): ?>
                                        <br><small class="text-muted">Base: <?php echo e($bahan['satuan_base']); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-success">Rp <?php echo e(number_format($bahan['harga'], 0, ',', '.')); ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary">
                                        Rp <?php echo e(number_format($bahan['subtotal'] ?? ($bahan['harga'] * $bahan['jumlah']), 0, ',', '.')); ?>

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
                                        $totalBiayaBahan = array_reduce($biayaBahan, function($carry, $bahan) {
                                            return $carry + ($bahan['subtotal'] ?? ($bahan['harga'] * $bahan['jumlah']));
                                        }, 0);
                                    ?>
                                    <span id="totalBiayaBahan">Rp <?php echo e(number_format($totalBiayaBahan, 0, ',', '.')); ?></span>
                                    <input type="hidden" name="total_biaya_bahan" id="totalBiayaBahanInput" value="<?php echo e($totalBiayaBahan); ?>">
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($biayaBahan)): ?>
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

        <!-- Section 2: BTKL (Biaya Tenaga Kerja Langsung) - READONLY -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>2. BTKL (Biaya Tenaga Kerja Langsung)</h5>
                <small class="text-warning">
                    <i class="fas fa-lock"></i> Data BTKL tidak dapat diubah (data mutlak dari master data)
                </small>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($btklData)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">Proses</th>
                                    <th width="15%">Tarif/Jam</th>
                                    <th width="15%">Kapasitas/Jam</th>
                                    <th width="15%">Biaya/Unit</th>
                                    <th width="15%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $noBtkl = 1; $totalBtkl = 0; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $btklData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $btkl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $totalBtkl += $btkl['subtotal']; ?>
                                    <tr class="table-light">
                                        <td class="text-center"><?php echo e($noBtkl++); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo e($btkl['nama_proses']); ?></div>
                                            <small class="text-muted"><?php echo e(number_format($btkl['durasi_jam'], 2)); ?> jam</small>
                                        </td>
                                        <td class="text-end bg-light">
                                            <span class="fw-bold">Rp <?php echo e(number_format($btkl['tarif_per_jam'], 0, ',', '.')); ?></span>
                                        </td>
                                        <td class="text-end bg-light">
                                            <?php echo e(number_format($btkl['kapasitas_per_jam'], 0, ',', '.')); ?>

                                        </td>
                                        <td class="text-end bg-warning">
                                            <span class="fw-bold text-success">Rp <?php echo e(number_format($btkl['biaya_per_produk'], 0, ',', '.')); ?></span>
                                        </td>
                                        <td class="text-end bg-info">
                                            <span class="fw-bold text-primary">Rp <?php echo e(number_format($btkl['subtotal'], 0, ',', '.')); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <th colspan="5" class="text-end">Total BTKL:</th>
                                    <th class="text-end">
                                        <span class="fw-bold">Rp <?php echo e(number_format($totalBtkl, 0, ',', '.')); ?></span>
                                        <input type="hidden" name="total_btkl" value="<?php echo e($totalBtkl); ?>">
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Data BTKL tidak tersedia untuk produk ini.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
let btklRowIndex = <?php echo e($prosesCount ?? 0); ?>;
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