@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    padding: 20px;
}

.section-header {
    background: #f8f9fa;
    margin: -20px -20px 20px -20px;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
    border-bottom: 1px solid #dee2e6;
}

.conversion-examples {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
}

.item-row {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.calculation-section {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 6px;
    padding: 15px;
}

.total-section {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
}

/* Price formatting improvements */
.subtotal-display {
    font-weight: 600;
    color: #198754;
}

.input-group-text {
    font-weight: 600;
    background-color: #e9ecef;
    border-color: #ced4da;
}

#total_harga {
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    letter-spacing: 1px;
}

.price-per-unit, .sub-satuan-price {
    font-weight: 600;
    color: #198754;
}

/* Calculation section improvements */
.calculation-section .input-group-text {
    background-color: #6c757d;
    color: white;
    font-weight: 600;
    border-color: #6c757d;
}

.calculation-section input[readonly] {
    background-color: #e9ecef !important;
    font-weight: 600;
    color: #198754;
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

    @if ($errors->any())
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Terjadi kesalahan:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transaksi.pembelian.store') }}" method="POST" onsubmit="debugFormData(this)">
        @csrf
        
        <!-- Header Information -->
        <div class="form-section">
            <div class="section-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Pembelian</h6>
            </div>
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" class="form-select" required onchange="updateItemsBasedOnVendor(this)">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" data-kategori="{{ $vendor->kategori }}">{{ $vendor->nama_vendor }} ({{ $vendor->kategori }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Nomor Faktur Pembelian</label>
                    <input type="text" name="nomor_faktur" class="form-control" placeholder="0232000002" value="{{ old('nomor_faktur') }}">
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
                                    💵 {{ $kb->nama_akun }}
                                    (Saldo: Rp {{ number_format($kb->saldo_realtime ?? $kb->saldo_awal ?? 0, 0, ',', '.') }})
                                </option>
                            @endif
                        @endforeach
                        <option value="credit">💳 Kredit (Hutang)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Conversion Examples -->
        <div class="form-section">
            <div class="section-header">
                <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Contoh Konversi Satuan Pembelian</h6>
            </div>
            
            <div class="conversion-examples">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-primary mb-2">Satuan Bahan & Konversi</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>• 1 Liter = 1 kg (cairan utama)</li>
                            <li>• 1 Ton = 1000 kg (bahan utama)</li>
                            <li>• 1 Kg = 2 Kg (bahan khusus)</li>
                            <li>• 1 Kg = 1 Kg (bahan normal)</li>
                            <li>• 500 Gram = 0.5 Kg</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-success mb-2">Satuan Konversi</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>• 1 Tabung = 12 kg (tabung 12 kg)</li>
                            <li>• 1 Karung = 25 kg (karung 25 kg)</li>
                            <li>• 1 Kaleng = 5.5 kg (kaleng 5.5 kg)</li>
                            <li>• 1 Jerigen = 5 kg (jerigen 5 kg)</li>
                            <li>• 1 Karton = 0.5 kg (karton 0.5 kg)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-info mb-2">Estimasi Harga Satuan</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>• 1 kg = Rp 5000 = Rp 5000 Gram</li>
                            <li>• 1 Liter = Rp 6000 = Rp 6000 Liter</li>
                            <li>• 1 Kaleng = Rp 27500 = Rp 5000 Kg</li>
                            <li>• 1 Tabung = Rp 60000 = Rp 5000 Kg</li>
                            <li>• 1 Ton = Rp 5000000 = Rp 5000 Kg</li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <small><i class="fas fa-lightbulb me-1"></i> 
                    <strong>Tips:</strong> Sistem akan otomatis mengkonversi satuan pembelian ke satuan utama untuk perhitungan stok. Pastikan faktor konversi sudah benar.
                    </small>
                </div>
            </div>
        </div>

        <!-- Purchase Details -->
        <div class="form-section">
            <div class="section-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-box me-2"></i>Detail Barang Baku</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                    <i class="fas fa-plus me-1"></i>Tambah Barang
                </button>
            </div>
            
            <div id="itemRows">
                <div class="item-row" data-row="0">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Nama Item</label>
                            <select name="item_id[]" class="form-select item-select" disabled>
                                <option value="">-- Pilih Vendor Dulu --</option>
                            </select>
                            <!-- Hidden input untuk menyimpan tipe item -->
                            <input type="hidden" name="tipe_item[]" class="tipe-item-input" value="">
                            <!-- Hidden input untuk faktor konversi (tetap ada untuk kompatibilitas) -->
                            <input type="hidden" name="faktor_konversi[]" value="1">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control" placeholder="0" min="0.01" step="0.01" onchange="calculateRowTotal(this)">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Satuan Pembelian</label>
                            <select name="satuan_pembelian[]" class="form-select satuan-select" onchange="calculateRowTotal(this)">
                                <option value="">-- Pilih Satuan --</option>
                                @foreach($satuans as $satuan)
                                    <option value="{{ $satuan->id }}" data-nama="{{ $satuan->nama }}">{{ $satuan->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Harga per Satuan</label>
                            <input type="number" name="harga_satuan[]" class="form-control" placeholder="0" min="0" onchange="calculateRowTotal(this)">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Harga Total</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control subtotal-display" placeholder="0" readonly style="background-color: #f8f9fa; text-align: right;">
                                <input type="hidden" name="subtotal[]" class="subtotal-value" value="0">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeItemRow(this)" style="display: none;">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                    
                    <!-- Conversion Section -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <label class="form-label small">Satuan Utama Item</label>
                            <input type="text" name="satuan_utama[]" class="form-control form-control-sm" readonly>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Jumlah dalam Satuan Utama</label>
                            <input type="number" name="jumlah_satuan_utama[]" class="form-control form-control-sm" placeholder="0" step="1" onchange="calculateRowTotal(this)">
                            <small class="text-muted">Input manual jumlah dalam satuan utama</small>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Konversi Otomatis</label>
                            <div class="form-control form-control-sm bg-light" style="min-height: 31px; display: flex; align-items: center;">
                                <span class="conversion-result">10 Kg = 8 Ekor</span>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Harga per Satuan Utama</label>
                            <div class="form-control form-control-sm bg-light" style="min-height: 31px; display: flex; align-items: center;">
                                <span class="price-per-unit">Rp 0</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sub Satuan Conversion Section -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white py-2">
                                    <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Konversi Sub Satuan</h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="sub-satuan-info" style="display: none;">
                                        <!-- Data Konversi Sub Satuan Saat Ini (Info Only) -->
                                        <div class="alert alert-info mb-3">
                                            <h6 class="mb-2"><i class="fas fa-info-circle me-1"></i>Data Konversi Sub Satuan Saat Ini</h6>
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <strong>Sub Satuan 1:</strong><br>
                                                    <span class="sub-satuan-1-text">-</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Sub Satuan 2:</strong><br>
                                                    <span class="sub-satuan-2-text">-</span>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Sub Satuan 3:</strong><br>
                                                    <span class="sub-satuan-3-text">-</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Pilihan Sub Satuan untuk Pembelian Kali Ini -->
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">Pilih Sub Satuan untuk Pembelian Kali Ini</label>
                                                <select name="sub_satuan_pilihan[]" class="form-select sub-satuan-select" onchange="updateSubSatuanKonversi(this)">
                                                    <option value="">-- Gunakan Satuan Utama --</option>
                                                </select>
                                                <small class="text-muted">Pilih sub satuan yang akan digunakan untuk pembelian ini</small>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label small fw-bold">Konversi untuk Pembelian Ini</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-info text-white fw-bold conversion-prefix">1 Satuan Utama =</span>
                                                    <input type="number" name="manual_conversion_factor[]" class="form-control fw-bold text-center conversion-input" placeholder="1" step="0.01" onchange="updateManualConversion(this)">
                                                    <span class="input-group-text bg-success text-white fw-bold conversion-suffix">Sub Satuan</span>
                                                </div>
                                                <small class="text-muted">Sesuaikan konversi sesuai kebutuhan pembelian Anda</small>
                                            </div>
                                        </div>
                                        
                                        <!-- Input Jumlah dan Harga -->
                                        <div class="row g-3 mt-2">
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">Jumlah dalam Sub Satuan</label>
                                                <input type="number" name="jumlah_sub_satuan[]" class="form-control jumlah-sub-satuan fw-bold" placeholder="0" step="0.01" onchange="updateSubSatuanFromInput(this)">
                                                <small class="text-muted">Input jumlah dalam sub satuan yang dipilih</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">Harga per Sub Satuan</label>
                                                <div class="form-control bg-warning fw-bold d-flex align-items-center" style="min-height: 38px;">
                                                    <span class="sub-satuan-price">Rp 0</span>
                                                </div>
                                                <small class="text-muted">Harga per unit sub satuan yang dipilih</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">Total Harga Sub Satuan</label>
                                                <div class="form-control bg-info text-dark fw-bold d-flex align-items-center" style="min-height: 38px;">
                                                    <span class="sub-satuan-total">Rp 0</span>
                                                </div>
                                                <small class="text-muted">Total harga untuk jumlah sub satuan</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="no-sub-satuan-info text-center text-muted py-3">
                                        <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                        <strong>Pilih item terlebih dahulu</strong><br>
                                        <small>untuk melihat sub satuan yang tersedia</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculation Section -->
        <div class="form-section">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Perhitungan Biaya</h6>
                </div>
                <div class="card-body">
                    <div class="calculation-section">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Subtotal</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="subtotal_display" class="form-control" readonly style="background-color: #f8f9fa; text-align: right;">
                                    <input type="hidden" name="subtotal_display" id="subtotal" value="0">
                                </div>
                                <small class="text-muted">Total semua item</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Biaya Kirim</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="biaya_kirim" id="biaya_kirim" class="form-control" placeholder="0" min="0" onchange="calculateTotal()" onblur="formatCurrencyInput(this)" style="text-align: right;">
                                </div>
                                <small class="text-muted">Ongkos kirim (opsional)</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">PPN (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text">%</span>
                                    <input type="number" name="ppn_persen" id="ppn_persen" class="form-control" placeholder="0" min="0" max="100" step="0.01" onchange="calculateTotal()" style="text-align: right;">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('ppn_persen').value = 11; calculateTotal();" title="PPN 11%">11%</button>
                                </div>
                                <small class="text-muted">Pajak Pertambahan Nilai</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">PPN Nominal</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="ppn_nominal_display" class="form-control" readonly style="background-color: #f8f9fa; text-align: right;">
                                    <input type="hidden" name="ppn_nominal" id="ppn_nominal" value="0">
                                </div>
                                <small class="text-muted">Nilai PPN dalam rupiah</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculation Summary -->
        <div class="form-section">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Ringkasan Perhitungan</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal Item:</span>
                                <span class="fw-bold" id="summary_subtotal">Rp 0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span>Biaya Kirim:</span>
                                <span class="fw-bold" id="summary_biaya_kirim">Rp 0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <span>PPN:</span>
                                <span class="fw-bold" id="summary_ppn">Rp 0</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between border-top pt-2">
                                <span class="fw-bold">Total Keseluruhan:</span>
                                <span class="fw-bold text-success" id="summary_total">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Section -->
        <div class="form-section">
            <div class="total-section">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Total Harga Pembelian</h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <h2 class="text-success mb-0 fw-bold" id="total_harga" style="font-size: 2.5rem;">Rp 0</h2>
                        <input type="hidden" name="total_harga" id="total_harga_input" value="0">
                        <small class="text-muted">Total keseluruhan pembelian</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Keterangan -->
        <div class="form-section">
            <div class="section-header">
                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Keterangan</h6>
            </div>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Simpan Pembelian
            </button>
        </div>
    </form>
</div>

<script>
let rowCount = 0;

// Sub satuan data from controller
const subSatuanData = @json($subSatuanData);

// Debug: Log the sub satuan data received from controller
console.log('=== SUB SATUAN DATA FROM CONTROLLER ===');
console.log('Full subSatuanData:', subSatuanData);
if (subSatuanData.bahan_baku && subSatuanData.bahan_baku[5]) {
    console.log('Ayam Potong (ID 5) data:', subSatuanData.bahan_baku[5]);
}
console.log('=== END DEBUG ===');

// Format number with clean decimal display
function formatCleanNumber(num) {
    if (num === 0) return '0';
    
    // Round to 2 decimal places
    const rounded = Math.round(num * 100) / 100;
    
    // If it's a whole number, return without decimal
    if (rounded % 1 === 0) {
        return rounded.toString();
    }
    
    // If it has decimals, show max 2 decimal places
    return rounded.toFixed(2).replace(/\.?0+$/, '');
}

// Format number with thousand separators for Indonesian currency
function formatNumber(num) {
    if (isNaN(num) || num === null || num === undefined) return '0';
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(Math.round(num));
}

// Format currency with Rp prefix
function formatCurrency(num) {
    if (isNaN(num) || num === null || num === undefined) return 'Rp 0';
    return 'Rp ' + formatNumber(num);
}

// Initialize display formatting on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calculation displays
    document.getElementById('subtotal_display').value = '0';
    document.getElementById('ppn_nominal_display').value = '0';
    
    // Initialize summary displays
    document.getElementById('summary_subtotal').textContent = 'Rp 0';
    document.getElementById('summary_biaya_kirim').textContent = 'Rp 0';
    document.getElementById('summary_ppn').textContent = 'Rp 0';
    document.getElementById('summary_total').textContent = 'Rp 0';
    
    // Add input formatting for biaya kirim
    const biayaKirimInput = document.getElementById('biaya_kirim');
    biayaKirimInput.addEventListener('input', function() {
        // Remove any non-numeric characters except decimal point
        this.value = this.value.replace(/[^0-9.]/g, '');
    });
});

// Format input field on blur (for Biaya Kirim)
function formatCurrencyInput(input) {
    const value = parseFloat(input.value) || 0;
    if (value > 0) {
        input.value = value; // Keep as number for calculation
    }
}

// Update sub satuan information when item is selected
function updateSubSatuanInfo(itemSelect) {
    const row = itemSelect.closest('.item-row');
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const itemId = selectedOption.value;
    const tipeItem = selectedOption.getAttribute('data-tipe');
    
    const subSatuanInfo = row.querySelector('.sub-satuan-info');
    const noSubSatuanInfo = row.querySelector('.no-sub-satuan-info');
    const subSatuanSelect = row.querySelector('.sub-satuan-select');
    
    // Debug: log data yang diterima
    console.log('=== DEBUG SUB SATUAN INFO UPDATE ===');
    console.log('Item ID:', itemId);
    console.log('Tipe Item:', tipeItem);
    console.log('Available subSatuanData:', subSatuanData);
    
    if (itemId && tipeItem && subSatuanData[tipeItem] && subSatuanData[tipeItem][itemId]) {
        const data = subSatuanData[tipeItem][itemId];
        
        // Debug: log data spesifik item
        console.log('Data untuk item ini:', data);
        console.log('Sub Satuan 1 faktor konversi:', data.sub_satuan_1 ? data.sub_satuan_1.faktor_konversi : 'null');
        console.log('Sub Satuan 2 faktor konversi:', data.sub_satuan_2 ? data.sub_satuan_2.faktor_konversi : 'null');
        console.log('Sub Satuan 3 faktor konversi:', data.sub_satuan_3 ? data.sub_satuan_3.faktor_konversi : 'null');
        
        // Show sub satuan info, hide no-info message
        subSatuanInfo.style.display = 'block';
        noSubSatuanInfo.style.display = 'none';
        
        // Update sub satuan displays
        const subSatuan1Text = row.querySelector('.sub-satuan-1-text');
        const subSatuan2Text = row.querySelector('.sub-satuan-2-text');
        const subSatuan3Text = row.querySelector('.sub-satuan-3-text');
        
        subSatuan1Text.textContent = data.sub_satuan_1 ? 
            `${data.sub_satuan_1.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_1.faktor_konversi} ${data.sub_satuan_1.nama})` : '-';
        subSatuan2Text.textContent = data.sub_satuan_2 ? 
            `${data.sub_satuan_2.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_2.faktor_konversi} ${data.sub_satuan_2.nama})` : '-';
        subSatuan3Text.textContent = data.sub_satuan_3 ? 
            `${data.sub_satuan_3.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_3.faktor_konversi} ${data.sub_satuan_3.nama})` : '-';
        
        // Populate sub satuan select options
        subSatuanSelect.innerHTML = '<option value="">-- Pilih Sub Satuan --</option>';
        
        if (data.sub_satuan_1) {
            subSatuanSelect.innerHTML += `<option value="${data.sub_satuan_1.id}|${data.sub_satuan_1.nama}" data-id="${data.sub_satuan_1.id}" data-nama="${data.sub_satuan_1.nama}" data-faktor="${data.sub_satuan_1.faktor_konversi}">
                ${data.sub_satuan_1.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_1.faktor_konversi} ${data.sub_satuan_1.nama})
            </option>`;
        }
        
        if (data.sub_satuan_2) {
            subSatuanSelect.innerHTML += `<option value="${data.sub_satuan_2.id}|${data.sub_satuan_2.nama}" data-id="${data.sub_satuan_2.id}" data-nama="${data.sub_satuan_2.nama}" data-faktor="${data.sub_satuan_2.faktor_konversi}">
                ${data.sub_satuan_2.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_2.faktor_konversi} ${data.sub_satuan_2.nama})
            </option>`;
        }
        
        if (data.sub_satuan_3) {
            subSatuanSelect.innerHTML += `<option value="${data.sub_satuan_3.id}|${data.sub_satuan_3.nama}" data-id="${data.sub_satuan_3.id}" data-nama="${data.sub_satuan_3.nama}" data-faktor="${data.sub_satuan_3.faktor_konversi}">
                ${data.sub_satuan_3.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_3.faktor_konversi} ${data.sub_satuan_3.nama})
            </option>`;
        }
        
        // Reset sub satuan conversion display
        updateSubSatuanKonversi(subSatuanSelect);
        
        console.log('=== END DEBUG ===');
        
    } else {
        // Hide sub satuan info, show no-info message
        subSatuanInfo.style.display = 'none';
        noSubSatuanInfo.style.display = 'block';
        
        console.log('Data sub satuan tidak ditemukan untuk item ini');
        console.log('Available data:', subSatuanData);
    }
}

// Update sub satuan conversion when sub satuan is selected
function updateSubSatuanKonversi(subSatuanSelect) {
    const row = subSatuanSelect.closest('.item-row');
    const selectedOption = subSatuanSelect.options[subSatuanSelect.selectedIndex];
    
    const conversionPrefix = row.querySelector('.conversion-prefix');
    const conversionInput = row.querySelector('.conversion-input');
    const conversionSuffix = row.querySelector('.conversion-suffix');
    const subSatuanPrice = row.querySelector('.sub-satuan-price');
    const subSatuanTotal = row.querySelector('.sub-satuan-total');
    const jumlahSubSatuanInput = row.querySelector('.jumlah-sub-satuan');
    
    // Get item info to show proper unit names
    const itemSelect = row.querySelector('.item-select');
    const selectedItemOption = itemSelect.options[itemSelect.selectedIndex];
    const satuanUtama = selectedItemOption ? selectedItemOption.getAttribute('data-satuan') || 'Satuan Utama' : 'Satuan Utama';
    
    if (selectedOption.value && selectedOption.getAttribute('data-faktor')) {
        const subSatuanNama = selectedOption.getAttribute('data-nama');
        const subSatuanFaktor = parseFloat(selectedOption.getAttribute('data-faktor'));
        
        // Update conversion display with proper unit names
        conversionPrefix.textContent = `1 ${satuanUtama} =`;
        conversionInput.value = subSatuanFaktor;
        conversionSuffix.textContent = subSatuanNama;
        
        // Enable conversion input
        conversionInput.disabled = false;
        
        // Calculate initial values
        updateManualConversion(conversionInput);
        
    } else {
        // Reset to default state
        conversionPrefix.textContent = '1 Satuan Utama =';
        conversionInput.value = '';
        conversionInput.disabled = true;
        conversionSuffix.textContent = 'Sub Satuan';
        
        jumlahSubSatuanInput.value = '';
        subSatuanPrice.textContent = 'Rp 0';
        subSatuanTotal.textContent = 'Rp 0';
    }
}

// Update conversion when user manually changes conversion factor
function updateManualConversion(conversionInput) {
    const row = conversionInput.closest('.item-row');
    const subSatuanSelect = row.querySelector('.sub-satuan-select');
    const selectedOption = subSatuanSelect.options[subSatuanSelect.selectedIndex];
    
    if (!selectedOption.value) return;
    
    const jumlahSatuanUtama = parseFloat(row.querySelector('input[name="jumlah_satuan_utama[]"]').value) || 0;
    const manualFaktor = parseFloat(conversionInput.value) || 0;
    const jumlahSubSatuanInput = row.querySelector('.jumlah-sub-satuan');
    
    if (jumlahSatuanUtama > 0 && manualFaktor > 0) {
        // Calculate quantity in sub satuan using manual conversion factor
        const jumlahDalamSubSatuan = jumlahSatuanUtama * manualFaktor;
        jumlahSubSatuanInput.value = formatCleanNumber(jumlahDalamSubSatuan);
        
        // Update prices
        updateSubSatuanPrices(row);
    }
}

// Update sub satuan prices and totals
function updateSubSatuanPrices(row) {
    const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]').value) || 0;
    const harga = parseFloat(row.querySelector('input[name="harga_satuan[]"]').value) || 0;
    const subtotal = jumlah * harga; // Total harga pembelian
    const jumlahSubSatuan = parseFloat(row.querySelector('.jumlah-sub-satuan').value) || 0;
    
    const subSatuanPrice = row.querySelector('.sub-satuan-price');
    const subSatuanTotal = row.querySelector('.sub-satuan-total');
    
    if (jumlahSubSatuan > 0 && subtotal > 0) {
        // Calculate price per sub satuan: TOTAL HARGA ÷ JUMLAH SUB SATUAN
        const hargaPerSubSatuan = subtotal / jumlahSubSatuan;
        subSatuanPrice.textContent = formatCurrency(hargaPerSubSatuan);
        
        // Calculate total for sub satuan
        const totalSubSatuan = jumlahSubSatuan * hargaPerSubSatuan;
        subSatuanTotal.textContent = formatCurrency(totalSubSatuan);
    } else {
        subSatuanPrice.textContent = 'Rp 0';
        subSatuanTotal.textContent = 'Rp 0';
        subSatuanTotal.textContent = 'Rp 0';
    }
}

// Update sub satuan when manual input changes
function updateSubSatuanFromInput(input) {
    const row = input.closest('.item-row');
    updateSubSatuanPrices(row);
}

// Add new item row
function addItemRow() {
    rowCount++;
    const itemRows = document.getElementById('itemRows');
    const newRow = document.querySelector('.item-row').cloneNode(true);
    
    newRow.setAttribute('data-row', rowCount);
    newRow.querySelectorAll('input, select').forEach(input => {
        if (input.type === 'number') {
            input.value = '';
        } else if (input.tagName === 'SELECT' && !input.classList.contains('satuan-select')) {
            input.selectedIndex = 0;
        }
        if (input.name === 'subtotal[]') {
            input.value = '0';
        }
        if (input.classList.contains('subtotal-value')) {
            input.value = '0';
        }
        if (input.classList.contains('subtotal-display')) {
            input.value = '0';
        }
        if (input.name === 'jumlah_satuan_utama[]') {
            input.value = '';
        }
        if (input.name === 'jumlah_sub_satuan[]') {
            input.value = '';
        }
    });
    
    // Reset item select based on current vendor selection
    const vendorSelect = document.querySelector('select[name="vendor_id"]');
    const itemSelect = newRow.querySelector('.item-select');
    const tipeItemInput = newRow.querySelector('input[name="tipe_item[]"]');
    
    if (vendorSelect.value) {
        // If vendor is already selected, populate items for new row
        const selectedVendorOption = vendorSelect.options[vendorSelect.selectedIndex];
        const kategori = selectedVendorOption.getAttribute('data-kategori');
        
        itemSelect.innerHTML = '<option value="">-- Pilih Item --</option>';
        
        if (kategori === 'Bahan Baku') {
            itemSelect.disabled = false;
            tipeItemInput.value = 'bahan_baku';
            @foreach ($bahanBakus as $bb)
                itemSelect.innerHTML += '<option value="{{ $bb->id }}" data-satuan="{{ $bb->satuan->nama ?? 'Unit' }}" data-tipe="bahan_baku">{{ $bb->nama_bahan }}</option>';
            @endforeach
        } else if (kategori === 'Bahan Pendukung') {
            itemSelect.disabled = false;
            tipeItemInput.value = 'bahan_pendukung';
            @foreach ($bahanPendukungs as $bp)
                itemSelect.innerHTML += '<option value="{{ $bp->id }}" data-satuan="{{ $bp->satuanRelation->nama ?? 'Unit' }}" data-tipe="bahan_pendukung">{{ $bp->nama_bahan }}</option>';
            @endforeach
        }
        
        // Set up onchange handler
        const row = newRow;
        const satuanUtamaInput = row.querySelector('input[name="satuan_utama[]"]');
        itemSelect.onchange = function() {
            const selectedItemOption = this.options[this.selectedIndex];
            const satuan = selectedItemOption.getAttribute('data-satuan') || 'Unit';
            satuanUtamaInput.value = satuan;
            updateSubSatuanInfo(this);
            calculateRowTotal(this);
        };
    } else {
        itemSelect.disabled = true;
        itemSelect.innerHTML = '<option value="">-- Pilih Vendor Dulu --</option>';
        tipeItemInput.value = '';
    }
    
    // Show delete button for new rows
    const deleteBtn = newRow.querySelector('button[onclick="removeItemRow(this)"]');
    deleteBtn.style.display = 'block';
    
    // Reset conversion displays
    newRow.querySelector('.conversion-result').textContent = '0 Unit (dari 0 Unit)';
    newRow.querySelector('.price-per-unit').textContent = 'Rp 0';
    
    // Reset sub satuan displays
    newRow.querySelector('.sub-satuan-info').style.display = 'none';
    newRow.querySelector('.no-sub-satuan-info').style.display = 'block';
    newRow.querySelector('.jumlah-sub-satuan').value = '';
    newRow.querySelector('.sub-satuan-price').textContent = 'Rp 0';
    
    itemRows.appendChild(newRow);
    updateDeleteButtons();
}
// Remove item row
function removeItemRow(button) {
    button.closest('.item-row').remove();
    updateDeleteButtons();
    calculateTotal();
}

// Update delete button visibility
function updateDeleteButtons() {
    const rows = document.querySelectorAll('.item-row');
    rows.forEach((row, index) => {
        const deleteBtn = row.querySelector('button[onclick="removeItemRow(this)"]');
        deleteBtn.style.display = rows.length > 1 ? 'block' : 'none';
    });
}

// Update items based on selected vendor category
function updateItemsBasedOnVendor(vendorSelect) {
    const selectedOption = vendorSelect.options[vendorSelect.selectedIndex];
    const kategori = selectedOption.getAttribute('data-kategori');
    
    // Update all item selects in all rows
    document.querySelectorAll('.item-select').forEach(itemSelect => {
        const row = itemSelect.closest('.item-row');
        const satuanUtamaInput = row.querySelector('input[name="satuan_utama[]"]');
        const tipeItemInput = row.querySelector('input[name="tipe_item[]"]');
        
        itemSelect.innerHTML = '<option value="">-- Pilih Item --</option>';
        
        if (kategori === 'Bahan Baku') {
            itemSelect.disabled = false;
            tipeItemInput.value = 'bahan_baku';
            @foreach ($bahanBakus as $bb)
                itemSelect.innerHTML += '<option value="{{ $bb->id }}" data-satuan="{{ $bb->satuan->nama ?? 'Unit' }}" data-tipe="bahan_baku">{{ $bb->nama_bahan }}</option>';
            @endforeach
        } else if (kategori === 'Bahan Pendukung') {
            itemSelect.disabled = false;
            tipeItemInput.value = 'bahan_pendukung';
            @foreach ($bahanPendukungs as $bp)
                itemSelect.innerHTML += '<option value="{{ $bp->id }}" data-satuan="{{ $bp->satuanRelation->nama ?? 'Unit' }}" data-tipe="bahan_pendukung">{{ $bp->nama_bahan }}</option>';
            @endforeach
        } else {
            itemSelect.disabled = true;
            tipeItemInput.value = '';
        }
        
        // Set up onchange handler for item selection
        itemSelect.onchange = function() {
            const selectedItemOption = this.options[this.selectedIndex];
            const satuan = selectedItemOption.getAttribute('data-satuan') || 'Unit';
            satuanUtamaInput.value = satuan;
            updateSubSatuanInfo(this);
            calculateRowTotal(this);
        };
        
        // Reset sub satuan info when vendor changes
        const subSatuanInfo = row.querySelector('.sub-satuan-info');
        const noSubSatuanInfo = row.querySelector('.no-sub-satuan-info');
        subSatuanInfo.style.display = 'none';
        noSubSatuanInfo.style.display = 'block';
    });
}

// Remove the old updateItemOptions function since we don't need it anymore

// Calculate row total
function calculateRowTotal(input) {
    const row = input.closest('.item-row');
    const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]').value) || 0;
    const harga = parseFloat(row.querySelector('input[name="harga_satuan[]"]').value) || 0;
    let jumlahSatuanUtama = parseFloat(row.querySelector('input[name="jumlah_satuan_utama[]"]').value) || 0;
    
    // Jika jumlah_satuan_utama kosong, hitung otomatis berdasarkan faktor konversi
    if (jumlahSatuanUtama === 0 && jumlah > 0) {
        const faktorKonversi = parseFloat(row.querySelector('input[name="faktor_konversi[]"]').value) || 1;
        jumlahSatuanUtama = jumlah * faktorKonversi;
        row.querySelector('input[name="jumlah_satuan_utama[]"]').value = Math.round(jumlahSatuanUtama);
    }
    
    // Calculate subtotal: jumlah × harga per satuan
    const subtotal = jumlah * harga;
    
    // Update hidden input with raw value
    row.querySelector('.subtotal-value').value = subtotal.toFixed(2);
    
    // Update display with formatted value
    row.querySelector('.subtotal-display').value = formatNumber(subtotal);
    
    // Update conversion displays
    // Get selected satuan pembelian name for display
    const satuanSelect = row.querySelector('select[name="satuan_pembelian[]"]');
    const selectedSatuan = satuanSelect.options[satuanSelect.selectedIndex];
    const satuanPembelianName = selectedSatuan ? selectedSatuan.getAttribute('data-nama') : 'Unit';
    
    // Get satuan utama name
    const satuanUtama = row.querySelector('input[name="satuan_utama[]"]').value || 'Unit';
    
    // Show conversion: jumlah pembelian = jumlah satuan utama
    if (jumlah > 0 && jumlahSatuanUtama > 0) {
        row.querySelector('.conversion-result').textContent = `${formatNumber(jumlah)} ${satuanPembelianName} = ${formatNumber(jumlahSatuanUtama)} ${satuanUtama}`;
    } else {
        row.querySelector('.conversion-result').textContent = `0 ${satuanPembelianName} = 0 ${satuanUtama}`;
    }
    
    // Calculate price per satuan utama: TOTAL HARGA ÷ JUMLAH SATUAN UTAMA
    const hargaPerSatuanUtama = jumlahSatuanUtama > 0 ? subtotal / jumlahSatuanUtama : 0;
    row.querySelector('.price-per-unit').textContent = formatCurrency(hargaPerSatuanUtama);
    
    // Update sub satuan conversion if sub satuan is selected
    const subSatuanSelect = row.querySelector('.sub-satuan-select');
    updateSubSatuanKonversi(subSatuanSelect);
    
    // Update sub satuan prices
    updateSubSatuanPrices(row);
    
    calculateTotal();
}
// Calculate total
function calculateTotal() {
    let subtotal = 0;
    
    // Sum all row subtotals
    document.querySelectorAll('.subtotal-value').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const biayaKirim = parseFloat(document.getElementById('biaya_kirim').value) || 0;
    const ppnPersen = parseFloat(document.getElementById('ppn_persen').value) || 0;
    
    const ppnNominal = (subtotal + biayaKirim) * (ppnPersen / 100);
    const totalHarga = subtotal + biayaKirim + ppnNominal;
    
    // Update displays with proper formatting
    document.getElementById('subtotal').value = subtotal;
    document.getElementById('subtotal_display').value = formatNumber(subtotal);
    document.getElementById('ppn_nominal').value = ppnNominal;
    document.getElementById('ppn_nominal_display').value = formatNumber(ppnNominal);
    document.getElementById('total_harga').textContent = formatCurrency(totalHarga);
    document.getElementById('total_harga_input').value = totalHarga;
    
    // Update summary section
    document.getElementById('summary_subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('summary_biaya_kirim').textContent = formatCurrency(biayaKirim);
    document.getElementById('summary_ppn').textContent = formatCurrency(ppnNominal);
    document.getElementById('summary_total').textContent = formatCurrency(totalHarga);
}

// Debug form data before submission
function debugFormData(form) {
    const formData = new FormData(form);
    console.log('Form data being submitted:');
    
    // Log all form data
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Specifically check arrays
    const itemIds = formData.getAll('item_id[]');
    const tipeItems = formData.getAll('tipe_item[]');
    const jumlahs = formData.getAll('jumlah[]');
    const satuanPembelians = formData.getAll('satuan_pembelian[]');
    const hargaSatuans = formData.getAll('harga_satuan[]');
    const subtotals = formData.getAll('subtotal[]');
    const faktorKonversis = formData.getAll('faktor_konversi[]');
    const jumlahSatuanUtamas = formData.getAll('jumlah_satuan_utama[]');
    const manualConversionFactors = formData.getAll('manual_conversion_factor[]');
    const subSatuanPilihans = formData.getAll('sub_satuan_pilihan[]');
    const jumlahSubSatuans = formData.getAll('jumlah_sub_satuan[]');
    
    console.log('Arrays:');
    console.log('item_id[]:', itemIds);
    console.log('tipe_item[]:', tipeItems);
    console.log('jumlah[]:', jumlahs);
    console.log('satuan_pembelian[]:', satuanPembelians);
    console.log('harga_satuan[]:', hargaSatuans);
    console.log('subtotal[]:', subtotals);
    console.log('jumlah_satuan_utama[]:', jumlahSatuanUtamas);
    console.log('manual_conversion_factor[]:', manualConversionFactors);
    console.log('sub_satuan_pilihan[]:', subSatuanPilihans);
    console.log('jumlah_sub_satuan[]:', jumlahSubSatuans);
    
    // Check if any required fields are empty
    let hasValidItems = false;
    for (let i = 0; i < itemIds.length; i++) {
        if (itemIds[i] && tipeItems[i] && jumlahs[i] && satuanPembelians[i] && hargaSatuans[i]) {
            hasValidItems = true;
            break;
        }
    }
    
    if (!hasValidItems) {
        alert('Peringatan: Tidak ada item yang valid terdeteksi!');
        console.error('No valid items found!');
    }
    
    return true; // Allow form submission
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateDeleteButtons();
    
    // Update sub satuan information when item is selected
    function updateSubSatuanInfo(itemSelect) {
        const row = itemSelect.closest('.item-row');
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const itemId = selectedOption.value;
        const tipeItem = selectedOption.getAttribute('data-tipe');
        
        const subSatuanInfo = row.querySelector('.sub-satuan-info');
        const noSubSatuanInfo = row.querySelector('.no-sub-satuan-info');
        const subSatuanSelect = row.querySelector('.sub-satuan-select');
        
        if (itemId && tipeItem && subSatuanData[tipeItem] && subSatuanData[tipeItem][itemId]) {
            const data = subSatuanData[tipeItem][itemId];
            
            // Show sub satuan info, hide no-info message
            subSatuanInfo.style.display = 'block';
            noSubSatuanInfo.style.display = 'none';
            
            // Update sub satuan displays
            const subSatuan1Text = row.querySelector('.sub-satuan-1-text');
            const subSatuan2Text = row.querySelector('.sub-satuan-2-text');
            const subSatuan3Text = row.querySelector('.sub-satuan-3-text');
            
            subSatuan1Text.textContent = data.sub_satuan_1 ? 
                `${data.sub_satuan_1.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_1.faktor_konversi} ${data.sub_satuan_1.nama})` : '-';
            subSatuan2Text.textContent = data.sub_satuan_2 ? 
                `${data.sub_satuan_2.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_2.faktor_konversi} ${data.sub_satuan_2.nama})` : '-';
            subSatuan3Text.textContent = data.sub_satuan_3 ? 
                `${data.sub_satuan_3.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_3.faktor_konversi} ${data.sub_satuan_3.nama})` : '-';
            
            // Populate sub satuan select options
            subSatuanSelect.innerHTML = '<option value="">-- Gunakan Satuan Utama --</option>';
            
            if (data.sub_satuan_1) {
                subSatuanSelect.innerHTML += `<option value="${data.sub_satuan_1.id}|${data.sub_satuan_1.nama}" data-id="${data.sub_satuan_1.id}" data-nama="${data.sub_satuan_1.nama}" data-faktor="${data.sub_satuan_1.faktor_konversi}">
                    ${data.sub_satuan_1.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_1.faktor_konversi} ${data.sub_satuan_1.nama})
                </option>`;
            }
            
            if (data.sub_satuan_2) {
                subSatuanSelect.innerHTML += `<option value="${data.sub_satuan_2.id}|${data.sub_satuan_2.nama}" data-id="${data.sub_satuan_2.id}" data-nama="${data.sub_satuan_2.nama}" data-faktor="${data.sub_satuan_2.faktor_konversi}">
                    ${data.sub_satuan_2.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_2.faktor_konversi} ${data.sub_satuan_2.nama})
                </option>`;
            }
            
            if (data.sub_satuan_3) {
                subSatuanSelect.innerHTML += `<option value="${data.sub_satuan_3.id}|${data.sub_satuan_3.nama}" data-id="${data.sub_satuan_3.id}" data-nama="${data.sub_satuan_3.nama}" data-faktor="${data.sub_satuan_3.faktor_konversi}">
                    ${data.sub_satuan_3.nama} (1 ${data.satuan_utama} = ${data.sub_satuan_3.faktor_konversi} ${data.sub_satuan_3.nama})
                </option>`;
            }
            
            // Reset sub satuan conversion display
            updateSubSatuanKonversi(subSatuanSelect);
            
        } else {
            // Hide sub satuan info, show no-info message
            subSatuanInfo.style.display = 'none';
            noSubSatuanInfo.style.display = 'block';
        }
    }

    // Update sub satuan conversion when sub satuan is selected
    function updateSubSatuanKonversi(subSatuanSelect) {
        const row = subSatuanSelect.closest('.item-row');
        const selectedOption = subSatuanSelect.options[subSatuanSelect.selectedIndex];
        
        const subSatuanConversion = row.querySelector('.sub-satuan-conversion');
        const subSatuanPrice = row.querySelector('.sub-satuan-price');
        
        const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name="harga_satuan[]"]').value) || 0;
        const faktorKonversi = parseFloat(row.querySelector('input[name="faktor_konversi[]"]').value) || 1;
        
        if (selectedOption.value && selectedOption.getAttribute('data-faktor')) {
            const subSatuanNama = selectedOption.getAttribute('data-nama');
            const subSatuanFaktor = parseFloat(selectedOption.getAttribute('data-faktor'));
            
            // Calculate conversion to sub satuan
            const jumlahDalamSatuanUtama = jumlah * faktorKonversi;
            const jumlahDalamSubSatuan = jumlahDalamSatuanUtama * subSatuanFaktor;
            const hargaPerSubSatuan = subSatuanFaktor > 0 ? harga / (faktorKonversi * subSatuanFaktor) : 0;
            
            // Get selected satuan pembelian name for display
            const satuanSelect = row.querySelector('select[name="satuan_pembelian[]"]');
            const selectedSatuan = satuanSelect.options[satuanSelect.selectedIndex];
            const satuanPembelianName = selectedSatuan ? selectedSatuan.getAttribute('data-nama') : 'Unit';
            
            subSatuanConversion.textContent = `${formatCleanNumber(jumlahDalamSubSatuan)} ${subSatuanNama} (dari ${formatNumber(jumlah)} ${satuanPembelianName})`;
            subSatuanPrice.textContent = formatCurrency(hargaPerSubSatuan);
            
        } else {
            subSatuanConversion.textContent = 'Pilih sub satuan untuk melihat konversi';
            subSatuanPrice.textContent = 'Rp 0';
        }
    }

    // Update the existing addItemRow function to include sub satuan reset
    const originalAddItemRow = addItemRow;
    addItemRow = function() {
        originalAddItemRow();
        
        // Reset sub satuan displays for new row
        const newRow = document.querySelector('.item-row:last-child');
        newRow.querySelector('.sub-satuan-info').style.display = 'none';
        newRow.querySelector('.no-sub-satuan-info').style.display = 'block';
        newRow.querySelector('.sub-satuan-conversion').textContent = 'Pilih sub satuan untuk melihat konversi';
        newRow.querySelector('.sub-satuan-price').textContent = 'Rp 0';
    };

    // Update the existing updateItemsBasedOnVendor function to include sub satuan reset
    const originalUpdateItemsBasedOnVendor = updateItemsBasedOnVendor;
    updateItemsBasedOnVendor = function(vendorSelect) {
        originalUpdateItemsBasedOnVendor(vendorSelect);
        
        // Reset sub satuan info when vendor changes
        document.querySelectorAll('.item-row').forEach(row => {
            const subSatuanInfo = row.querySelector('.sub-satuan-info');
            const noSubSatuanInfo = row.querySelector('.no-sub-satuan-info');
            subSatuanInfo.style.display = 'none';
            noSubSatuanInfo.style.display = 'block';
            
            // Update item select onchange to include sub satuan update
            const itemSelect = row.querySelector('.item-select');
            const originalOnChange = itemSelect.onchange;
            itemSelect.onchange = function() {
                if (originalOnChange) originalOnChange.call(this);
                updateSubSatuanInfo(this);
            };
        });
    };

    // Update the existing calculateRowTotal function to include sub satuan update
    const originalCalculateRowTotal = calculateRowTotal;
    calculateRowTotal = function(input) {
        originalCalculateRowTotal(input);
        
        // Update sub satuan conversion if sub satuan is selected
        const row = input.closest('.item-row');
        const subSatuanSelect = row.querySelector('.sub-satuan-select');
        updateSubSatuanKonversi(subSatuanSelect);
    };

    // Update the existing debugFormData function to include sub satuan data
    const originalDebugFormData = debugFormData;
    debugFormData = function(form) {
        const result = originalDebugFormData(form);
        
        // Log sub satuan data
        const formData = new FormData(form);
        const subSatuanPilihans = formData.getAll('sub_satuan_pilihan[]');
        console.log('sub_satuan_pilihan[]:', subSatuanPilihans);
        
        return result;
    };
});
</script>

@endsection