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
                                    @if(str_contains(strtolower($kb->nama_akun), 'kas'))
                                        💵 Kas {{ $kb->nama_akun }}
                                    @else
                                        🏦 {{ $kb->nama_akun }}
                                    @endif
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
                            <input type="number" name="subtotal[]" class="form-control" placeholder="0" readonly style="background-color: #f8f9fa;">
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
                            <label class="form-label small">Faktor Konversi (Manual)</label>
                            <input type="number" name="faktor_konversi[]" class="form-control form-control-sm" placeholder="1" step="0.0001" value="1" onchange="calculateRowTotal(this)">
                            <small class="text-muted">1 satuan pembelian = berapa satuan utama</small>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Jumlah dalam Satuan Utama</label>
                            <div class="form-control form-control-sm bg-light" style="min-height: 31px; display: flex; align-items: center;">
                                <span class="conversion-result">0 Kg (dari 0 Unit)</span>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label small">Harga per Satuan Utama</label>
                            <div class="form-control form-control-sm bg-light" style="min-height: 31px; display: flex; align-items: center;">
                                <span class="price-per-unit">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculation Section -->
        <div class="form-section">
            <div class="section-header">
                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Perhitungan Biaya</h6>
            </div>
            
            <div class="calculation-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Subtotal</label>
                        <input type="number" name="subtotal_display" id="subtotal" class="form-control" readonly>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Biaya Kirim</label>
                        <input type="number" name="biaya_kirim" id="biaya_kirim" class="form-control" placeholder="0" min="0" onchange="calculateTotal()">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">PPN (%)</label>
                        <input type="number" name="ppn_persen" id="ppn_persen" class="form-control" placeholder="0" min="0" max="100" step="0.01" onchange="calculateTotal()">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">PPN Nominal</label>
                        <input type="number" name="ppn_nominal" id="ppn_nominal" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Section -->
        <div class="form-section">
            <div class="total-section">
                <h4 class="mb-3">Total Harga</h4>
                <h2 class="text-primary mb-0" id="total_harga">Rp 0</h2>
                <input type="hidden" name="total_harga" id="total_harga_input" value="0">
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

// Format number with thousand separators
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
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
        if (input.name === 'faktor_konversi[]') {
            input.value = '1';
        }
        if (input.name === 'subtotal[]') {
            input.value = '0';
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
            calculateRowTotal(this);
        };
    });
}

// Remove the old updateItemOptions function since we don't need it anymore

// Calculate row total
function calculateRowTotal(input) {
    const row = input.closest('.item-row');
    const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]').value) || 0;
    const harga = parseFloat(row.querySelector('input[name="harga_satuan[]"]').value) || 0;
    
    // Calculate subtotal: jumlah × harga per satuan
    const subtotal = jumlah * harga;
    row.querySelector('input[name="subtotal[]"]').value = subtotal.toFixed(2);
    
    // Update conversion displays
    const faktor = parseFloat(row.querySelector('input[name="faktor_konversi[]"]').value) || 1;
    const konversiHasil = jumlah * faktor;
    const hargaPerUnit = faktor > 0 ? harga / faktor : 0;
    
    // Get selected satuan pembelian name for display
    const satuanSelect = row.querySelector('select[name="satuan_pembelian[]"]');
    const selectedSatuan = satuanSelect.options[satuanSelect.selectedIndex];
    const satuanPembelianName = selectedSatuan ? selectedSatuan.getAttribute('data-nama') : 'Unit';
    
    // Get satuan utama name
    const satuanUtama = row.querySelector('input[name="satuan_utama[]"]').value || 'Unit';
    
    row.querySelector('.conversion-result').textContent = formatNumber(konversiHasil.toFixed(4)) + ' ' + satuanUtama + ' (dari ' + formatNumber(jumlah) + ' ' + satuanPembelianName + ')';
    row.querySelector('.price-per-unit').textContent = 'Rp ' + formatNumber(Math.round(hargaPerUnit));
    
    calculateTotal();
}
// Calculate total
function calculateTotal() {
    let subtotal = 0;
    
    // Sum all row subtotals
    document.querySelectorAll('input[name="subtotal[]"]').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const biayaKirim = parseFloat(document.getElementById('biaya_kirim').value) || 0;
    const ppnPersen = parseFloat(document.getElementById('ppn_persen').value) || 0;
    
    const ppnNominal = (subtotal + biayaKirim) * (ppnPersen / 100);
    const totalHarga = subtotal + biayaKirim + ppnNominal;
    
    // Update displays
    document.getElementById('subtotal').value = subtotal;
    document.getElementById('ppn_nominal').value = ppnNominal;
    document.getElementById('total_harga').textContent = 'Rp ' + formatNumber(Math.round(totalHarga));
    document.getElementById('total_harga_input').value = totalHarga;
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
    
    console.log('Arrays:');
    console.log('item_id[]:', itemIds);
    console.log('tipe_item[]:', tipeItems);
    console.log('jumlah[]:', jumlahs);
    console.log('satuan_pembelian[]:', satuanPembelians);
    console.log('harga_satuan[]:', hargaSatuans);
    console.log('subtotal[]:', subtotals);
    console.log('faktor_konversi[]:', faktorKonversis);
    
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
});
</script>

@endsection