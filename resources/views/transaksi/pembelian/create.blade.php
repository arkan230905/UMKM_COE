@extends('layouts.app')

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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-shopping-cart me-2"></i>Tambah Pembelian
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
        </div>
    @endif

    <form action="{{ route('transaksi.pembelian.store') }}" method="POST">
        @csrf
        
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
                    <div class="col-md-3">
                        <label class="form-label">Nomor Faktur Pembelian</label>
                        <input type="text" name="nomor_faktur" class="form-control" placeholder="Masukkan nomor faktur" value="{{ old('nomor_faktur') }}">
                        <small class="text-muted">Nomor faktur dari vendor (opsional)</small>
                    </div>
                    <div class="col-md-3">
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
                                        (Saldo: Rp {{ number_format($kb->saldo_awal ?? 0, 0, ',', '.') }})
                                    </option>
                                @endif
                            @endforeach
                            <option value="credit">💳 Kredit (Hutang)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" id="cardBahanBaku" style="display: none !important;">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-box me-2"></i>Detail Bahan Baku</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanBakuRow()">
                    <i class="fas fa-plus me-1"></i>Tambah Baris
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
                                            data-satuan-utama="{{ $bb->satuan->nama ?? 'KG' }}">
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
                            <small class="text-muted">Satuan utama: <span class="satuan-utama">-</span></small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga/Satuan</label>
                            <input type="number" name="harga_satuan_pembelian[]" class="form-control" value="0" min="0" onchange="hitungKonversi(this)">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga/Satuan Utama</label>
                            <input type="number" name="harga_satuan[]" class="form-control" value="0" min="0" readonly>
                            <small class="text-muted">Harga per satuan utama (setelah konversi)</small>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanBakuRow(this)" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" id="cardBahanPendukung" style="display: none !important;">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Detail Bahan Pendukung</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="addBahanPendukungRow()">
                    <i class="fas fa-plus me-1"></i>Tambah Baris
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
                            <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" onchange="hitungTotal()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan</label>
                            <input type="text" name="satuan_pendukung[]" class="form-control" readonly placeholder="Pilih bahan pendukung">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga/Satuan</label>
                            <input type="number" name="harga_satuan_pendukung[]" class="form-control" value="0" min="0" onchange="hitungTotal()">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanPendukungRow(this)" style="display: none;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Keterangan</h6>
            </div>
            <div class="card-body">
                <textarea name="keterangan" class="form-control" rows="2" placeholder="Keterangan (opsional)"></textarea>
            </div>
        </div>

        <!-- Total Pembelian -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>Total Pembelian
                        </h5>
                    </div>
                    <div class="col-md-4 text-end">
                        <h4 class="mb-0 text-primary" id="totalPembelian">Rp 0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan</button>
        </div>
    </form>
</div>

<script>
// Format angka ke format Indonesia
function formatAngka(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

// Hitung total pembelian
function hitungTotal() {
    let total = 0;
    
    // Hitung total bahan baku
    const bahanBakuRows = document.querySelectorAll('#bahanBakuRows .bahan-baku-row');
    bahanBakuRows.forEach(row => {
        const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]')?.value || 0);
        const harga = parseFloat(row.querySelector('input[name="harga_satuan_pembelian[]"]')?.value || 0);
        if (jumlah > 0 && harga > 0) {
            total += jumlah * harga;
        }
    });
    
    // Hitung total bahan pendukung
    const bahanPendukungRows = document.querySelectorAll('#bahanPendukungRows .bahan-pendukung-row');
    bahanPendukungRows.forEach(row => {
        const jumlah = parseFloat(row.querySelector('input[name="jumlah_pendukung[]"]')?.value || 0);
        const harga = parseFloat(row.querySelector('input[name="harga_satuan_pendukung[]"]')?.value || 0);
        if (jumlah > 0 && harga > 0) {
            total += jumlah * harga;
        }
    });
    
    // Update total display
    document.getElementById('totalPembelian').textContent = 'Rp ' + formatAngka(total);
    
    // Show/hide remove buttons
    updateRemoveButtons();
}

