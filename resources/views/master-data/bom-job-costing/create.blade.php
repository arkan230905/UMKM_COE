@extends('layouts.app')

@push('styles')
<style>
/* BOM Create page - Modern & Fresh Design */
.container-fluid h3,
.container-fluid small,
.container-fluid .card-header h5,
.container-fluid .card-body label,
.container-fluid .card-body .form-label,
.container-fluid .card-body small {
    color: #e2e8f0 !important;
}

/* Modern Table Design */
.table {
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.table thead th {
    color: #1a202c !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    font-size: 0.85rem !important;
    padding: 1rem 0.75rem !important;
    border: none !important;
}

.table tbody td {
    color: #2d3748 !important;
    background-color: rgba(255,255,255,0.98) !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.875rem 0.75rem !important;
    vertical-align: middle !important;
}

/* BBB Table - Gradient Blue */
#bbbTable thead th {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%) !important;
    color: white !important;
}

#bbbTable tbody td {
    background: linear-gradient(135deg, rgba(66, 153, 225, 0.05) 0%, rgba(49, 130, 206, 0.05) 100%) !important;
}

/* Bahan Pendukung Table - Gradient Green */
#bpTable thead th {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
    color: white !important;
}

#bpTable tbody td {
    background: linear-gradient(135deg, rgba(72, 187, 120, 0.05) 0%, rgba(56, 161, 105, 0.05) 100%) !important;
}

/* BOP Table - Gradient Orange */
#bopTable thead th {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%) !important;
    color: white !important;
}

#bopTable tbody td {
    background: linear-gradient(135deg, rgba(237, 137, 54, 0.05) 0%, rgba(221, 107, 32, 0.05) 100%) !important;
}

/* Summary table - Modern Purple */
.card-body .table-bordered td {
    background: linear-gradient(135deg, rgba(128, 90, 213, 0.08) 0%, rgba(124, 58, 237, 0.08) 100%) !important;
    color: white !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
}

/* Force white text in summary section */
.card-body .table td,
.card-body .table-bordered td,
.card-body table td {
    color: white !important;
}

/* Specific targeting for summary table */
.bg-success + .card-body .table td,
.bg-success + .card-body .table-bordered td {
    color: white !important;
}

/* Even more specific for the summary values */
#summaryBBB,
#summaryBP,
#totalBiayaBahan {
    color: white !important;
}

/* Total rows - Modern highlighting */
.table-warning td {
    background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%) !important;
    color: white !important;
    font-weight: 700 !important;
}

.table-info td {
    background: linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%) !important;
    color: white !important;
    font-weight: 700 !important;
}

.table-primary td {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    font-size: 1.1em !important;
}

.table-success td {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    font-size: 1.1em !important;
}

/* Force white text for all summary elements */
.card .card-body .table td,
.card .card-body .table-bordered td,
.card .card-body table tbody td,
.card .card-body table tr td {
    color: white !important;
}

/* Target the specific summary card */
.border-success .card-body .table td,
.border-success .card-body .table-bordered td {
    color: white !important;
}

/* Override any conflicting styles */
.container-fluid .card-body .table td {
    color: white !important;
}

/* Modern Form Labels */
.form-label {
    color: #a0aec0 !important;
    font-weight: 500 !important;
    font-size: 0.9rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

/* Card headers with modern gradients */
.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.bg-dark {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
}

/* Modern Button Design */
.btn {
    border-radius: 8px !important;
    font-weight: 500 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    font-size: 0.85rem !important;
    padding: 0.6rem 1.2rem !important;
    transition: all 0.3s ease !important;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border: none !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4) !important;
}

.btn-primary:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6) !important;
}

.btn-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%) !important;
    border: none !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4) !important;
}

.btn-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
    border: none !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.4) !important;
}

.btn-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
    border: none !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4) !important;
}

/* Modern Form Controls */
.form-select, .form-control {
    background-color: rgba(255,255,255,0.95) !important;
    color: #2d3748 !important;
    border: 2px solid #e2e8f0 !important;
    border-radius: 8px !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
}

