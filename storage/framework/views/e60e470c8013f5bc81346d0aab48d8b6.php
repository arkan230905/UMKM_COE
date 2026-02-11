<?php $__env->startSection('title', 'Satuan'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Brown Theme */
.bg-brown {
    background: #8B7355 !important;
}

/* Card Enhancement */
.card {
    border: none;
    box-shadow: 0 4px 6px rgba(111, 78, 55, 0.1);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 15px rgba(111, 78, 55, 0.15);
    transform: translateY(-2px);
}

/* Horizontal Tabs Style */
.horizontal-tabs {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #f0e6dc;
    padding-bottom: 0;
    background: linear-gradient(to bottom, #faf8f6, transparent);
    padding: 1rem 0 0;
    margin: -1rem -1rem 1.5rem -1rem;
    padding-left: 1rem;
    padding-right: 1rem;
}

.tab-btn {
    background: none;
    border: none;
    padding: 0.75rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 500;
    color: #8B7355;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    border-radius: 8px 8px 0 0;
    position: relative;
}

.tab-btn:hover {
    color: #7a6348;
    background: rgba(139, 115, 85, 0.05);
    transform: translateY(-1px);
}

.tab-btn.active {
    color: #8B7355;
    font-weight: 600;
    border-bottom-color: #8B7355;
    background: rgba(139, 115, 85, 0.08);
}

.tab-separator {
    color: #d4c4b0;
    font-size: 1.2rem;
    margin: 0 0.75rem;
    user-select: none;
    font-weight: 300;
}

/* Table Enhancement */
.table {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(111, 78, 55, 0.05);
}

.table thead th {
    background: #8B7355;
    color: white;
    font-weight: 600;
    border: none;
    padding: 1rem;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #faf8f6;
    transform: scale(1.01);
}

.table tbody td {
    padding: 0.875rem 1rem;
    vertical-align: middle;
    border-color: #f0e6dc;
}

/* Badge Enhancement */
.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    border-radius: 6px;
}

/* Button Enhancement */
.btn-outline-primary {
    border-color: #8B7355;
    color: #8B7355;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background-color: #8B7355;
    border-color: #8B7355;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(139, 115, 85, 0.2);
}

.btn-outline-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
}

/* Header Button Enhancement */
.btn-light {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #8B7355;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-light:hover {
    background: white;
    color: #8B7355;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 115, 85, 0.2);
}

.btn-brown {
    background: #8B7355;
    border: none;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-brown:hover {
    background: #7a6348;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 115, 85, 0.3);
}

.tab-content-custom {
    margin-top: 1rem;
}

.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
}

