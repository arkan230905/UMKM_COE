@extends('layouts.app')

@section('title', 'Edit BOM')

@push('styles')
<style>
.bom-form {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.material-row {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
    margin-bottom: 1rem;
    background: white;
    border-radius: 4px;
}

.material-row:hover {
    background-color: #f8f9fa;
}

.action-buttons {
    margin-top: 1rem;
}

.action-buttons .btn-sm {
    margin-right: 0.5rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.action-buttons .btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.btn-save {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.btn-save:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    color: white;
}

.btn-cancel {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    color: white;
}

.btn-cancel:hover {
    background: linear-gradient(135deg, #e082ea 0%, #e4465b 100%);
    color: white;
}

.btn-add {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    border: none;
    color: white;
}

.btn-add:hover {
    background: linear-gradient(135deg, #33d86b 0%, #28e7c7 100%);
    color: white;
}

.total-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
}

.total-section h3 {
    margin: 0;
    font-size: 1.5rem;
}

.total-section .total-amount {
    font-size: 2rem;
    font-weight: bold;
}

.remove-btn {
    display: none;
}

.remove-btn.show {
    display: block;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-edit me-2"></i>Edit BOM
        </h2>
        <div class="btn-group">
            <a href="{{ route('bom.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <form id="editBomForm" method="POST" action="{{ route('bom.update', $bom->id) }}">
        @csrf
        <input type="hidden" name="id" value="{{ $bom->id }}">
        
        <div class="bom-form">
            <div class="row mb-4">
                <div class="col-md-12">
                    <label class="form-label">Pilih Produk</label>
                    <select name="produk_id" class="form-select" onchange="updateProductInfo(this)">
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($produks as $produk)
                            <option value="{{ $produk->id }}" 
                                    data-harga="{{ $produk->harga_jual ?? 0 }}"
                                    data-satuan="{{ $produk->satuan->nama ?? 'pcs' }}"
                                    {{ $bom->produk_id == $produk->id ? 'selected' : '' }}>
                                {{ $produk->nama_produk }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah[]" class="form-control" min="0.01" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Satuan</label>
                    <select name="satuan_pembelian[]" class="form-select">
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
                    <input type="number" name="harga_satuan_pembelian[]" class="form-control" min="0" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total Harga</label>
                    <input type="number" name="total_harga" class="form-control" readonly>
                </div>
            </div>

            <div class="row g-3" id="bahanBakuRows">
                <!-- Load existing BOM details -->
                @foreach ($bom->details as $detail)
                    @if ($detail->bahan_baku)
                        <div class="col-md-12">
                            <div class="material-row">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <select name="bahan_baku_id[]" class="form-select">
                                            <option value="{{ $detail->bahan_baku->id }}" selected>{{ $detail->bahan_baku->nama_bahan }}</option>
                                            @foreach ($bahanBakus as $bb)
                                                @if ($bb->id != $detail->bahan_baku->id)
                                                    <option value="{{ $bb->id }}">{{ $bb->nama_bahan }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="jumlah[]" class="form-control" min="0.01" step="0.01" value="{{ $detail->jumlah }}">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="satuan_pembelian[]" class="form-select">
                                            <option value="{{ $detail->satuan_pembelian }}" selected>{{ $detail->satuan_pembelian }}</option>
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
                                        <input type="number" name="harga_satuan_pembelian[]" class="form-control" min="0" step="0.01" value="{{ $detail->harga_satuan_pembelian }}">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="total_harga" class="form-control" readonly value="{{ $detail->subtotal }}">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeMaterialRow(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if ($detail->bahan_pendukung)
                        <div class="col-md-12">
                            <div class="material-row">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <select name="bahan_pendukung_id[]" class="form-select">
                                            <option value="{{ $detail->bahan_pendukung->id }}" selected>{{ $detail->bahan_pendukung->nama_bahan }}</option>
                                            @foreach ($bahanPendukungs as $bp)
                                                @if ($bp->id != $detail->bahan_pendukung->id)
                                                    <option value="{{ $bp->id }}">{{ $bp->nama_bahan }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="jumlah_pendukung[]" class="form-control" min="0.01" step="0.01" value="{{ $detail->jumlah }}">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="satuan_pembelian_pendukung[]" class="form-select">
                                            <option value="{{ $detail->satuan_pembelian }}" selected>{{ $detail->satuan_pembelian }}</option>
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
                                        <input type="number" name="harga_satuan_pembelian_pendukung[]" class="form-control" min="0" step="0.01" value="{{ $detail->harga_satuan_pembelian }}">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="total_harga_pendukung" class="form-control" readonly value="{{ $detail->subtotal }}">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeMaterialRow(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="row g-3" id="newBahanBakuRows">
                <!-- New Bahan Baku rows will be added here -->
            </div>

            <div class="row g-3" id="newBahanPendukungRows">
                <!-- New Bahan Pendukung rows will be added here -->
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-icon btn-add" onclick="addNewBahanBakuRow()">
                        <i class="fas fa-plus me-1"></i>Tambah Bahan Baku
                    </button>
                    <button type="button" class="btn btn-icon btn-add" onclick="addNewBahanPendukungRow()">
                        <i class="fas fa-plus me-1"></i>Tambah Bahan Pendukung
                    </button>
                </div>
            </div>

            <div class="total-section">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Total BOM</h3>
                        <div class="total-amount">Rp <span id="totalBom">{{ number_format($bom->total_biaya, 0, ',', '.') }}</span></div>
                    </div>
                    <div class="col-md-6">
                        <h3>Total Item</h3>
                        <div class="total-amount"><span id="totalItems">{{ $bom->details->count() }}</span></div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-icon btn-save">
                    <i class="fas fa-save me-1"></i>Update BOM
                </button>
                <a href="{{ route('bom.index') }}" class="btn btn-icon btn-cancel">
                    <i class="fas fa-times me-1"></i>Batal
                </a>
            </div>
        </div>
    </form>
</div>

<script>
let materialRows = 0;
let totalBom = {{ $bom->total_biaya }};
let totalItems = {{ $bom->details->count() }};

function addNewBahanBakuRow() {
    materialRows++;
    const container = document.getElementById('newBahanBakuRows');
    const newRow = document.createElement('div');
    newRow.className = 'col-md-12';
    newRow.innerHTML = `
        <div class="material-row">
            <div class="row g-3">
                <div class="col-md-6">
                    <select name="bahan_baku_id[]" class="form-select">
                        <option value="">-- Pilih Bahan Baku --</option>
                        @foreach ($bahanBakus as $bb)
                            <option value="{{ $bb->id }}">{{ $bb->nama_bahan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="jumlah[]" class="form-control" min="0.01" step="0.01" placeholder="Jumlah">
                </div>
                <div class="col-md-2">
                    <select name="satuan_pembelian[]" class="form-select">
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
                    <input type="number" name="harga_satuan_pembelian[]" class="form-control" min="0" step="0.01" placeholder="Harga/Satuan">
                </div>
                <div class="col-md-2">
                    <input type="number" name="total_harga" class="form-control" readonly placeholder="Total Harga">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeMaterialRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    updateRemoveButtons();
    updateTotal();
}

function addNewBahanPendukungRow() {
    materialRows++;
    const container = document.getElementById('newBahanPendukungRows');
    const newRow = document.createElement('div');
    newRow.className = 'col-md-12';
    newRow.innerHTML = `
        <div class="material-row">
            <div class="row g-3">
                <div class="col-md-6">
                    <select name="bahan_pendukung_id[]" class="form-select">
                        <option value="">-- Pilih Bahan Pendukung --</option>
                        @foreach ($bahanPendukungs as $bp)
                            <option value="{{ $bp->id }}">{{ $bp->nama_bahan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="jumlah_pendukung[]" class="form-control" min="0.01" step="0.01" placeholder="Jumlah">
                </div>
                <div class="col-md-2">
                    <select name="satuan_pembelian_pendukung[]" class="form-select">
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
                    <input type="number" name="harga_satuan_pembelian_pendukung[]" class="form-control" min="0" step="0.01" placeholder="Harga/Satuan">
                </div>
                <div class="col-md-2">
                    <input type="number" name="total_harga_pendukung" class="form-control" readonly placeholder="Total Harga">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeMaterialRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    updateRemoveButtons();
    updateTotal();
}

function removeMaterialRow(button) {
    const row = button.closest('.material-row');
    row.remove();
    materialRows--;
    updateRemoveButtons();
    updateTotal();
}

function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-btn');
    removeButtons.forEach(btn => {
        btn.classList.toggle('show', removeButtons.length > 1);
    });
}

function updateTotal() {
    totalBom = 0;
    totalItems = 0;
    
    // Calculate total from existing rows
    const existingRows = document.querySelectorAll('#bahanBakuRows .material-row, #newBahanBakuRows .material-row');
    existingRows.forEach(row => {
        const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]')?.value || 0);
        const harga = parseFloat(row.querySelector('input[name="harga_satuan_pembelian[]"]')?.value || 0);
        totalBom += jumlah * harga;
        totalItems++;
    });
    
    // Calculate total from existing rows
    const existingPendukungRows = document.querySelectorAll('#bahanPendukungRows .material-row, #newBahanPendukungRows .material-row');
    existingPendukungRows.forEach(row => {
        const jumlah = parseFloat(row.querySelector('input[name="jumlah_pendukung[]"]')?.value || 0);
        const harga = parseFloat(row.querySelector('input[name="harga_satuan_pembelian_pendukung[]"]')?.value || 0);
        totalBom += jumlah * harga;
        totalItems++;
    });
    
    // Update display
    document.getElementById('totalBom').textContent = formatNumber(totalBom);
    document.getElementById('totalItems').textContent = totalItems;
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function updateProductInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption.value) {
        const hargaJual = parseFloat(selectedOption.getAttribute('data-harga') || 0);
        const satuan = selectedOption.getAttribute('data-satuan') || 'pcs';
        
        // Update all harga inputs with product's selling price
        const hargaInputs = document.querySelectorAll('input[name="harga_satuan_pembelian[]"]');
        hargaInputs.forEach(input => {
            input.value = hargaJual;
        });
        
        // Update all satuan selects with product's unit
        const satuanSelects = document.querySelectorAll('select[name="satuan_pembelian[]"]');
        satuanSelects.forEach(select => {
            // Find the option that matches the product's unit
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === satuan) {
                    select.selectedIndex = i;
                    break;
                }
            }
        });
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="jumlah[]"], input[name="harga_satuan_pembelian[]"], input[name="jumlah_pendukung[]"], input[name="harga_satuan_pembelian_pendukung[]"]')) {
            updateTotal();
        }
    });
});
</script>
@endsection