.form-select:focus, .form-control:focus {
    background-color: white !important;
    color: #2d3748 !important;
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    transform: translateY(-1px) !important;
}

/* Modern Price Display */
.text-end {
    color: #38a169 !important;
    font-weight: 700 !important;
    font-family: 'Segoe UI', system-ui, sans-serif !important;
}

/* Modern Alert Design - Cyber/Neon Style */
.alert-info {
    background: linear-gradient(135deg, rgba(79, 172, 254, 0.1) 0%, rgba(0, 242, 254, 0.1) 100%) !important;
    border: 2px solid #4facfe !important;
    border-radius: 12px !important;
    color: #0ea5e9 !important;
    font-weight: 600 !important;
    padding: 1.25rem !important;
    box-shadow: 0 8px 25px rgba(79, 172, 254, 0.2) !important;
    position: relative !important;
    overflow: hidden !important;
}

.alert-info::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: -100% !important;
    width: 100% !important;
    height: 100% !important;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent) !important;
    animation: shimmer 2s infinite !important;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Modern Card Design */
.card {
    border-radius: 16px !important;
    border: none !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
    backdrop-filter: blur(10px) !important;
}

.card-header {
    border-radius: 16px 16px 0 0 !important;
    border-bottom: none !important;
    padding: 1.5rem !important;
}

