@extends('layouts.app')

@section('content')
<div class="retur-create-container container-fluid py-4 owner-retur-theme">
    <div class="retur-create-hero mb-4">
        <div>
            <span class="retur-pill">Form Retur</span>
            <h2 class="retur-create-title">Tambah Retur Barang</h2>
            <p class="retur-create-subtext">Catat detail retur, pilih tipe transaksi, dan pastikan stok ter-update dengan rapi.</p>
        </div>
        <a href="{{ route('transaksi.retur.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke daftar
        </a>
    </div>

    <div class="retur-create-card">
        <div class="card-glass">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('transaksi.retur.store') }}" method="POST" id="formRetur" class="retur-form">
                @csrf

                @if(request()->filled('ref_id'))
                    <input type="hidden" name="ref_id" value="{{ request('ref_id') }}">
                @endif

                <!-- Form Header -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Retur</label>
                        <div class="input-with-icon">
                            <i class="bi bi-calendar-event"></i>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipe Retur</label>
                        <div class="input-with-icon">
                            <i class="bi bi-arrows-repeat"></i>
                            <select name="type" id="typeRetur" class="form-select" required onchange="toggleReturType()">
                                <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Retur Penjualan</option>
                                <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Retur Pembelian</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kompensasi</label>
                        <div class="input-with-icon">
                            <i class="bi bi-wallet2"></i>
                            <select name="kompensasi" class="form-select" required>
                                <option value="credit">Kredit/Nota</option>
                                <option value="refund">Refund</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Detail Retur Section -->
                <div class="retur-detail-header mb-3" id="headerDetail">Detail Retur - PRODUK</div>

                <div class="retur-table-wrapper mb-4">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th id="headerColumn">Produk</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Harga Asal (Opsional)</th>
                                <th class="text-center" style="width: 60px;">
                                    <button type="button" class="btn btn-icon add-row" onclick="addRowRetur()"><i class="bi bi-plus-lg"></i></button>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRetur"></tbody>
                    </table>
                </div>

                <!-- Buttons -->
                <div class="retur-form-actions">
                    <a href="{{ route('transaksi.retur.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-retur-primary">
                        <i class="bi bi-save"></i> Simpan Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data dari server
const PRODUKS = @json($produks);
const BAHAN_BAKUS = @json($bahanBakus);
let rowIndex = 0;

// Toggle tipe retur
function toggleReturType() {
    const type = document.getElementById('typeRetur').value;
    const headerDetail = document.getElementById('headerDetail');
    const headerColumn = document.getElementById('headerColumn');
    
    if (type === 'sale') {
        headerDetail.textContent = '📋 Detail Retur - PRODUK';
        headerColumn.textContent = 'PRODUK';
    } else {
        headerDetail.textContent = '📋 Detail Retur - BAHAN BAKU';
        headerColumn.textContent = 'BAHAN BAKU';
    }
    
    // Rebuild all rows
    rebuildAllRows();
}

// Rebuild semua rows
function rebuildAllRows() {
    const tbody = document.getElementById('tbodyRetur');
    const rowCount = tbody.children.length;
    
    // Clear
    tbody.innerHTML = '';
    rowIndex = 0;
    
    // Add at least one row
    if (rowCount === 0) {
        addRowRetur();
    } else {
        for (let i = 0; i < rowCount; i++) {
            addRowRetur();
        }
    }
}

// Add row
function addRowRetur() {
    const type = document.getElementById('typeRetur').value;
    const tbody = document.getElementById('tbodyRetur');
    const tr = document.createElement('tr');
    
    let selectOptions = '';
    if (type === 'sale') {
        selectOptions = '<option value="">-- Pilih Produk --</option>';
        PRODUKS.forEach(item => {
            selectOptions += `<option value="${item.id}">${item.nama_produk}</option>`;
        });
    } else {
        selectOptions = '<option value="">-- Pilih Bahan Baku --</option>';
        BAHAN_BAKUS.forEach(item => {
            selectOptions += `<option value="${item.id}">${item.nama_bahan}</option>`;
        });
    }
    
    tr.innerHTML = `
        <td>
            <select name="details[${rowIndex}][produk_id]" class="form-select" required>
                ${selectOptions}
            </select>
        </td>
        <td>
            <input type="number" step="0.01" min="0.01" name="details[${rowIndex}][qty]" class="form-control text-center" placeholder="0" required>
        </td>
        <td>
            <input type="number" step="0.01" name="details[${rowIndex}][harga_satuan_asal]" class="form-control text-center" placeholder="Opsional">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-icon btn-remove" onclick="removeRowRetur(this)"><i class="bi bi-trash"></i></button>
        </td>
    `;
    
    tbody.appendChild(tr);
    rowIndex++;
}

