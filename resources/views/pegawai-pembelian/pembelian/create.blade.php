@extends('layouts.pegawai-pembelian')

@section('title', 'Tambah Pembelian')

@push('styles')
<style>
#vendorSelect {
    position: relative !important;
}

/* Force Bootstrap select dropdown to open downward */
.form-select {
    position: relative !important;
}

.form-select:focus {
    position: relative !important;
    z-index: 1 !important;
}

/* Prevent dropdown from moving up */
select.form-select {
    appearance: none !important;
    position: relative !important;
}

/* Ensure dropdown options stay below */
select.form-select option {
    position: static !important;
}

/* Container to prevent layout shift */
.vendor-select-container {
    position: relative !important;
    min-height: 80px !important;
}

/* Formatting untuk input total */
input[name="total[]"], input[name="total_pendukung[]"] {
    font-weight: bold;
    color: #2c3e50;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

input[name="total[]"]:focus, input[name="total_pendukung[]"]:focus {
    background-color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* Formatting untuk grand total */
#grandTotal {
    font-weight: bold;
    color: #27ae60;
    background-color: #d4edda;
    border: 2px solid #c3e6cb;
    font-size: 1.2em;
    text-align: center;
}

#grandTotal:focus {
    background-color: #c3e6cb;
    box-shadow: 0 0 0 0.2rem rgba(40,167,69,.25);
}
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold">
            <i class="bi bi-cart-plus"></i> Tambah Pembelian
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.pembelian.index') }}">Pembelian</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('pegawai-pembelian.pembelian.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <strong>Terjadi kesalahan:</strong>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <strong>Error:</strong> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('pegawai-pembelian.pembelian.store') }}" method="POST">
    @csrf
    
    <!-- Informasi Pembelian -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informasi Pembelian</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                    <div class="vendor-select-container">
                        <select name="vendor_id" id="vendorSelect" class="form-select" required 
                        style="position: relative !important;"
                        onchange="
                        var bahanBaku = document.getElementById('cardBahanBaku');
                        var bahanPendukung = document.getElementById('cardBahanPendukung');
                        var selectedOption = this.options[this.selectedIndex];
                        var kategori = (selectedOption.getAttribute('data-kategori') || '').toLowerCase();
                        
                        // Hide both first with !important
                        bahanBaku.style.setProperty('display', 'none', 'important');
                        bahanPendukung.style.setProperty('display', 'none', 'important');
                        
                        // Show appropriate section based on exact category
                        if (this.value) {
                            if (kategori === 'bahan pendukung' || kategori === 'pendukung') {
                                bahanPendukung.style.setProperty('display', 'block', 'important');
                            } else {
                                bahanBaku.style.setProperty('display', 'block', 'important');
                            }
                        }
                    ">
                            <option value="" data-kategori="">-- Pilih Vendor --</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}" data-kategori="{{ $vendor->kategori ?? 'Bahan Baku' }}">
                                    {{ $vendor->nama_vendor }} ({{ $vendor->kategori ?? 'Bahan Baku' }})
                                </option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-select" required>
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            @foreach($kasbank as $kb)
                                @if($kb->nama_akun)
                                    <option value="{{ $kb->id }}">
                                        @if($kb->nama_akun)
                                            @if(str_contains(strtolower($kb->nama_akun), 'kas'))
                                                💵 Kas {{ $kb->nama_akun }}
                                            @else
                                                🏦 {{ $kb->nama_akun }}
                                            @endif
                                        @endif
                                        (Saldo Akhir: Rp {{ number_format($currentBalances[$kb->kode_akun] ?? 0, 0, ',', '.') }})
                                    </option>
                                @endif
                            @endforeach
                            <option value="credit">💳 Kredit (Hutang)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">No. Referensi</label>
                        <input type="text" name="no_referensi" class="form-control" placeholder="Opsional">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Bahan Baku -->
        <div class="card mb-4" id="cardBahanBaku" style="display: none !important;">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-box me-2"></i>Detail Bahan Baku</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanBakuRow()">
                    <i class="bi bi-plus me-1"></i>Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div id="bahanBakuRows">
                    <!-- Dynamic rows will be inserted here -->
                    <div class="row g-3 bahan-baku-row" data-row-index="0">
                        <div class="col-md-3">
                            <label class="form-label">Bahan Baku</label>
                            <select name="bahan_baku_id[]" class="form-select" onchange="updateBahanBakuInfo(this)">
                                <option value="">-- Pilih Bahan Baku --</option>
                                @foreach ($bahanBakus as $bb)
                                    <option value="{{ $bb->id }}" 
                                            data-harga="{{ $bb->harga_satuan ?? 0 }}" 
                                            data-satuan="{{ $bb->satuan->nama ?? 'Tidak Diketahui' }}"
                                            data-satuan-id="{{ $bb->satuan_id ?? '' }}"
                                            data-satuan-utama="{{ $bb->satuan ?? 'KG' }}">
                                        {{ $bb->nama_bahan }} - Rp {{ number_format($bb->harga_satuan ?? 0, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control" value="1" min="0.01" step="0.01" 
                                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan</label>
                            <select name="satuan_pembelian[]" class="form-select" 
                                    onchange="calculateTotal(this)">
                                <option value="">-- Pilih --</option>
                                <option value="1">Kilogram</option>
                                <option value="3">Liter</option>
                                <option value="4">Pieces</option>
                                <option value="5">Buah</option>
                                <option value="25">Pack</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga/Satuan</label>
                            <input type="number" name="harga_satuan[]" class="form-control" value="0" min="0" 
                                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total</label>
                            <input type="text" name="total[]" class="form-control" value="0" readonly placeholder="Rp 0">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanBakuRow(this)" style="display: none;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Bahan Pendukung -->
        <div class="card mb-4" id="cardBahanPendukung" style="display: none !important;">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Detail Bahan Pendukung</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanPendukungRow()">
                    <i class="bi bi-plus me-1"></i>Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div id="bahanPendukungRows">
                    <!-- Dynamic rows will be inserted here -->
                    <div class="row g-3 bahan-pendukung-row" data-row-index="0">
                        <div class="col-md-4">
                            <label class="form-label">Bahan Pendukung</label>
                            <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                                <option value="">-- Pilih Bahan Pendukung --</option>
                                @foreach ($bahanPendukungs as $bp)
                                    <option value="{{ $bp->id }}" 
                                            data-harga="{{ $bp->harga_satuan ?? 0 }}" 
                                            data-satuan="{{ $bp->satuan->nama ?? 'Tidak Diketahui' }}"
                                            data-satuan-id="{{ $bp->satuan_id ?? '' }}">
                                        {{ $bp->nama_bahan }} - Rp {{ number_format($bp->harga_satuan ?? 0, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" 
                                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan</label>
                            <input type="text" name="satuan_pendukung[]" class="form-control" readonly placeholder="Pilih bahan pendukung">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga/Satuan</label>
                            <input type="number" name="harga_satuan_pendukung[]" class="form-control" value="0" min="0" 
                                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Total</label>
                            <input type="text" name="total_pendukung[]" class="form-control" value="0" readonly placeholder="Rp 0">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanPendukungRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keterangan dan Total -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Ringkasan Pembelian</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan keterangan pembelian (opsional)"></textarea>
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid">
                            <label class="form-label">Total Pembelian</label>
                            <input type="text" id="grandTotal" class="form-control form-control-lg fw-bold" value="0" readonly placeholder="Rp 0">
                            <button type="submit" class="btn btn-success btn-lg mt-2">
                                <i class="bi bi-check-circle me-2"></i>Simpan Pembelian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let bahanBakuRowIndex = 1;
let bahanPendukungRowIndex = 1;

// Simple and direct calculation function
function calculateTotal(element) {
    const row = element.closest('.row');
    let jumlahInput, hargaInput, totalInput;
    
    // Find the correct inputs based on row type
    if (row.classList.contains('bahan-baku-row')) {
        jumlahInput = row.querySelector('input[name="jumlah[]"]');
        hargaInput = row.querySelector('input[name="harga_satuan[]"]');
        totalInput = row.querySelector('input[name="total[]"]');
    } else if (row.classList.contains('bahan-pendukung-row')) {
        jumlahInput = row.querySelector('input[name="jumlah_pendukung[]"]');
        hargaInput = row.querySelector('input[name="harga_satuan_pendukung[]"]');
        totalInput = row.querySelector('input[name="total_pendukung[]"]');
    }
    
    if (jumlahInput && hargaInput && totalInput) {
        const jumlah = parseFloat(jumlahInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const total = jumlah * harga;
        
        // Simpan nilai asli untuk form submission
        totalInput.value = total;
        totalInput.dataset.rawValue = total;
        
        // Tampilkan format Rupiah yang benar
        totalInput.value = formatRupiah(total);
        
        console.log('Calculation:', {
            jumlah: jumlah,
            harga: harga,
            total: total
        });
    }
    
    // Always update grand total
    updateGrandTotal();
}

// Format Rupiah yang benar
function formatRupiah(angka) {
    if (angka === 0) return 'Rp 0';
    
    let number_string = angka.toString();
    let sisa = number_string.length % 3;
    let rupiah = '';
    
    if (sisa > 0) {
        rupiah = number_string.substr(0, sisa) + '.';
    }
    
    let ribuan = number_string.substr(sisa).match(/\d{3}/g);
    if (ribuan) {
        rupiah += ribuan.join('.');
    }
    
    return 'Rp ' + rupiah;
}

// Simple grand total function
function updateGrandTotal() {
    let grandTotal = 0;
    
    // Sum all bahan baku totals
    document.querySelectorAll('#bahanBakuRows input[name="total[]"]').forEach(input => {
        const rawValue = parseFloat(input.dataset.rawValue) || 0;
        grandTotal += rawValue;
    });
    
    // Sum all bahan pendukung totals
    document.querySelectorAll('#bahanPendukungRows input[name="total_pendukung[]"]').forEach(input => {
        const rawValue = parseFloat(input.dataset.rawValue) || 0;
        grandTotal += rawValue;
    });
    
    // Update grand total input dengan formatting Rupiah
    const grandTotalInput = document.getElementById('grandTotal');
    if (grandTotalInput) {
        // Simpan nilai asli
        grandTotalInput.value = grandTotal;
        grandTotalInput.dataset.rawValue = grandTotal;
        
        // Tampilkan format Rupiah
        if (grandTotal >= 1000) {
            grandTotalInput.value = formatRupiah(grandTotal);
            grandTotalInput.style.fontWeight = 'bold';
            grandTotalInput.style.color = '#27ae60';
            grandTotalInput.style.fontSize = '1.1em';
        }
        
        console.log('Grand Total Updated:', grandTotal);
    }
}

// Helper function untuk format number
function formatNumber(num) {
    return num.toLocaleString('id-ID');
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing calculations');
    
    // Trigger calculation for all existing inputs
    document.querySelectorAll('input[name="jumlah[]"], input[name="harga_satuan[]"], input[name="jumlah_pendukung[]"], input[name="harga_satuan_pendukung[]"]').forEach(input => {
        calculateTotal(input);
    });
});

// Bahan Baku functions
function addBahanBakuRow() {
    const container = document.getElementById('bahanBakuRows');
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-baku-row';
    newRow.setAttribute('data-row-index', bahanBakuRowIndex);
    
    newRow.innerHTML = `
        <div class="col-md-3">
            <select name="bahan_baku_id[]" class="form-select" onchange="updateBahanBakuInfo(this)">
                <option value="">-- Pilih Bahan Baku --</option>
                @foreach ($bahanBakus as $bb)
                    <option value="{{ $bb->id }}" 
                            data-harga="{{ $bb->harga_satuan ?? 0 }}" 
                            data-satuan="{{ $bb->satuan->nama ?? 'Tidak Diketahui' }}"
                            data-satuan-id="{{ $bb->satuan_id ?? '' }}"
                            data-satuan-utama="{{ $bb->satuan ?? 'KG' }}">
                        {{ $bb->nama_bahan }} - Rp {{ number_format($bb->harga_satuan ?? 0, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="jumlah[]" class="form-control" value="1" min="0.01" step="0.01" 
                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
        </div>
        <div class="col-md-2">
            <select name="satuan_pembelian[]" class="form-select" 
                    onchange="calculateTotal(this)">
                <option value="">-- Pilih --</option>
                <option value="1">Kilogram</option>
                <option value="3">Liter</option>
                <option value="4">Pieces</option>
                <option value="5">Buah</option>
                <option value="25">Pack</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="harga_satuan[]" class="form-control" value="0" min="0" 
                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
        </div>
        <div class="col-md-2">
            <input type="text" name="total[]" class="form-control" value="0" readonly placeholder="Rp 0">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanBakuRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newRow);
    bahanBakuRowIndex++;
    updateRemoveButtons();
}

function removeBahanBakuRow(button) {
    const row = button.closest('.bahan-baku-row');
    row.remove();
    hitungGrandTotal();
    updateRemoveButtons();
}

function updateBahanBakuInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.bahan-baku-row');
    const hargaInput = row.querySelector('input[name="harga_satuan[]"]');
    const satuanInput = row.querySelector('select[name="satuan_pembelian[]"]');
    const jumlahInput = row.querySelector('input[name="jumlah[]"]');
    
    if (selectedOption.value) {
        const harga = selectedOption.getAttribute('data-harga');
        const satuanId = selectedOption.getAttribute('data-satuan-id');
        
        // Set harga
        hargaInput.value = harga || 0;
        
        // Set satuan dropdown
        if (satuanId) {
            satuanInput.value = satuanId;
        }
        
        // Hitung total otomatis
        calculateTotal(jumlahInput);
    } else {
        hargaInput.value = 0;
        satuanInput.value = '';
        calculateTotal(jumlahInput);
    }
}

// Bahan Pendukung functions
function addBahanPendukungRow() {
    const container = document.getElementById('bahanPendukungRows');
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-pendukung-row';
    newRow.setAttribute('data-row-index', bahanPendukungRowIndex);
    
    newRow.innerHTML = `
        <div class="col-md-4">
            <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                <option value="">-- Pilih Bahan Pendukung --</option>
                @foreach ($bahanPendukungs as $bp)
                    <option value="{{ $bp->id }}" 
                            data-harga="{{ $bp->harga_satuan ?? 0 }}" 
                            data-satuan="{{ $bp->satuan->nama ?? 'Tidak Diketahui' }}"
                            data-satuan-id="{{ $bp->satuan_id ?? '' }}">
                        {{ $bp->nama_bahan }} - Rp {{ number_format($bp->harga_satuan ?? 0, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" 
                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
        </div>
        <div class="col-md-2">
            <input type="text" name="satuan_pendukung[]" class="form-control" readonly placeholder="Pilih bahan pendukung">
        </div>
        <div class="col-md-3">
            <input type="number" name="harga_satuan_pendukung[]" class="form-control" value="0" min="0" 
                   onkeyup="calculateTotal(this)" onchange="calculateTotal(this)">
        </div>
        <div class="col-md-1">
            <input type="text" name="total_pendukung[]" class="form-control" value="0" readonly placeholder="Rp 0">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanPendukungRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newRow);
    bahanPendukungRowIndex++;
    updateRemoveButtons();
}

function removeBahanPendukungRow(button) {
    const row = button.closest('.bahan-pendukung-row');
    row.remove();
    hitungGrandTotal();
    updateRemoveButtons();
}

function updateBahanPendukungInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.bahan-pendukung-row');
    const hargaInput = row.querySelector('input[name="harga_satuan_pendukung[]"]');
    const satuanInput = row.querySelector('input[name="satuan_pendukung[]"]');
    const jumlahInput = row.querySelector('input[name="jumlah_pendukung[]"]');
    const totalInput = row.querySelector('input[name="total_pendukung[]"]');
    
    if (selectedOption.value) {
        const harga = selectedOption.getAttribute('data-harga');
        const satuan = selectedOption.getAttribute('data-satuan');
        const satuanId = selectedOption.getAttribute('data-satuan-id');
        
        hargaInput.value = harga || 0;
        satuanInput.value = satuan || '';
        
        // Hitung total otomatis
        if (jumlahInput && totalInput) {
            const jumlah = parseFloat(jumlahInput.value) || 0;
            const harga = parseFloat(hargaInput.value) || 0;
            totalInput.value = jumlah * harga;
        }
        
        // Update grand total
        updateGrandTotal();
    } else {
        hargaInput.value = 0;
        satuanInput.value = '';
        if (totalInput) totalInput.value = 0;
        updateGrandTotal();
    }
}

// Calculation functions
function hitungTotal(element) {
    // Get the element that triggered the event
    const sourceElement = element || this;
    console.log('hitungTotal triggered by:', sourceElement);
    
    const row = sourceElement.closest('.row');
    console.log('Found row:', row);
    
    let jumlahInput, hargaInput, totalInput;
    
    // Check if this is bahan baku or bahan pendukung row
    if (row && row.classList.contains('bahan-baku-row')) {
        jumlahInput = row.querySelector('input[name="jumlah[]"]');
        hargaInput = row.querySelector('input[name="harga_satuan[]"]');
        totalInput = row.querySelector('input[name="total[]"]');
        console.log('Bahan Baku row found:', { jumlahInput, hargaInput, totalInput });
    } else if (row && row.classList.contains('bahan-pendukung-row')) {
        jumlahInput = row.querySelector('input[name="jumlah_pendukung[]"]');
        hargaInput = row.querySelector('input[name="harga_satuan_pendukung[]"]');
        totalInput = row.querySelector('input[name="total_pendukung[]"]');
        console.log('Bahan Pendukung row found:', { jumlahInput, hargaInput, totalInput });
    }
    
    if (jumlahInput && hargaInput && totalInput) {
        const jumlah = parseFloat(jumlahInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const total = jumlah * harga;
        totalInput.value = total;
        
        // Debug: Log calculation
        console.log('Calculation:', {
            jumlah: jumlah,
            harga: harga,
            total: total,
            rowType: row.classList.contains('bahan-baku-row') ? 'bahan baku' : 'bahan pendukung'
        });
        
        // Trigger grand total update
        hitungGrandTotal();
    } else {
        console.log('Missing inputs:', { jumlahInput, hargaInput, totalInput, row });
    }
}

// Wrapper function for event handlers
function hitungTotalWrapper() {
    console.log('hitungTotalWrapper called on:', this);
    hitungTotal(this);
}

function hitungKonversi() {
    // Implementasi konversi satuan jika diperlukan
    hitungTotal.call(this);
}

function hitungGrandTotal() {
    let grandTotal = 0;
    
    // Hitung total bahan baku
    const bahanBakuTotals = document.querySelectorAll('#bahanBakuRows input[name="total[]"]');
    bahanBakuTotals.forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });
    
    // Hitung total bahan pendukung
    const bahanPendukungTotals = document.querySelectorAll('#bahanPendukungRows input[name="total_pendukung[]"]');
    bahanPendukungTotals.forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });
    
    // Update grand total input
    const grandTotalInput = document.getElementById('grandTotal');
    if (grandTotalInput) {
        grandTotalInput.value = grandTotal;
        
        // Debug: Log grand total calculation
        console.log('Grand Total Calculation:', {
            bahanBakuCount: bahanBakuTotals.length,
            bahanPendukungCount: bahanPendukungTotals.length,
            grandTotal: grandTotal
        });
    }
}

// Initialize calculations on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing calculations');
    
    // Trigger initial calculation for existing rows
    const allInputs = document.querySelectorAll('input[name="jumlah[]"], input[name="harga_satuan[]"], input[name="jumlah_pendukung[]"], input[name="harga_satuan_pendukung[]"]');
    console.log('Found inputs for initialization:', allInputs.length, allInputs);
    
    allInputs.forEach((input, index) => {
        console.log(`Initializing input ${index}:`, input);
        hitungTotalWrapper.call(input);
    });
    
    // Also trigger calculation for any existing values
    setTimeout(() => {
        console.log('Delayed initialization trigger');
        document.querySelectorAll('input[name="jumlah[]"], input[name="harga_satuan[]"], input[name="jumlah_pendukung[]"], input[name="harga_satuan_pendukung[]"]').forEach(input => {
            if (input.value) {
                console.log('Triggering calculation for input with value:', input.value, input);
                hitungTotalWrapper.call(input);
            }
        });
    }, 100);
});

// Manual trigger function for debugging
window.manualCalculate = function() {
    console.log('Manual calculation triggered');
    document.querySelectorAll('input[name="jumlah[]"], input[name="harga_satuan[]"], input[name="jumlah_pendukung[]"], input[name="harga_satuan_pendukung[]"]').forEach(input => {
        hitungTotalWrapper.call(input);
    });
};

console.log('Calculation functions loaded. Use manualCalculate() in console to test.');

function updateRemoveButtons() {
    // Show/hide remove buttons based on row count
    const bahanBakuRows = document.querySelectorAll('#bahanBakuRows .bahan-baku-row');
    const bahanPendukungRows = document.querySelectorAll('#bahanPendukungRows .bahan-pendukung-row');
    
    bahanBakuRows.forEach((row, index) => {
        const button = row.querySelector('button');
        button.style.display = bahanBakuRows.length > 1 ? 'block' : 'none';
    });
    
    bahanPendukungRows.forEach((row, index) => {
        const button = row.querySelector('button');
        button.style.display = bahanPendukungRows.length > 1 ? 'block' : 'none';
    });
}
</script>
@endsection