/* Hover Effects */
.table tbody tr:hover td {
    background-color: rgba(102, 126, 234, 0.05) !important;
    transform: scale(1.01) !important;
    transition: all 0.2s ease !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3><i class="bi bi-calculator me-2"></i>Hitung Bahan - {{ $produk->nama_produk }}</h3>
            <small class="text-muted">Kode: {{ $produk->kode_produk }}</small>
        </div>
        <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Produk</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('master-data.bom-job-costing.store', $produk->id) }}" method="POST" id="bomForm">
        @csrf
        
        <!-- Info Produk -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-box me-2"></i>Informasi Produk</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Produk</label>
                        <input type="text" class="form-control" value="{{ $produk->nama_produk }}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label for="jumlahProduk" class="form-label fw-bold">Jumlah Produk yang Dihitung</label>
                        <input type="number" name="jumlah_produk" id="jumlahProduk" class="form-control" value="1" min="1" step="1" required>
                        <small class="text-muted">Masukkan jumlah produk yang akan dihitung biayanya</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. BBB -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>1. Biaya Bahan Baku (BBB)</h5></div>
            <div class="card-body">
                <table class="table table-bordered" id="bbbTable">
                    <thead class="table-light">
                        <tr><th width="35%" style="color: black !important;">Bahan Baku</th><th width="15%">Jumlah</th><th width="10%">Satuan</th><th width="15%">Harga/Satuan</th><th width="15%">Subtotal</th><th width="10%">Aksi</th></tr>
                    </thead>
                    <tbody id="bbbBody">
                        <tr class="bbb-row">
                            <td>
                                <select name="bbb_id[]" class="form-select form-select-sm bbb-select">
                                    <option value="">-- Pilih Bahan Baku --</option>
                                    @foreach($bahanBakus as $bb)
                                        <option value="{{ $bb->id }}" data-harga="{{ $bb->harga_satuan ?? 0 }}" data-satuan="{{ $bb->satuanRelation->kode ?? 'KG' }}" data-category="{{ $bb->satuanRelation->kategori ?? 'Berat' }}">{{ $bb->nama_bahan }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="bbb_jumlah[]" class="form-control form-control-sm bbb-jumlah" value="0" min="0" step="0.01"></td>
                            <td><select name="bbb_satuan[]" class="form-select form-select-sm bbb-satuan">
                                    <option value="">-- Satuan --</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                    @endforeach
                                </select></td>
                            <td class="bbb-harga text-end">Rp 0</td>
                            <td class="bbb-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bbb"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot><tr class="table-warning"><td colspan="4" class="text-end fw-bold">Total BBB</td><td class="text-end fw-bold" id="totalBBB">Rp 0</td><td></td></tr></tfoot>
                </table>
                <button type="button" class="btn btn-primary btn-sm" id="btnTambahBBB" style="color: black !important; background-color: #0d6efd !important; border-color: #0d6efd !important;"><i class="bi bi-plus"></i> Tambah Bahan Baku</button>
            </div>
        </div>

        <!-- 2. Bahan Pendukung -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="bi bi-droplet me-2"></i>2. Bahan Penolong/Pendukung</h5></div>
            <div class="card-body">
                <table class="table table-bordered" id="bpTable">
                    <thead class="table-light">
                        <tr><th width="35%">Bahan Penolong</th><th width="15%">Jumlah</th><th width="10%">Satuan</th><th width="15%">Harga/Satuan</th><th width="15%">Subtotal</th><th width="10%">Aksi</th></tr>
                    </thead>
                    <tbody id="bpBody">
                        <tr class="bp-row">
                            <td>
                                <select name="bp_id[]" class="form-select form-select-sm bp-select">
                                    <option value="">-- Pilih Bahan Penolong --</option>
                                    @foreach($bahanPendukungs as $bp)
                                        <option value="{{ $bp->id }}" data-harga="{{ $bp->harga_satuan ?? 0 }}" data-satuan="{{ $bp->satuanRelation->kode ?? 'PCS' }}">{{ $bp->nama_bahan }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="bp_jumlah[]" class="form-control form-control-sm bp-jumlah" value="0" min="0" step="0.01"></td>
                            <td><select name="bp_satuan[]" class="form-select form-select-sm bp-satuan">
                                    <option value="">-- Satuan --</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->kode }}">{{ $satuan->kode }}</option>
                                    @endforeach
                                </select></td>
                            <td class="bp-harga text-end">Rp 0</td>
                            <td class="bp-subtotal text-end fw-bold">Rp 0</td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus-bp"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot><tr class="table-info"><td colspan="4" class="text-end fw-bold">Total Bahan Penolong</td><td class="text-end fw-bold" id="totalBP">Rp 0</td><td></td></tr></tfoot>
                </table>
                <button type="button" class="btn btn-info btn-sm text-dark" id="btnTambahBP"><i class="bi bi-plus"></i> Tambah Bahan Penolong</button>
            </div>
        </div>

        <!-- Ringkasan Biaya Bahan -->
        <div class="card shadow-sm mb-3 border-success">
            <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Biaya Bahan</h5></div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><td width="60%">Total Biaya Bahan Baku (BBB)</td><td class="text-end fw-bold" id="summaryBBB">Rp 0</td></tr>
                    <tr><td>Total Bahan Penolong</td><td class="text-end fw-bold" id="summaryBP">Rp 0</td></tr>
                    <tr class="table-success"><td class="fw-bold fs-5">TOTAL BIAYA BAHAN PER UNIT</td><td class="text-end fw-bold fs-5" id="totalBiayaBahan">Rp 0</td></tr>
                </table>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-save me-1"></i> Simpan Biaya Bahan</button>
            <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary btn-lg">Batal</a>
        </div>
    </form>
</div>

