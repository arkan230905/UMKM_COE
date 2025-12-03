@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">🔄 Tambah Retur</h4>
            <small>Buat transaksi retur barang</small>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('transaksi.retur.store') }}" method="POST" id="formRetur">
                @csrf

                <!-- Form Header -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📅 Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-lg" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📦 Tipe Retur</label>
                        <select name="type" id="typeRetur" class="form-select form-select-lg" required onchange="toggleReturType()">
                            <option value="sale">Retur Penjualan</option>
                            <option value="purchase">Retur Pembelian</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">📝 Kompensasi</label>
                        <select name="kompensasi" class="form-select form-select-lg" required>
                            <option value="credit">Kredit/Nota</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                </div>

                <!-- Detail Retur Section -->
                <div class="card bg-primary text-white mb-3">
                    <div class="card-body py-2">
                        <h5 class="mb-0" id="headerDetail">📋 Detail Retur - PRODUK</h5>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th width="50%" id="headerColumn">PRODUK</th>
                                <th width="20%">QTY</th>
                                <th width="25%">HARGA ASAL (OPTIONAL)</th>
                                <th width="5%">
                                    <button type="button" class="btn btn-sm btn-success" onclick="addRowRetur()">➕</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRetur">
                            <!-- Rows will be added here -->
                        </tbody>
                    </table>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('transaksi.retur.index') }}" class="btn btn-secondary btn-lg">
                        ✖️ Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Simpan Retur
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
            <input type="number" step="0.01" min="0.01" name="details[${rowIndex}][qty]" class="form-control" placeholder="0" required>
        </td>
        <td>
            <input type="number" step="0.01" name="details[${rowIndex}][harga_satuan_asal]" class="form-control" placeholder="Optional">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRowRetur(this)">🗑️</button>
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
@endsection