/* Empty State Enhancement */
.text-muted {
    color: #8B7355 !important;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .horizontal-tabs {
        gap: 0.25rem;
        padding: 0.75rem 0.5rem 0;
        margin: -0.75rem -0.5rem 1.5rem -0.5rem;
    }
    
    .tab-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .tab-separator {
        margin: 0 0.25rem;
    }
    
    .card-header h5 {
        font-size: 1rem;
    }
    
    .btn-light {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-brown text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale me-2"></i>
                        Satuan
                    </h5>
                    <button type="button" class="btn btn-light btn-sm" onclick="showAddSatuanModal()">
                        <i class="fas fa-plus me-2"></i>Tambah Satuan
                    </button>
                </div>
                <div class="card-body">
                    <!-- Custom Horizontal Tab Navigation -->
                    <div class="horizontal-tabs mb-4">
                        <button class="tab-btn active" data-tab="satuan" onclick="switchTab('satuan')">
                            Satuan
                        </button>
                        <span class="tab-separator">|</span>
                        <button class="tab-btn" data-tab="konversi" onclick="switchTab('konversi')">
                            Konversi
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content-custom">
                        <!-- TAB 1: SATUAN -->
                        <div class="tab-panel active" id="satuan-panel">
                            <!-- Tabel Satuan Sederhana -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="20%">Kode Satuan</th>
                                            <th width="60%">Nama Satuan</th>
                                            <th width="20%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $satuans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $satuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo e($satuan->kode); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo e($satuan->nama); ?></strong>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editSatuan(<?php echo e($satuan->id); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteSatuan(<?php echo e($satuan->id); ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p class="mb-0">Belum ada data satuan</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: KONVERSI -->
                        <div class="tab-panel" id="konversi-panel" style="display: none;">
                            <!-- Alert Info -->
                            <div class="alert alert-warning mb-4">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exchange-alt me-2"></i>
                                    Alat Pengecekan Konversi Satuan
                                </h6>
                                <p class="mb-2">
                                    <strong>Tab ini HANYA UNTUK CEK</strong> - tidak mengubah data, tidak menyimpan transaksi, 
                                    tidak memengaruhi stok atau costing.
                                </p>
                                <p class="mb-0">
                                    Gunakan untuk mengecek informasi konversi satuan secara cepat dan mudah.
                                </p>
                            </div>

                            <!-- Konversi Tool -->
                            <div class="row">
                                <div class="col-md-8 mx-auto">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <form id="konversiChecker">
                                                <!-- Input Jumlah -->
                                                <div class="mb-4">
                                                    <label for="jumlah" class="form-label fw-bold">
                                                        Jumlah
                                                    </label>
                                                    <input 
                                                        type="number" 
                                                        id="jumlah" 
                                                        class="form-control form-control-lg text-center"
                                                        step="0.01"
                                                        min="0"
                                                        placeholder="Masukkan jumlah"
                                                        value="1"
                                                    >
                                                    <div class="form-text">Masukkan angka yang ingin dikonversi</div>
                                                </div>

                                                <!-- Satuan Asal dan Tujuan -->
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <label for="satuan_asal" class="form-label fw-bold">
                                                            Dari Satuan
                                                        </label>
                                                        <select id="satuan_asal" class="form-select form-select-lg">
                                                            <option value="">-- Pilih Satuan --</option>
                                                        </select>
                                                        <div class="form-text">Satuan asal yang akan dikonversi</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="satuan_tujuan" class="form-label fw-bold">
                                                            Ke Satuan
                                                        </label>
                                                        <select id="satuan_tujuan" class="form-select form-select-lg">
                                                            <option value="">-- Pilih Satuan --</option>
                                                        </select>
                                                        <div class="form-text">Satuan tujuan konversi</div>
                                                    </div>
                                                </div>

                                                <!-- Hasil Konversi -->
                                                <div class="mb-4">
                                                    <label for="hasil" class="form-label fw-bold">
                                                        Hasil
                                                    </label>
                                                    <input 
                                                        type="text" 
                                                        id="hasil" 
                                                        class="form-control form-control-lg text-center bg-light fs-4"
                                                        readonly
                                                        placeholder="Hasil akan muncul otomatis"
                                                    >
                                                    <div class="form-text">Hasil konversi otomatis (read-only)</div>
                                                </div>

                                                <!-- Info Konversi -->
                                                <div id="infoKonversi" class="alert alert-secondary" style="display: none;">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        <span id="infoText" class="fw-bold"></span>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Reference -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border-0">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-book me-2"></i>
                                                Referensi Konversi Umum
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <h6 class="fw-bold text-primary">Berat</h6>
                                                    <ul class="list-unstyled">
                                                        <li>1 kg = 1.000 gram</li>
                                                        <li>1 kg = 10 ons</li>
                                                        <li>1 ons = 100 gram</li>
                                                        <li>1 ton = 1.000 kg</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="fw-bold text-success">Volume</h6>
                                                    <ul class="list-unstyled">
                                                        <li>1 liter = 1.000 ml</li>
                                                        <li>1 liter = 1 kg (air)</li>
                                                        <li>1 galon = 3,785 liter</li>
                                                        <li>1 gelas = 250 ml</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="fw-bold text-warning">Pieces</h6>
                                                    <ul class="list-unstyled">
                                                        <li>1 lusin = 12 buah</li>
                                                        <li>1 kodi = 20 buah</li>
                                                        <li>1 gross = 144 buah</li>
                                                        <li>1 rim = 500 lembar</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Satuan -->
<div class="modal fade" id="addSatuanModal" tabindex="-1" aria-labelledby="addSatuanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-brown text-white">
                <h5 class="modal-title" id="addSatuanModalLabel">
                    <i class="fas fa-plus me-2"></i>Tambah Satuan Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSatuanForm">
                    <div class="mb-3">
                        <label for="kodeSatuan" class="form-label fw-bold">
                            Kode Satuan
                        </label>
                        <input type="text" class="form-control" id="kodeSatuan" placeholder="Contoh: BOX, PCS, KG" required>
                        <div class="form-text">Kode unik untuk identifikasi satuan</div>
                    </div>
                    <div class="mb-3">
                        <label for="namaSatuan" class="form-label fw-bold">
                            Nama Satuan
                        </label>
                        <input type="text" class="form-control" id="namaSatuan" placeholder="Contoh: Box, Pieces, Kilogram" required>
                        <div class="form-text">Nama lengkap satuan yang akan ditampilkan</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-brown" onclick="saveSatuan()">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Custom tab switching function
function switchTab(tabName) {
    // Hide all panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
        panel.style.display = 'none';
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected panel
    const targetPanel = document.getElementById(tabName + '-panel');
    if (targetPanel) {
        targetPanel.classList.add('active');
        targetPanel.style.display = 'block';
    }
    
    // Add active class to clicked button
    const targetBtn = document.querySelector('[data-tab="' + tabName + '"]');
    if (targetBtn) {
        targetBtn.classList.add('active');
    }
}

// Show Add Satuan Modal
function showAddSatuanModal() {
    const modal = new bootstrap.Modal(document.getElementById('addSatuanModal'));
    document.getElementById('addSatuanForm').reset();
    modal.show();
}

// Save Satuan
function saveSatuan() {
    const kode = document.getElementById('kodeSatuan').value.trim();
    const nama = document.getElementById('namaSatuan').value.trim();
    
    if (!kode || !nama) {
        alert('Mohon lengkapi semua field!');
        return;
    }
    
    // Simulate saving (in real implementation, this would be an AJAX call)
    const newRow = `
        <tr>
            <td>
                <span class="badge bg-primary">
                    ${kode.toUpperCase()}
                </span>
            </td>
            <td>
                <strong>${nama}</strong>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary" onclick="editSatuan('new')">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSatuan('new')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    // Add to table
    const tbody = document.querySelector('#satuan-panel tbody');
    if (tbody.querySelector('tr td[colspan="3"]')) {
        // Remove empty state if exists
        tbody.innerHTML = '';
    }
    tbody.insertAdjacentHTML('beforeend', newRow);
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addSatuanModal'));
    modal.hide();
    
    // Show success message
    showSuccessMessage('Satuan berhasil ditambahkan!');
}

// Show success message
function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Data satuan yang tersedia
const satuanData = <?php echo json_encode($satuans ?? [], 15, 512) ?>;

// Konversi faktor ke satuan dasar (kg/gram/liter)
const konversiFaktor = {
    // Berat
    'kg': 1,
    'kilogram': 1,
    'gram': 0.001,
    'g': 0.001,
    'gr': 0.001,
    'ons': 0.1,
    'hg': 0.1,
    'dag': 0.01,
    'kwintal': 100,
    'ton': 1000,
    'lbs': 0.453592,
    'oz': 0.0283495,
    
    // Volume
    'liter': 1,
    'l': 1,
    'ltr': 1,
    'mililiter': 0.001,
    'ml': 0.001,
    'gelas': 0.25,
    'sendok_makan': 0.015,
    'sendok_teh': 0.005,
    'galon': 3.785,
    
    // Pieces (diasumsikan 1:1 untuk konversi universal)
    'pcs': 1,
    'pc': 1,
    'buah': 1,
    'piece': 1,
    'pack': 1,
    'pak': 1,
    'box': 1,
    'botol': 1,
    'dus': 1,
    'bungkus': 1,
    'kaleng': 1,
    'sachet': 1,
    'tablet': 1,
    'kapsul': 1,
    'tube': 1,
    'potong': 1,
    'lembar': 1,
    'roll': 1,
    'meter': 1,
    'cm': 0.01,
    'mm': 0.001,
    'inch': 0.0254,
    'kodi': 20,
    'lusin': 12,
    'gross': 144,
    'rim': 500
};

// Initialize dropdowns
function initializeDropdowns() {
    const satuanAsal = document.getElementById('satuan_asal');
    const satuanTujuan = document.getElementById('satuan_tujuan');
    
    // Clear existing options
    satuanAsal.innerHTML = '<option value="">-- Pilih Satuan --</option>';
    satuanTujuan.innerHTML = '<option value="">-- Pilih Satuan --</option>';
    
    // Add satuan options from database
    satuanData.forEach(satuan => {
        const optionAsal = new Option(satuan.nama, satuan.nama.toLowerCase());
        const optionTujuan = new Option(satuan.nama, satuan.nama.toLowerCase());
        
        satuanAsal.add(optionAsal);
        satuanTujuan.add(optionTujuan);
    });
    
    // Add common units if not in database
    const commonUnits = ['kg', 'gram', 'liter', 'ml', 'pcs', 'buah', 'potong', 'lusin'];
    commonUnits.forEach(unit => {
        if (!Array.from(satuanAsal.options).some(opt => opt.value === unit)) {
            satuanAsal.add(new Option(unit.toUpperCase(), unit));
            satuanTujuan.add(new Option(unit.toUpperCase(), unit));
        }
    });
}

// Konversi satuan
function konversiSatuan(jumlah, dari, ke) {
    if (!jumlah || !dari || !ke) return 0;
    
    const dariNormal = dari.toLowerCase().trim();
    const keNormal = ke.toLowerCase().trim();
    
    // Jika satuan sama
    if (dariNormal === keNormal) return jumlah;
    
    // Dapatkan faktor konversi
    const faktorDari = konversiFaktor[dariNormal] || 1;
    const faktorKe = konversiFaktor[keNormal] || 1;
    
    // Konversi: jumlah * (faktorDari / faktorKe)
    const hasil = jumlah * (faktorDari / faktorKe);
    
    return hasil;
}

// Update hasil konversi
function updateHasil() {
    const jumlah = parseFloat(document.getElementById('jumlah').value) || 0;
    const satuanAsal = document.getElementById('satuan_asal').value;
    const satuanTujuan = document.getElementById('satuan_tujuan').value;
    const hasilField = document.getElementById('hasil');
    const infoDiv = document.getElementById('infoKonversi');
    const infoText = document.getElementById('infoText');
    
    if (jumlah > 0 && satuanAsal && satuanTujuan) {
        const hasil = konversiSatuan(jumlah, satuanAsal, satuanTujuan);
        
        if (hasil > 0) {
            hasilField.value = formatNumber(hasil);
            
            // Show info
            const satuanAsalDisplay = satuanAsal.toUpperCase();
            const satuanTujuanDisplay = satuanTujuan.toUpperCase();
            infoText.textContent = `${formatNumber(jumlah)} ${satuanAsalDisplay} = ${formatNumber(hasil)} ${satuanTujuanDisplay}`;
            infoDiv.style.display = 'block';
            
            // Add animation
            hasilField.classList.add('border-success');
            setTimeout(() => {
                hasilField.classList.remove('border-success');
            }, 1000);
        } else {
            hasilField.value = '0';
            infoDiv.style.display = 'none';
        }
    } else {
        hasilField.value = '';
        infoDiv.style.display = 'none';
    }
}

// Format number
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(2) + ' jt';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(2) + ' rb';
    } else {
        return num.toFixed(4).replace(/\.?0+$/, '');
    }
}

// Dummy functions for edit/delete (not implemented in this view)
function editSatuan(id) {
    alert('Fitur edit satuan tidak tersedia di halaman ini. Gunakan halaman master data satuan.');
}

function deleteSatuan(id) {
    alert('Fitur hapus satuan tidak tersedia di halaman ini. Gunakan halaman master data satuan.');
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    initializeDropdowns();
    
    // Add event listeners
    document.getElementById('jumlah').addEventListener('input', updateHasil);
    document.getElementById('satuan_asal').addEventListener('change', updateHasil);
    document.getElementById('satuan_tujuan').addEventListener('change', updateHasil);
    
    // Auto-swap satuan
    document.getElementById('satuan_asal').addEventListener('dblclick', function() {
        const asal = this.value;
        const tujuan = document.getElementById('satuan_tujuan').value;
        
        if (asal && tujuan) {
            this.value = tujuan;
            document.getElementById('satuan_tujuan').value = asal;
            updateHasil();
        }
    });
    
    // Initial calculation
    updateHasil();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 's':
                e.preventDefault();
                document.getElementById('jumlah').focus();
                break;
            case 'a':
                e.preventDefault();
                document.getElementById('satuan_asal').focus();
                break;
            case 't':
                e.preventDefault();
                document.getElementById('satuan_tujuan').focus();
                break;
        }
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\UMKM_COE\resources\views/master-data/satuan/dashboard.blade.php ENDPATH**/ ?>