<script>
function formatRupiah(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
function parseRupiah(s) { return parseFloat(String(s).replace(/[^0-9]/g, '')) || 0; }
function getJumlahProduk() { return parseInt(document.getElementById('jumlahProduk').value) || 1; }

// Convert price from base unit to selected unit
function convertPrice(basePrice, baseUnit, targetUnit) {
    if (baseUnit === targetUnit) return basePrice;
    
    // Define conversion factors (how many smaller units in one larger unit)
    const conversions = {
        'Berat': { 'KG': 1000, 'G': 1, 'MG': 0.001 },
        'Volume': { 'LTR': 1000, 'ML': 1 },
        'Unit': { 'PCS': 1, 'PACK': 10, 'BOX': 100 },
        'Energi': { 'WTT': 1 },
        'Ukuran': { 'SDT': 1 }
    };
    
    // Get category from data or determine by unit
    let category = arguments[3] || 'Unit';
    
    // Auto-detect category if not provided
    if (!arguments[3]) {
        const allUnits = Object.keys(conversions).flatMap(k => Object.keys(conversions[k]));
        if (allUnits.includes(baseUnit)) {
            for (const [cat, units] of Object.entries(conversions)) {
                if (Object.keys(units).includes(baseUnit)) {
                    category = cat;
                    break;
                }
            }
        }
    }
    
    const categoryConversions = conversions[category] || {};
    
    // Convert to smallest unit first, then to target unit
    const baseToSmallest = categoryConversions[baseUnit] || 1;
    const targetToSmallest = categoryConversions[targetUnit] || 1;
    
    // Convert base price to smallest unit, then to target unit
    const priceInSmallestUnit = basePrice / baseToSmallest;
    const convertedPrice = priceInSmallestUnit * targetToSmallest;
    
    return convertedPrice;
}

function hitungBBB(row) {
    const sel = row.querySelector('.bbb-select'), jml = row.querySelector('.bbb-jumlah'), sat = row.querySelector('.bbb-satuan');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) { 
        row.querySelector('.bbb-harga').textContent = 'Rp 0'; 
        row.querySelector('.bbb-subtotal').textContent = 'Rp 0'; 
        hitungTotalBBB(); 
        return; 
    }
    
    const baseHarga = parseFloat(opt.dataset.harga) || 0;
    const baseUnit = opt.dataset.satuan || 'KG';
    const selectedUnit = sat.value;
    const category = opt.dataset.category || 'Berat';
    
    // Set satuan default dari data, tapi biarkan user ubah
    if (sat.value === '') {
        sat.value = baseUnit;
    }
    
    // Convert price to selected unit
    const convertedHarga = convertPrice(baseHarga, baseUnit, selectedUnit, category);
    
    row.querySelector('.bbb-harga').textContent = formatRupiah(convertedHarga);
    row.querySelector('.bbb-subtotal').textContent = formatRupiah((parseFloat(jml.value) || 0) * convertedHarga);
    hitungTotalBBB();
}
function hitungTotalBBB() {
    let t = 0; document.querySelectorAll('.bbb-row').forEach(r => t += parseRupiah(r.querySelector('.bbb-subtotal').textContent));
    document.getElementById('totalBBB').textContent = formatRupiah(t);
    document.getElementById('summaryBBB').textContent = formatRupiah(t);
    hitungHPP();
}
function attachBBB(row) {
    row.querySelector('.bbb-select').addEventListener('change', () => hitungBBB(row));
    row.querySelector('.bbb-jumlah').addEventListener('input', () => hitungBBB(row));
    row.querySelector('.bbb-satuan').addEventListener('change', () => hitungBBB(row));
    row.querySelector('.btn-hapus-bbb').addEventListener('click', () => { row.remove(); hitungTotalBBB(); });
}

function hitungBP(row) {
    const sel = row.querySelector('.bp-select'), jml = row.querySelector('.bp-jumlah'), sat = row.querySelector('.bp-satuan');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) { 
        row.querySelector('.bp-harga').textContent = 'Rp 0'; 
        row.querySelector('.bp-subtotal').textContent = 'Rp 0'; 
        hitungTotalBP(); 
        return; 
    }
    
    const baseHarga = parseFloat(opt.dataset.harga) || 0;
    const baseUnit = opt.dataset.satuan || 'PCS';
    const selectedUnit = sat.value;
    
    // Set satuan default dari data, tapi biarkan user ubah
    if (sat.value === '') {
        sat.value = baseUnit;
    }
    
    // Convert price to selected unit (auto-detect category)
    const convertedHarga = convertPrice(baseHarga, baseUnit, selectedUnit);
    
    row.querySelector('.bp-harga').textContent = formatRupiah(convertedHarga);
    row.querySelector('.bp-subtotal').textContent = formatRupiah((parseFloat(jml.value) || 0) * convertedHarga);
    hitungTotalBP();
}
function hitungTotalBP() {
    let t = 0; document.querySelectorAll('.bp-row').forEach(r => t += parseRupiah(r.querySelector('.bp-subtotal').textContent));
    document.getElementById('totalBP').textContent = formatRupiah(t);
    document.getElementById('summaryBP').textContent = formatRupiah(t);
    hitungHPP();
}
function attachBP(row) {
    row.querySelector('.bp-select').addEventListener('change', () => hitungBP(row));
    row.querySelector('.bp-jumlah').addEventListener('input', () => hitungBP(row));
    row.querySelector('.bp-satuan').addEventListener('change', () => hitungBP(row));
    row.querySelector('.btn-hapus-bp').addEventListener('click', () => { row.remove(); hitungTotalBP(); });
}