// Remove row
function removeRowRetur(btn) {
    const tbody = document.getElementById('tbodyRetur');
    if (tbody.children.length > 1) {
        btn.closest('tr').remove();
    } else {
        alert('Minimal harus ada 1 baris!');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    addRowRetur(); // Add first row
});
</script>

<style>
.owner-retur-theme {
    position: relative;
    z-index: 0;
    min-height: calc(100vh - 3rem);
    background: radial-gradient(circle at 10% 10%, rgba(99, 102, 241, 0.15) 0%, transparent 55%),
                radial-gradient(circle at 95% 20%, rgba(236, 72, 153, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, rgba(8, 20, 45, 0.95) 0%, rgba(8, 20, 45, 0.8) 100%);
}

.owner-retur-theme::before,
.owner-retur-theme::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: -1;
}

.owner-retur-theme::before {
    background: radial-gradient(circle at 40% 0%, rgba(56, 189, 248, 0.18), transparent 60%);
}

.owner-retur-theme::after {
    background: radial-gradient(circle at 80% 75%, rgba(147, 197, 253, 0.16), transparent 55%);
}

.owner-retur-theme .retur-create-hero {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.85), rgba(79, 70, 229, 0.8));
    border: 1px solid rgba(148, 163, 233, 0.35);
    box-shadow: 0 18px 36px rgba(14, 23, 42, 0.45);
    backdrop-filter: blur(16px);
}

.owner-retur-theme .retur-pill {
    background: rgba(248, 250, 252, 0.16);
    color: rgba(226, 232, 240, 0.9);
}

.owner-retur-theme .retur-create-title {
    color: #f8fafc;
}

.owner-retur-theme .retur-create-subtext {
    color: rgba(226, 232, 240, 0.78);
}

.owner-retur-theme .card-glass {
    background: linear-gradient(155deg, rgba(15, 23, 42, 0.95), rgba(17, 24, 39, 0.82));
    border: 1px solid rgba(71, 85, 105, 0.45);
    box-shadow: 0 22px 45px rgba(2, 6, 23, 0.6);
    backdrop-filter: blur(16px);
    color: #e2e8f0;
}

.owner-retur-theme .retur-form label.form-label {
    color: rgba(226, 232, 240, 0.88);
}

.owner-retur-theme .input-with-icon i {
    color: #a5b4fc;
}

.owner-retur-theme .input-with-icon .form-control,
.owner-retur-theme .input-with-icon .form-select,
.owner-retur-theme .form-control,
.owner-retur-theme .form-select {
    background-color: rgba(15, 23, 42, 0.55);
    border: 1px solid rgba(148, 163, 184, 0.35);
    color: #e2e8f0;
}

.owner-retur-theme .form-control::placeholder {
    color: rgba(148, 163, 184, 0.85);
}

.owner-retur-theme .retur-detail-header {
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(51, 65, 85, 0.55);
    color: rgba(226, 232, 240, 0.92);
}

.owner-retur-theme .retur-table-wrapper {
    border: 1px solid rgba(71, 85, 105, 0.45);
    box-shadow: 0 22px 45px rgba(2, 6, 23, 0.5);
}

.owner-retur-theme .retur-table-wrapper table thead {
    background: rgba(30, 41, 59, 0.78);
    color: #f8fafc;
}

.owner-retur-theme .retur-table-wrapper table th,
.owner-retur-theme .retur-table-wrapper table td {
    border-bottom: 1px solid rgba(51, 65, 85, 0.55);
}

