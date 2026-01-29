@extends('layouts.app')

@section('title', 'Edit Pembelian')

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

.form-select, .form-control {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-select:focus, .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.card {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn {
    border-radius: 0.375rem;
    font-weight: 500;
}

.total-input {
    font-weight: bold;
    background-color: #f8f9fa;
    border-color: #6c757d;
}

.table-responsive {
    max-height: 400px;
    overflow-y: auto;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0">
            <i class="fas fa-edit me-2"></i>Edit Pembelian
        </h2>
        <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Notifications -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('transaksi.pembelian.update', $pembelian->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pembelian</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
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
                            
                            if (kategori === 'bahan baku') {
                                bahanBaku.style.setProperty('display', 'block', 'important');
                            } else if (kategori === 'bahan pendukung') {
                                bahanPendukung.style.setProperty('display', 'block', 'important');
                            } else {
                                // Default: show both
                                bahanBaku.style.setProperty('display', 'block', 'important');
                                bahanPendukung.style.setProperty('display', 'block', 'important');
                            }
                            ">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" 
                                            data-kategori="{{ $vendor->kategori }}"
                                            {{ $pembelian->vendor_id == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ $pembelian->tanggal }}" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="bank_id" class="form-select" required>
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            @foreach ($kasbank as $bank)
                                <option value="{{ $bank->id }}" {{ $pembelian->bank_id == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->nama_akun }} - {{ $bank->kode_akun }} (Saldo Akhir: Rp {{ number_format($currentBalances[$bank->kode_akun] ?? 0, 0, ',', '.') }})
                                </option>
                            @endforeach
                            <option value="credit" {{ $pembelian->bank_id === null ? 'selected' : '' }}>Kredit (Utang)</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="1">{{ $pembelian->keterangan ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bahan Baku Section -->
        <div class="card mb-4" id="cardBahanBaku">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-box me-2"></i>Bahan Baku
                </h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanBakuRow()">
                    <i class="fas fa-plus me-1"></i>Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div id="bahanBakuRows">
                    <!-- Dynamic rows will be inserted here -->
                    @if($pembelian->details->where('bahan_baku_id', '!=', null)->count() > 0)
                        @foreach($pembelian->details->where('bahan_baku_id', '!=', null) as $index => $detail)
                            <div class="row g-3 bahan-baku-row" data-row-index="{{ $index }}">
                                <div class="col-md-3">
                                    <label class="form-label">Bahan Baku</label>
                                    <select name="bahan_baku_id[]" class="form-select" onchange="updateBahanBakuInfo(this)">
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach ($bahanBakus as $bb)
                                            <option value="{{ $bb->id }}" 
                                                    data-harga="{{ $bb->harga_satuan ?? 0 }}" 
                                                    data-satuan="{{ $bb->satuan->nama ?? 'Tidak Diketahui' }}"
                                                    data-satuan-id="{{ $bb->satuan_id ?? '' }}"
                                                    data-satuan-utama="{{ $bb->satuan ?? 'KG' }}"
                                                    {{ $detail->bahan_baku_id == $bb->id ? 'selected' : '' }}>
                                                {{ $bb->nama_bahan }} - Rp {{ number_format($bb->harga_satuan ?? 0, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" name="jumlah[]" class="form-control" value="{{ $detail->jumlah }}" min="0.01" step="0.01" onchange="hitungKonversi(this)">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Satuan</label>
                                    <select name="satuan_pembelian[]" class="form-select" onchange="hitungKonversi(this)">
                                        <option value="">-- Pilih --</option>
                                        <option value="kg">Kilogram (kg)</option>
                                        <option value="gram">Gram (g)</option>
                                        <option value="liter">Liter (L)</option>
                                        <option value="mililiter">Mililiter (ml)</option>
                                        <option value="pcs">Pieces (pcs)</option>
                                        <option value="buah">Buah</option>
                                        <option value="pack">Pack</option>
                                        <option value="pak">Pak</option>
                                        <option value="box">Box</option>
                                        <option value="botol">Botol</option>
                                        <option value="dus">Dus</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Harga/Satuan</label>
                                    <input type="number" name="harga_satuan_pembelian[]" class="form-control" value="{{ $detail->harga_satuan }}" min="0" onchange="hitungKonversi(this)">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Total</label>
                                    <input type="text" class="form-control total-input" readonly value="{{ number_format($detail->jumlah * $detail->harga_satuan, 0, ',', '.') }}">
                                </div>
                            </div>
                        @endforeach
                    @else
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
                                <input type="number" name="jumlah[]" class="form-control" value="1" min="0.01" step="0.01" onchange="hitungKonversi(this)">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Satuan</label>
                                <select name="satuan_pembelian[]" class="form-select" onchange="hitungKonversi(this)">
                                    <option value="">-- Pilih --</option>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="gram">Gram (g)</option>
                                    <option value="liter">Liter (L)</option>
                                    <option value="mililiter">Mililiter (ml)</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="buah">Buah</option>
                                    <option value="pack">Pack</option>
                                    <option value="pak">Pak</option>
                                    <option value="box">Box</option>
                                    <option value="botol">Botol</option>
                                    <option value="dus">Dus</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Harga/Satuan</label>
                                <input type="number" name="harga_satuan_pembelian[]" class="form-control" value="0" min="0" onchange="hitungKonversi(this)">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control total-input" readonly value="0">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bahan Pendukung Section -->
        <div class="card mb-4" id="cardBahanPendukung">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>Bahan Pendukung
                </h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanPendukungRow()">
                    <i class="fas fa-plus me-1"></i>Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div id="bahanPendukungRows">
                    <!-- Dynamic rows will be inserted here -->
                    @if($pembelian->details->where('bahan_pendukung_id', '!=', null)->count() > 0)
                        @foreach($pembelian->details->where('bahan_pendukung_id', '!=', null) as $index => $detail)
                            <div class="row g-3 bahan-pendukung-row" data-row-index="{{ $index }}">
                                <div class="col-md-4">
                                    <label class="form-label">Bahan Pendukung</label>
                                    <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                                        <option value="">-- Pilih Bahan Pendukung --</option>
                                        @foreach ($bahanPendukungs as $bp)
                                            <option value="{{ $bp->id }}" 
                                                    data-harga="{{ $bp->harga_satuan ?? 0 }}" 
                                                    data-satuan="{{ $bp->satuanRelation->nama ?? 'Tidak Diketahui' }}"
                                                    {{ $detail->bahan_pendukung_id == $bp->id ? 'selected' : '' }}>
                                                {{ $bp->nama_bahan }} - Rp {{ number_format($bp->harga_satuan ?? 0, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" name="jumlah_pendukung[]" class="form-control" value="{{ $detail->jumlah }}" min="0.01" step="0.01" onchange="hitungTotalPendukung(this)">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Harga/Satuan</label>
                                    <input type="number" name="harga_satuan_pendukung[]" class="form-control" value="{{ $detail->harga_satuan }}" min="0" onchange="hitungTotalPendukung(this)">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Total</label>
                                    <input type="text" class="form-control total-input" readonly value="{{ number_format($detail->jumlah * $detail->harga_satuan, 0, ',', '.') }}">
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row g-3 bahan-pendukung-row" data-row-index="0">
                            <div class="col-md-4">
                                <label class="form-label">Bahan Pendukung</label>
                                <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                                    <option value="">-- Pilih Bahan Pendukung --</option>
                                    @foreach ($bahanPendukungs as $bp)
                                        <option value="{{ $bp->id }}" 
                                                data-harga="{{ $bp->harga_satuan ?? 0 }}" 
                                                data-satuan="{{ $bp->satuanRelation->nama ?? 'Tidak Diketahui' }}">
                                            {{ $bp->nama_bahan }} - Rp {{ number_format($bp->harga_satuan ?? 0, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" onchange="hitungTotalPendukung(this)">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Harga/Satuan</label>
                                <input type="number" name="harga_satuan_pendukung[]" class="form-control" value="0" min="0" onchange="hitungTotalPendukung(this)">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control total-input" readonly value="0">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Pembelian
                </button>
                <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize vendor selection
    const vendorSelect = document.getElementById('vendorSelect');
    if (vendorSelect) {
        vendorSelect.dispatchEvent(new Event('change'));
    }

    // Initialize existing rows
    initializeExistingRows();
});

function initializeExistingRows() {
    // Initialize existing bahan baku rows
    document.querySelectorAll('.bahan-baku-row').forEach(row => {
        const select = row.querySelector('select[name="bahan_baku_id[]"]');
        if (select && select.value) {
            updateBahanBakuInfo(select);
        }
    });

    // Initialize existing bahan pendukung rows
    document.querySelectorAll('.bahan-pendukung-row').forEach(row => {
        const select = row.querySelector('select[name="bahan_pendukung_id[]"]');
        if (select && select.value) {
            updateBahanPendukungInfo(select);
        }
    });
}

function updateBahanBakuInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.bahan-baku-row');
    
    // Update satuan info
    const satuanText = selectedOption.getAttribute('data-satuan') || '';
    const satuanUtama = selectedOption.getAttribute('data-satuan-utama') || 'KG';
    const satuanSelect = row.querySelector('select[name="satuan_pembelian[]"]');
    const hargaInput = row.querySelector('input[name="harga_satuan_pembelian[]"]');
    const totalInput = row.querySelector('.total-input');
    
    // Update satuan select
    if (satuanSelect) {
        satuanSelect.value = '';
    }
    
    // Update harga input
    if (hargaInput && !hargaInput.value) {
        const currentHarga = selectedOption.getAttribute('data-harga');
        if (currentHarga) {
            hargaInput.value = currentHarga;
        }
    }
    
    // Update satuan utama display
    const satuanUtamaSpan = row.querySelector('.satuan-utama');
    if (satuanUtamaSpan) {
        satuanUtamaSpan.textContent = satuanUtama;
    }
    
    hitungKonversi(row);
}

function updateBahanPendukungInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.bahan-pendukung-row');
    const hargaInput = row.querySelector('input[name="harga_satuan_pendukung[]"]');
    const satuanText = selectedOption.getAttribute('data-satuan') || '';
    
    // Update harga input
    if (hargaInput && !hargaInput.value) {
        const currentHarga = selectedOption.getAttribute('data-harga');
        if (currentHarga) {
            hargaInput.value = currentHarga;
        }
    }
    
    hitungTotalPendukung(row);
}

function hitungKonversi(element) {
    const row = element.closest('.bahan-baku-row') || element;
    const jumlahInput = row.querySelector('input[name="jumlah[]"]');
    const hargaInput = row.querySelector('input[name="harga_satuan_pembelian[]"]');
    const totalInput = row.querySelector('.total-input');
    
    if (jumlahInput && hargaInput && totalInput) {
        const jumlah = parseFloat(jumlahInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const total = jumlah * harga;
        totalInput.value = total.toLocaleString('id-ID');
    }
}

function hitungTotalPendukung(element) {
    const row = element.closest('.bahan-pendukung-row') || element;
    const jumlahInput = row.querySelector('input[name="jumlah_pendukung[]"]');
    const hargaInput = row.querySelector('input[name="harga_satuan_pendukung[]"]');
    const totalInput = row.querySelector('.total-input');
    
    if (jumlahInput && hargaInput && totalInput) {
        const jumlah = parseFloat(jumlahInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const total = jumlah * harga;
        totalInput.value = total.toLocaleString('id-ID');
    }
}

function addBahanBakuRow() {
    const container = document.getElementById('bahanBakuRows');
    const rowCount = container.querySelectorAll('.bahan-baku-row').length;
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-baku-row';
    newRow.setAttribute('data-row-index', rowCount);
    
    newRow.innerHTML = `
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
            <input type="number" name="jumlah[]" class="form-control" value="1" min="0.01" step="0.01" onchange="hitungKonversi(this)">
        </div>
        <div class="col-md-2">
            <label class="form-label">Satuan</label>
            <select name="satuan_pembelian[]" class="form-select" onchange="hitungKonversi(this)">
                <option value="">-- Pilih --</option>
                <option value="kg">Kilogram (kg)</option>
                <option value="gram">Gram (g)</option>
                <option value="liter">Liter (L)</option>
                <option value="mililiter">Mililiter (ml)</option>
                <option value="pcs">Pieces (pcs)</option>
                <option value="buah">Buah</option>
                <option value="pack">Pack</option>
                <option value="pak">Pak</option>
                <option value="box">Box</option>
                <option value="botol">Botol</option>
                <option value="dus">Dus</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Harga/Satuan</label>
            <input type="number" name="harga_satuan_pembelian[]" class="form-control" value="0" min="0" onchange="hitungKonversi(this)">
        </div>
        <div class="col-md-3">
            <label class="form-label">Total</label>
            <input type="text" class="form-control total-input" readonly value="0">
        </div>
    `;
    
    container.appendChild(newRow);
}

function addBahanPendukungRow() {
    const container = document.getElementById('bahanPendukungRows');
    const rowCount = container.querySelectorAll('.bahan-pendukung-row').length;
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-pendukung-row';
    newRow.setAttribute('data-row-index', rowCount);
    
    newRow.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Bahan Pendukung</label>
            <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                <option value="">-- Pilih Bahan Pendukung --</option>
                @foreach ($bahanPendukungs as $bp)
                    <option value="{{ $bp->id }}" 
                            data-harga="{{ $bp->harga_satuan ?? 0 }}" 
                            data-satuan="{{ $bp->satuanRelation->nama ?? 'Tidak Diketahui' }}">
                        {{ $bp->nama_bahan }} - Rp {{ number_format($bp->harga_satuan ?? 0, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" onchange="hitungTotalPendukung(this)">
        </div>
        <div class="col-md-3">
            <label class="form-label">Harga/Satuan</label>
            <input type="number" name="harga_satuan_pendukung[]" class="form-control" value="0" min="0" onchange="hitungTotalPendukung(this)">
        </div>
        <div class="col-md-2">
            <label class="form-label">Total</label>
            <input type="text" class="form-control total-input" readonly value="0">
        </div>
    `;
    
    container.appendChild(newRow);
}
</script>
@endsection