function hitungHPP() {
    const bbb = parseRupiah(document.getElementById('summaryBBB').textContent);
    const bp = parseRupiah(document.getElementById('summaryBP').textContent);
    const total = bbb + bp; // Hanya BBB + Bahan Pendukung
    const jumlahProduk = getJumlahProduk();
    
    // Update Total Biaya Bahan per unit
    const biayaPerUnit = jumlahProduk > 0 ? total / jumlahProduk : 0;
    document.getElementById('totalBiayaBahan').textContent = formatRupiah(biayaPerUnit);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bbb-row').forEach(r => attachBBB(r));
    document.querySelectorAll('.bp-row').forEach(r => attachBP(r));
    
    // Add event listener for jumlahProduk
    const jumlahProdukField = document.getElementById('jumlahProduk');
    if (jumlahProdukField) {
        jumlahProdukField.addEventListener('input', hitungHPP);
    }
    
    // Form submission validation - hanya cek BBB dan BP
    document.getElementById('bomForm').addEventListener('submit', function(e) {
        const totalBBB = parseRupiah(document.getElementById('totalBBB').textContent);
        const totalBP = parseRupiah(document.getElementById('totalBP').textContent);
        const totalBiayaBahan = parseRupiah(document.getElementById('totalBiayaBahan').textContent);
        
        // Check if any material costs have been calculated
        if (totalBBB === 0 && totalBP === 0) {
            e.preventDefault();
            alert('PERINGATAN: Tidak dapat menyimpan biaya bahan!\n\n' +
                  'Anda belum menghitung biaya bahan apapun.\n\n' +
                  'Silakan isi minimal salah satu dari:\n' +
                  '• Biaya Bahan Baku (BBB)\n' +
                  '• Bahan Penolong/Pendukung\n\n' +
                  'Kemudian coba simpan kembali.');
            return false;
        }
    });
    
    document.getElementById('btnTambahBBB').addEventListener('click', () => {
        const templateRow = document.querySelector('.bbb-row');
        const newRow = templateRow.cloneNode(true);
        
        // Reset all form values
        newRow.querySelector('.bbb-select').value = '';
        newRow.querySelector('.bbb-jumlah').value = '0';
        newRow.querySelector('.bbb-satuan').value = '';
        newRow.querySelector('.bbb-harga').textContent = 'Rp 0';
        newRow.querySelector('.bbb-subtotal').textContent = 'Rp 0';
        
        // Clear any selected state
        newRow.querySelector('.bbb-select').selectedIndex = 0;
        
        // Add to table
        document.getElementById('bbbBody').appendChild(newRow);
        attachBBB(newRow);
        
        // Recalculate totals
        hitungTotalBBB();
    });
    
    document.getElementById('btnTambahBP').addEventListener('click', () => {
        const templateRow = document.querySelector('.bp-row');
        const newRow = templateRow.cloneNode(true);
        
        // Reset all form values
        newRow.querySelector('.bp-select').value = '';
        newRow.querySelector('.bp-jumlah').value = '0';
        newRow.querySelector('.bp-satuan').value = '';
        newRow.querySelector('.bp-harga').textContent = 'Rp 0';
        newRow.querySelector('.bp-subtotal').textContent = 'Rp 0';
        
        // Clear any selected state
        newRow.querySelector('.bp-select').selectedIndex = 0;
        
        // Add to table
        document.getElementById('bpBody').appendChild(newRow);
        attachBP(newRow);
        
        // Recalculate totals
        hitungTotalBP();
    });
    
    // Initial calculation
    hitungHPP();
});
</script>
@endsection