.owner-retur-theme .btn-icon.add-row {
    background: linear-gradient(120deg, #8b5cf6, #6366f1, #22d3ee);
    color: #0f172a;
}

.owner-retur-theme .btn-icon.btn-remove {
    background: rgba(239, 68, 68, 0.14);
    color: #fca5a5;
}

.owner-retur-theme .btn-retur-primary {
    background: linear-gradient(120deg, #8b5cf6, #6366f1, #22d3ee);
    color: #0f172a !important;
    box-shadow: 0 14px 22px rgba(99, 102, 241, 0.35);
}

.owner-retur-theme .btn-retur-primary:hover {
    color: #0f172a !important;
}

.owner-retur-theme .btn-outline-secondary {
    border-color: rgba(148, 163, 184, 0.45) !important;
    background-color: rgba(15, 23, 42, 0.5) !important;
    color: rgba(226, 232, 240, 0.9) !important;
}

.owner-retur-theme .btn-outline-secondary:hover {
    background-color: rgba(148, 163, 184, 0.18) !important;
    border-color: rgba(226, 232, 240, 0.45) !important;
    color: #ffffff !important;
}

.retur-create-container {
    position: relative;
    color: #111827;
}

.retur-create-hero {
    background: linear-gradient(135deg, #7d5cff, #9c6bff);
    border-radius: 24px;
    padding: 28px 32px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    color: #fff;
    box-shadow: 0 20px 40px rgba(76, 29, 149, 0.28);
}

.retur-create-title {
    font-weight: 700;
    font-size: 2rem;
    margin-top: 12px;
}

.retur-create-subtext {
    max-width: 520px;
    opacity: 0.85;
}

.retur-pill {
    background: rgba(255, 255, 255, 0.18);
    border-radius: 999px;
    padding: 6px 16px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.retur-create-card {
    margin-top: -32px;
    position: relative;
    z-index: 2;
}

.card-glass {
    background: #fff;
    border-radius: 22px;
    padding: 32px;
    box-shadow: 0 28px 60px rgba(17, 24, 39, 0.12);
    border: 1px solid rgba(125, 92, 255, 0.16);
}

.retur-form label.form-label {
    font-weight: 600;
    color: #1f2937;
}

.input-with-icon {
    position: relative;
}

.input-with-icon i {
    position: absolute;
    top: 50%;
    left: 12px;
    transform: translateY(-50%);
    color: #7d5cff;
}

.input-with-icon .form-control,
.input-with-icon .form-select {
    padding-left: 40px;
    border-radius: 12px;
    border: 1px solid #d8ddf3;
    height: 46px;
}

.retur-detail-header {
    background: #f6f8ff;
    border-radius: 18px;
    padding: 14px 18px;
    font-weight: 600;
    letter-spacing: 0.4px;
    color: #3f4a6b;
    border: 1px solid #e3e7ff;
}

.retur-table-wrapper {
    border: 1px solid #e3e7ff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 14px 32px rgba(148, 163, 184, 0.18);
}

.retur-table-wrapper table thead {
    background: linear-gradient(135deg, rgba(125, 92, 255, 0.18), rgba(125, 92, 255, 0.05));
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.6px;
}

.retur-table-wrapper table th,
.retur-table-wrapper table td {
    border-bottom: 1px solid #edf2ff;
}

.btn-icon {
    border-radius: 999px;
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
}

.btn-icon.add-row {
    background: linear-gradient(135deg, #7d5cff, #9c6bff);
    color: #fff;
    box-shadow: 0 10px 20px rgba(125, 92, 255, 0.25);
}

.btn-icon.btn-remove {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
}

.retur-form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.btn-retur-primary {
    border-radius: 999px;
    padding: 10px 22px;
    background: linear-gradient(135deg, #7d5cff, #9c6bff);
    color: #fff;
    font-weight: 600;
    border: none;
    box-shadow: 0 14px 26px rgba(125, 92, 255, 0.25);
}

.btn-retur-primary:hover {
    color: #fff;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .card-glass {
        padding: 24px;
    }

    .retur-form-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .retur-form-actions .btn {
        width: 100%;
    }
}
</style>
@endsection
