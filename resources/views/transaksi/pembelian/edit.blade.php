@extends('layouts.app')

@section('title', 'Edit Pembelian')

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
            <i class="fas fa-edit me-2"></i>Edit Pembelian #{{ $pembelian->nomor_pembelian ?? $pembelian->id }}
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

    <form action="{{ route('transaksi.pembelian.update', $pembelian->id) }}" method="POST" enctype="multipart/form-data" onsubmit="debugFormData(this)">
        @csrf
        @method('PUT')
        
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
                            <option value="{{ $vendor->id }}" data-kategori="{{ $vendor->kategori }}" {{ $pembelian->vendor_id == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->nama_vendor }} ({{ $vendor->kategori }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Nomor Faktur Pembelian</label>
                    <input type="text" name="nomor_faktur" class="form-control" placeholder="0232000002" value="{{ old('nomor_faktur', $pembelian->nomor_faktur) }}">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Bukti Faktur</label>
                    <input type="file" name="bukti_faktur" class="form-control" accept="image/*,.pdf" onchange="previewBuktiFaktur(this)">
                    <small class="text-muted">Upload gambar atau PDF (opsional)</small>
                    @if($pembelian->bukti_faktur)
                        <div class="mt-2">
                            <small class="text-info">Bukti faktur saat ini: 
                                <a href="{{ asset($pembelian->bukti_faktur) }}" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-eye me-1"></i>Lihat
                                </a>
                            </small>
                        </div>
                    @endif
                    <div id="bukti_faktur_preview" class="mt-2" style="display: none;">
                        <img id="bukti_faktur_img" src="#" alt="Preview" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $pembelian->tanggal->format('Y-m-d') }}" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="bank_id" class="form-select" required>
                        <option value="">-- Pilih Metode Pembayaran --</option>
                        @foreach($kasbank as $kb)
                            @if($kb->nama_akun)
                                <option value="{{ $kb->id }}" {{ $pembelian->bank_id == $kb->id ? 'selected' : '' }}>
                                    {{ $kb->nama_akun }}
                                    (Saldo: Rp {{ number_format($kb->saldo_realtime ?? $kb->saldo_awal ?? 0, 0, ',', '.') }})
                                </option>
                            @endif
                        @endforeach
                        <option value="credit" {{ ($pembelian->payment_method === 'credit' || $pembelian->bank_id === null) ? 'selected' : '' }}>Kredit (Hutang)</option>
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
                            <li>1 Liter = 1 kg (cairan utama)</li>
                            <li>1 Ton = 1000 kg (bahan utama)</li>
                            <li>1 Kg = 2 Kg (bahan khusus)</li>
                            <li>1 Kg = 1 Kg (bahan normal)</li>
                            <li>500 Gram = 0.5 Kg</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-success mb-2">Satuan Konversi</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>1 Tabung = 12 kg (tabung 12 kg)</li>
                            <li>1 Karung = 25 kg (karung 25 kg)</li>
                            <li>1 Kaleng = 5.5 kg (kaleng 5.5 kg)</li>
                            <li>1 Jerigen = 5 kg (jerigen 5 kg)</li>
                            <li>1 Karton = 0.5 kg (karton 0.5 kg)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-info mb-2">Estimasi Harga Satuan</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>1 kg = Rp 5000 = Rp 5000 Gram</li>
                            <li>1 Liter = Rp 6000 = Rp 6000 Liter</li>
                            <li>1 Kaleng = Rp 27500 = Rp 5000 Kg</li>
                            <li>1 Tabung = Rp 60000 = Rp 5000 Kg</li>
                            <li>1 Ton = Rp 5000000 = Rp 5000 Kg</li>
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
                <!-- Existing items will be loaded here -->
                @foreach($pembelian->details as $index => $detail)
                <div class="item-row" data-row="{{ $index }}">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Nama Item</label>
                            <select name="item_id[]" class="form-select item-select" onchange="updateItemInfo(this)">
                                <option value="">-- Pilih Vendor Dulu --</option>
                                @if($detail->bahan_baku_id)
                                    @foreach($bahanBakus as $bb)
                                        @if($bb->vendor_id == $pembelian->vendor_id)
                                            <option value="{{ $bb->id }}" data-tipe="bahan_baku" data-nama="{{ $bb->nama_bahan }}" data-satuan="{{ $bb->satuan->nama ?? '' }}" {{ $detail->bahan_baku_id == $bb->id ? 'selected' : '' }}>
                                                {{ $bb->nama_bahan }}
                                            </option>
                                        @endif
                                    @endforeach
                                @elseif($detail->bahan_pendukung_id)
                                    @foreach($bahanPendukungs as $bp)
                                        @if($bp->vendor_id == $pembelian->vendor_id)
                                            <option value="{{ $bp->id }}" data-tipe="bahan_pendukung" data-nama="{{ $bp->nama_bahan }}" data-satuan="{{ $bp->satuanRelation->nama ?? '' }}" {{ $detail->bahan_pendukung_id == $bp->id ? 'selected' : '' }}>
                                                {{ $bp->nama_bahan }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <!-- Hidden input untuk menyimpan tipe item -->
                            <input type="hidden" name="tipe_item[]" class="tipe-item-input" value="{{ $detail->bahan_baku_id ? 'bahan_baku' : 'bahan_pendukung' }}">
                            <!-- Hidden input untuk faktor konversi (tetap ada untuk kompatibilitas) -->
                            <input type="hidden" name="faktor_konversi[]" value="1">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control" placeholder="0" min="0.01" step="0.01" value="{{ $detail->jumlah }}" onchange="calculateRowTotal(this)">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Satuan Pembelian</label>
                            <select name="satuan_pembelian[]" class="form-select satuan-select" onchange="calculateRowTotal(this)">
                                <option value="">-- Pilih Satuan --</option>
                                @foreach($satuans as $satuan)
                                    <option value="{{ $satuan->id }}" data-nama="{{ $satuan->nama }}" {{ $detail->satuan_pembelian_id == $satuan->id ? 'selected' : '' }}>
                                        {{ $satuan->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Harga per Satuan</label>
                            <input type="text" name="harga_satuan[]" class="form-control price-input" placeholder="0" value="{{ number_format($detail->harga_satuan, 0, ',', '.') }}" oninput="formatPriceInput(this)" onchange="calculateRowTotal(this)">
                            <input type="hidden" name="harga_satuan_raw[]" class="price-raw" value="{{ $detail->harga_satuan }}">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Harga Total</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control subtotal-display" placeholder="0" readonly style="background-color: #f8f9fa; text-align: right;" value="{{ number_format($detail->jumlah * $detail->harga_satuan, 0, ',', '.') }}">
                                <input type="hidden" name="subtotal[]" class="subtotal-value" value="{{ $detail->jumlah * $detail->harga_satuan }}">
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeItemRow(this)" {{ $loop->first ? 'style="display: none;"' : '' }}>
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                    
                    <!-- Conversion Section -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <label class="form-label small">Satuan Utama Item</label>
                            <input type="text" name="satuan_utama[]" class="form-control form-control-sm" readonly value="{{ $detail->bahan_baku ? $detail->bahan_baku->satuan->nama : ($detail->bahan_pendukung ? $detail->bahan_pendukung->satuanRelation->nama : '') }}">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Jumlah dalam Satuan Utama</label>
                            <input type="number" name="jumlah_satuan_utama[]" class="form-control form-control-sm" placeholder="0" step="1" value="{{ $detail->jumlah_satuan_utama ?? '' }}" onchange="calculateRowTotal(this)">
                            <small class="text-muted">Input manual jumlah dalam satuan utama</small>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Konversi Otomatis</label>
                            <div class="form-control form-control-sm bg-light" style="min-height: 31px; display: flex; align-items: center;">
                                <span class="conversion-result">{{ $detail->jumlah }} {{ $detail->satuanRelation->nama ?? '' }} = {{ $detail->jumlah_satuan_utama ?? $detail->jumlah }} {{ $detail->bahan_baku ? $detail->bahan_baku->satuan->nama : ($detail->bahan_pendukung ? $detail->bahan_pendukung->satuanRelation->nama : '') }}</span>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Harga per Satuan Utama</label>
                            <div class="form-control form-control-sm bg-light" style="min-height: 31px; display: flex; align-items: center;">
                                <span class="price-per-unit">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
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
                                    <input type="text" id="subtotal_display" class="form-control" readonly style="background-color: #f8f9fa; text-align: right;" value="{{ number_format($pembelian->subtotal, 0, ',', '.') }}">
                                    <input type="hidden" name="subtotal_display" id="subtotal" value="{{ $pembelian->subtotal }}">
                                </div>
                                <small class="text-muted">Total semua item</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Biaya Kirim</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="biaya_kirim" id="biaya_kirim" class="form-control" placeholder="0" min="0" value="{{ $pembelian->biaya_kirim ?? 0 }}" onchange="calculateTotal()" onblur="formatCurrencyInput(this)" style="text-align: right;">
                                </div>
                                <small class="text-muted">Ongkos kirim (opsional)</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">PPN (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text">%</span>
                                    <input type="number" name="ppn_persen" id="ppn_persen" class="form-control" placeholder="0" min="0" max="100" step="0.01" value="{{ $pembelian->ppn_persen ?? 0 }}" onchange="calculateTotal()" style="text-align: right;">
                                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('ppn_persen').value = 11; calculateTotal();" title="PPN 11%">11%</button>
                                </div>
                                <small class="text-muted">Pajak Pertambahan Nilai</small>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">PPN Nominal</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="ppn_nominal_display" class="form-control" readonly style="background-color: #f8f9fa; text-align: right;" value="{{ number_format($pembelian->ppn_nominal, 0, ',', '.') }}">
                                    <input type="hidden" name="ppn_nominal" id="ppn_nominal" value="{{ $pembelian->ppn_nominal }}">
                                </div>
                                <small class="text-muted">Nilai PPN dalam rupiah</small>
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
                        <h2 class="text-success mb-0 fw-bold" id="total_harga" style="font-size: 2.5rem;">Rp {{ number_format($pembelian->total_harga, 0, ',', '.') }}</h2>
                        <input type="hidden" name="total_harga" id="total_harga_input" value="{{ $pembelian->total_harga }}">
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
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan (opsional)">{{ $pembelian->keterangan ?? '' }}</textarea>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Update Pembelian
            </button>
        </div>
    </form>
</div>

<script>
let rowCount = {{ $pembelian->details->count() }};

// Sub satuan data from controller
const subSatuanData = @json($subSatuanData);

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

// Format price input with thousand separator
function formatPriceInput(input) {
    let value = input.value;
    
    // Remove all non-numeric characters
    value = value.replace(/[^0-9]/g, '');
    
    // Parse to number
    const numValue = parseInt(value) || 0;
    
    // Format with thousand separator (dot)
    input.value = numValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    // Store raw value in hidden input
    const row = input.closest('.item-row');
    if (row) {
        const rawInput = row.querySelector('.price-raw');
        if (rawInput) {
            rawInput.value = numValue;
        }
    }
}

// Initialize display formatting on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calculation displays
    document.getElementById('subtotal_display').value = '{{ number_format($pembelian->subtotal, 0, ',', '.') }}';
    document.getElementById('ppn_nominal_display').value = '{{ number_format($pembelian->ppn_nominal, 0, ',', '.') }}';
    
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

// Preview bukti faktur function
function previewBuktiFaktur(input) {
    const preview = document.getElementById('bukti_faktur_preview');
    const img = document.getElementById('bukti_faktur_img');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Check if file is image
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            // For PDF files, show a placeholder or icon
            img.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwYXRoIGQ9Ik0xNCAySDZhMiAyIDAgMCAwLTIgMnYxNmEyIDIgMCAwIDAgMiAyaDhhMiAyIDAgMCAwIDItMnY4TTggMTguSDZ2LTZoMk0xNiAySDZ2LTJoMTB2MTBIMTZ2LTJ6Ii8+PC9zdmc+';
            preview.style.display = 'block';
        }
    } else {
        preview.style.display = 'none';
    }
}

// Update items based on selected vendor
function updateItemsBasedOnVendor(vendorSelect) {
    const selectedOption = vendorSelect.options[vendorSelect.selectedIndex];
    const vendorId = selectedOption.value;
    const vendorKategori = selectedOption.getAttribute('data-kategori');
    
    // Update all item selects
    const itemSelects = document.querySelectorAll('.item-select');
    itemSelects.forEach(select => {
        select.innerHTML = '<option value="">-- Pilih Item --</option>';
        
        if (vendorId && vendorKategori) {
            // Get items based on vendor category
            const items = subSatuanData[vendorKategori === 'Bahan Baku' ? 'bahan_baku' : 'bahan_pendukung'] || {};
            
            Object.keys(items).forEach(itemId => {
                const item = items[itemId];
                if (item.vendor_id == vendorId) {
                    const option = document.createElement('option');
                    option.value = itemId;
                    option.setAttribute('data-tipe', vendorKategori === 'Bahan Baku' ? 'bahan_baku' : 'bahan_pendukung');
                    option.setAttribute('data-nama', item.nama_bahan || item.nama);
                    option.setAttribute('data-satuan', item.satuan?.nama || item.satuanRelation?.nama || '');
                    option.textContent = item.nama_bahan || item.nama;
                    select.appendChild(option);
                }
            });
        }
        
        select.disabled = !vendorId;
    });
}

// Update item info when item is selected
function updateItemInfo(itemSelect) {
    const row = itemSelect.closest('.item-row');
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    const tipeItem = selectedOption.getAttribute('data-tipe');
    const itemName = selectedOption.getAttribute('data-nama');
    const satuan = selectedOption.getAttribute('data-satuan');
    
    // Update tipe item input
    const tipeItemInput = row.querySelector('.tipe-item-input');
    if (tipeItemInput) {
        tipeItemInput.value = tipeItem;
    }
    
    // Update satuan utama display
    const satuanUtamaInput = row.querySelector('input[name="satuan_utama[]"]');
    if (satuanUtamaInput) {
        satuanUtamaInput.value = satuan;
    }
}

// Add new item row
function addItemRow() {
    const container = document.getElementById('itemRows');
    const newRow = document.createElement('div');
    newRow.className = 'item-row';
    newRow.setAttribute('data-row', rowCount);
    
    // Clone the first row structure
    const firstRow = container.querySelector('.item-row');
    if (firstRow) {
        newRow.innerHTML = firstRow.innerHTML;
        
        // Clear values
        newRow.querySelectorAll('input, select').forEach(input => {
            if (input.type === 'text' || input.type === 'number') {
                input.value = '';
            } else if (input.type === 'hidden') {
                input.value = input.name.includes('harga_satuan_raw') ? '0' : '1';
            } else {
                input.selectedIndex = 0;
            }
        });
        
        // Update names with new index
        newRow.querySelectorAll('[name]').forEach(input => {
            const name = input.getAttribute('name');
            if (name.includes('[]')) {
                const newName = name.replace('[]', '[' + rowCount + ']');
                input.setAttribute('name', newName);
            }
        });
        
        // Show delete button for all rows except first
        const deleteBtn = newRow.querySelector('button[onclick="removeItemRow(this)"]');
        if (deleteBtn) {
            deleteBtn.style.display = 'block';
        }
        
        container.appendChild(newRow);
        rowCount++;
    }
}

// Remove item row
function removeItemRow(button) {
    const row = button.closest('.item-row');
    row.remove();
    calculateTotal();
}

// Calculate row total
function calculateRowTotal(element) {
    const row = element.closest('.item-row');
    const jumlah = parseFloat(row.querySelector('input[name^="jumlah"]').value) || 0;
    const hargaSatuan = parseFloat(row.querySelector('input[name^="harga_satuan_raw"]').value) || 0;
    const subtotal = jumlah * hargaSatuan;
    
    // Update subtotal display
    const subtotalDisplay = row.querySelector('.subtotal-display');
    const subtotalValue = row.querySelector('.subtotal-value');
    if (subtotalDisplay) {
        subtotalDisplay.value = formatNumber(subtotal);
    }
    if (subtotalValue) {
        subtotalValue.value = subtotal;
    }
    
    calculateTotal();
}

// Calculate total
function calculateTotal() {
    const subtotalInputs = document.querySelectorAll('.subtotal-value');
    let subtotal = 0;
    
    subtotalInputs.forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const biayaKirim = parseFloat(document.getElementById('biaya_kirim').value) || 0;
    const ppnPersen = parseFloat(document.getElementById('ppn_persen').value) || 0;
    
    // Calculate PPN
    const basePPN = subtotal + biayaKirim;
    const ppnNominal = basePPN * (ppnPersen / 100);
    
    // Calculate total
    const total = subtotal + biayaKirim + ppnNominal;
    
    // Update displays
    document.getElementById('subtotal').value = subtotal;
    document.getElementById('subtotal_display').value = formatNumber(subtotal);
    document.getElementById('ppn_nominal').value = ppnNominal;
    document.getElementById('ppn_nominal_display').value = formatNumber(ppnNominal);
    document.getElementById('total_harga').textContent = formatCurrency(total);
    document.getElementById('total_harga_input').value = total;
}

// Debug form data
function debugFormData(form) {
    console.log('Form Data:', new FormData(form));
    return true;
}

// Initialize vendor items on page load
document.addEventListener('DOMContentLoaded', function() {
    const vendorSelect = document.querySelector('select[name="vendor_id"]');
    if (vendorSelect && vendorSelect.value) {
        updateItemsBasedOnVendor(vendorSelect);
    }
});
</script>
@endsection