// Add new bahan baku row
function addBahanBakuRow() {
    const container = document.getElementById('bahanBakuRows');
    const rowCount = container.children.length;
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-baku-row';
    newRow.setAttribute('data-row-index', rowCount);
    
    // Get bahan baku options from first row
    const firstRow = container.querySelector('.bahan-baku-row');
    const firstSelect = firstRow.querySelector('select[name="bahan_baku_id[]"]');
    const options = firstSelect.innerHTML;
    
    newRow.innerHTML = `
        <div class="col-md-3">
            <label class="form-label">Bahan Baku</label>
            <select name="bahan_baku_id[]" class="form-select" onchange="updateBahanBakuInfo(this)">
                ${options}
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
            <small class="text-muted">Satuan utama: <span class="satuan-utama">-</span></small>
        </div>
        <div class="col-md-2">
            <label class="form-label">Harga/Satuan</label>
            <input type="number" name="harga_satuan_pembelian[]" class="form-control" value="0" min="0" onchange="hitungKonversi(this)">
        </div>
        <div class="col-md-3">
            <label class="form-label">Harga/Satuan Utama</label>
            <input type="number" name="harga_satuan[]" class="form-control" value="0" min="0" readonly>
            <small class="text-muted">Harga per satuan utama (setelah konversi)</small>
        </div>
        <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanBakuRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newRow);
    
    // Show remove button for all rows
    updateRemoveButtons();
    
    // Update total
    hitungTotal();
}

// Add new bahan pendukung row
function addBahanPendukungRow() {
    const container = document.getElementById('bahanPendukungRows');
    const rowCount = container.children.length;
    const newRow = document.createElement('div');
    newRow.className = 'row g-3 bahan-pendukung-row';
    newRow.setAttribute('data-row-index', rowCount);
    
    // Get bahan pendukung options from first row
    const firstRow = container.querySelector('.bahan-pendukung-row');
    const firstSelect = firstRow.querySelector('select[name="bahan_pendukung_id[]"]');
    const options = firstSelect.innerHTML;
    
    newRow.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Bahan Pendukung</label>
            <select name="bahan_pendukung_id[]" class="form-select" onchange="updateBahanPendukungInfo(this)">
                ${options}
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Jumlah</label>
            <input type="number" name="jumlah_pendukung[]" class="form-control" value="1" min="0.01" step="0.01" onchange="hitungTotal()">
        </div>
        <div class="col-md-2">
            <label class="form-label">Satuan</label>
            <input type="text" name="satuan_pendukung[]" class="form-control" readonly placeholder="Pilih bahan pendukung">
        </div>
        <div class="col-md-3">
            <label class="form-label">Harga/Satuan</label>
            <input type="number" name="harga_satuan_pendukung[]" class="form-control" value="0" min="0" onchange="hitungTotal()">
        </div>
        <div class="col-md-1">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeBahanPendukungRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(newRow);
    
    // Show remove button for all rows
    updateRemoveButtons();
    
    // Update total
    hitungTotal();
}

// Remove bahan baku row
function removeBahanBakuRow(button) {
    const row = button.closest('.bahan-baku-row');
    row.remove();
    updateRemoveButtons();
    hitungTotal();
}

// Remove bahan pendukung row
function removeBahanPendukungRow(button) {
    const row = button.closest('.bahan-pendukung-row');
    row.remove();
    updateRemoveButtons();
    hitungTotal();
}

// Update remove buttons visibility
function updateRemoveButtons() {
    // Update bahan baku remove buttons
    const bahanBakuRows = document.querySelectorAll('#bahanBakuRows .bahan-baku-row');
    bahanBakuRows.forEach((row, index) => {
        const removeBtn = row.querySelector('button');
        if (removeBtn) {
            removeBtn.style.display = bahanBakuRows.length > 1 ? 'block' : 'none';
        }
    });
    
    // Update bahan pendukung remove buttons
    const bahanPendukungRows = document.querySelectorAll('#bahanPendukungRows .bahan-pendukung-row');
    bahanPendukungRows.forEach((row, index) => {
        const removeBtn = row.querySelector('button');
        if (removeBtn) {
            removeBtn.style.display = bahanPendukungRows.length > 1 ? 'block' : 'none';
        }
    });
}

function updateBahanBakuInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.row');
    
    // Get the input fields in the same row
    const satuanUtamaSpan = row.querySelector('.satuan-utama');
    const satuanSelect = row.querySelector('select[name="satuan_pembelian[]"]');
    const hargaInput = row.querySelector('input[name="harga_satuan_pembelian[]"]');
    const hargaUtamaInput = row.querySelector('input[name="harga_satuan[]"]');
    
    if (selectedOption.value) {
        // Update satuan utama
        const satuanUtama = selectedOption.getAttribute('data-satuan-utama') || 'KG';
        satuanUtamaSpan.textContent = satuanUtama;
        
        // Set default satuan pembelian ke satuan utama (lowercase)
        const satuanUtamaLower = satuanUtama.toLowerCase();
        for (let i = 0; i < satuanSelect.options.length; i++) {
            if (satuanSelect.options[i].value === satuanUtamaLower) {
                satuanSelect.selectedIndex = i;
                break;
            }
        }
        
        // Update harga pembelian
        const harga = selectedOption.getAttribute('data-harga') || 0;
        hargaInput.value = parseFloat(harga);
        
        // Hitung konversi
        hitungKonversi(row);
        
        // Update total
        hitungTotal();
    } else {
        // Clear fields if no selection
        satuanUtamaSpan.textContent = '-';
        satuanSelect.selectedIndex = 0;
        hargaInput.value = 0;
        hargaUtamaInput.value = 0;
        
        // Update total
        hitungTotal();
    }
}

function hitungKonversi(element) {
    const row = element.closest('.row');
    
    // Get values from the same row
    const bahanBakuSelect = row.querySelector('select[name="bahan_baku_id[]"]');
    const jumlahInput = row.querySelector('input[name="jumlah[]"]');
    const satuanSelect = row.querySelector('select[name="satuan_pembelian[]"]');
    const hargaInput = row.querySelector('input[name="harga_satuan_pembelian[]"]');
    const hargaUtamaInput = row.querySelector('input[name="harga_satuan[]"]');
    
    if (!bahanBakuSelect.value || !jumlahInput.value || !satuanSelect.value || !hargaInput.value) {
        hargaUtamaInput.value = 0;
        return;
    }
    
    const selectedOption = bahanBakuSelect.options[bahanBakuSelect.selectedIndex];
    const satuanUtama = selectedOption.getAttribute('data-satuan-utama') || 'KG';
    
    const jumlah = parseFloat(jumlahInput.value);
    const satuanPembelian = satuanSelect.value;
    const hargaPembelian = parseFloat(hargaInput.value);
    
    // Simple conversion logic
    let konversiFaktor = 1;
    let satuanNormal = satuanPembelian;
    
    // Konversi ke satuan utama (KG untuk contoh)
    if (satuanUtama === 'KG' || satuanUtama === 'Kilogram') {
        switch(satuanPembelian) {
            case 'kg':
                konversiFaktor = 1;
                break;
            case 'gram':
                konversiFaktor = 0.001; // 1 gram = 0.001 kg
                break;
            case 'liter':
            case 'l':
                konversiFaktor = 1; // Asumi 1L = 1kg
                break;
            case 'mililiter':
            case 'ml':
                konversiFaktor = 0.001; // 1ml = 0.001L = 0.001kg
                break;
            case 'pcs':
            case 'buah':
            case 'pack':
            case 'pak':
            case 'box':
            case 'botol':
            case 'dus':
                konversiFaktor = 1; // Tidak bisa konversi, asumsikan 1 pcs = 1 kg
                break;
            default:
                konversiFaktor = 1;
        }
    }
    
    // Hitung jumlah dalam satuan utama
    const jumlahUtama = jumlah * konversiFaktor;
    
    // Hitung harga per satuan utama
    const hargaPerSatuanUtama = hargaPembelian / konversiFaktor;
    
    // Update harga per satuan utama
    hargaUtamaInput.value = hargaPerSatuanUtama.toFixed(2);
    
    // Update total
    hitungTotal();
}

function updateBahanPendukungInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const row = select.closest('.row');
    
    // Get the input fields in the same row
    const satuanInput = row.querySelector('input[name="satuan_pendukung[]"]');
    const hargaInput = row.querySelector('input[name="harga_satuan_pendukung[]"]');
    
    if (selectedOption.value) {
        // Update satuan
        satuanInput.value = selectedOption.getAttribute('data-satuan') || '';
        
        // Update harga
        const harga = selectedOption.getAttribute('data-harga') || 0;
        hargaInput.value = parseFloat(harga);
        
        // Update total
        hitungTotal();
    } else {
        // Clear fields if no selection
        satuanInput.value = '';
        hargaInput.value = 0;
        
        // Update total
        hitungTotal();
    }
}

// Add event listeners for input changes to update total
document.addEventListener('DOMContentLoaded', function() {
    // Add change event listeners to all input fields
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="jumlah[]"], input[name="harga_satuan_pembelian[]"], input[name="jumlah_pendukung[]"], input[name="harga_satuan_pendukung[]"]')) {
            hitungTotal();
        }
    });
    
    // Add input event listeners for real-time updates
    document.addEventListener('input', function(e) {
        if (e.target.matches('input[name="jumlah[]"], input[name="harga_satuan_pembelian[]"], input[name="jumlah_pendukung[]"], input[name="harga_satuan_pendukung[]"]')) {
            hitungTotal();
        }
    });
});
</script>


@endsection