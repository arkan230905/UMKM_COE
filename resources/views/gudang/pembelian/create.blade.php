@extends('layouts.gudang')

@section('title', 'Tambah Pembelian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tambah Pembelian</h1>
        <a href="{{ route('gudang.pembelian') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('gudang.pembelian.store') }}" method="POST" id="formPembelian">
        @csrf
        
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pembelian</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="">-- Pilih Vendor --</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->nama_vendor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Metode Bayar</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="credit">Kredit</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="sumber_dana_wrapper">
                        <label class="form-label">Sumber Dana</label>
                        <select name="sumber_dana" id="sumber_dana" class="form-select">
                            @foreach($kasbank as $kb)
                                <option value="{{ $kb->kode_akun }}">{{ $kb->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-boxes"></i> Detail Item</h5>
                <div>
                    <button type="button" class="btn btn-light btn-sm" onclick="addItem('bahan_baku')">
                        <i class="fas fa-plus"></i> Bahan Baku
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="addItem('bahan_pendukung')">
                        <i class="fas fa-plus"></i> Bahan Pendukung
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Tipe</th>
                            <th>Item</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Harga/Satuan</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="5" class="text-end">TOTAL:</th>
                            <th id="grandTotal">Rp 0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle"></i> Harga akan di-average dengan stok yang ada.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('gudang.pembelian') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>

<script>
const bahanBakuList = @json($bahanBakus);
const bahanPendukungList = @json($bahanPendukungs);
const satuanList = @json($satuans);
</script>
@endsection

@section('scripts')
<script>
let itemIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    toggleSumberDana();
    document.getElementById('payment_method').addEventListener('change', toggleSumberDana);
    addItem('bahan_baku');
});

function toggleSumberDana() {
    const method = document.getElementById('payment_method').value;
    document.getElementById('sumber_dana_wrapper').style.display = method === 'credit' ? 'none' : 'block';
}

function addItem(tipe) {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.id = 'row_' + itemIndex;
    
    let itemOptions = '<option value="">-- Pilih --</option>';
    const list = tipe === 'bahan_baku' ? bahanBakuList : bahanPendukungList;
    list.forEach(b => {
        const sat = b.satuan ? (b.satuan.kode || b.satuan) : 'pcs';
        itemOptions += '<option value="' + b.id + '" data-harga="' + (b.harga_satuan || 0) + '" data-satuan="' + sat + '">' + b.kode_bahan + ' - ' + b.nama_bahan + '</option>';
    });
    
    let satuanOptions = '';
    satuanList.forEach(s => {
        satuanOptions += '<option value="' + s.kode + '">' + s.kode + '</option>';
    });
    
    const badge = tipe === 'bahan_baku' ? '<span class="badge bg-primary">Bahan Baku</span>' : '<span class="badge bg-warning text-dark">Bahan Pendukung</span>';
    
    row.innerHTML = '<td><input type="hidden" name="items[' + itemIndex + '][tipe]" value="' + tipe + '">' + badge + '</td>' +
        '<td><select name="items[' + itemIndex + '][item_id]" class="form-select form-select-sm item-select" required onchange="onItemChange(this,' + itemIndex + ')">' + itemOptions + '</select></td>' +
        '<td><input type="number" name="items[' + itemIndex + '][jumlah]" class="form-control form-control-sm jumlah" min="0.01" step="0.01" value="1" required onchange="calculateRow(' + itemIndex + ')"></td>' +
        '<td><select name="items[' + itemIndex + '][satuan]" class="form-select form-select-sm satuan">' + satuanOptions + '</select></td>' +
        '<td><input type="number" name="items[' + itemIndex + '][harga_satuan]" class="form-control form-control-sm harga" min="0" step="100" value="0" required onchange="calculateRow(' + itemIndex + ')"></td>' +
        '<td class="subtotal text-end fw-bold">Rp 0</td>' +
        '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(' + itemIndex + ')"><i class="fas fa-trash"></i></button></td>';
    
    tbody.appendChild(row);
    itemIndex++;
}

function onItemChange(select, idx) {
    const opt = select.options[select.selectedIndex];
    const row = document.getElementById('row_' + idx);
    row.querySelector('.harga').value = opt.dataset.harga || 0;
    const satSel = row.querySelector('.satuan');
    for (let i = 0; i < satSel.options.length; i++) {
        if (satSel.options[i].value.toLowerCase() === (opt.dataset.satuan || '').toLowerCase()) {
            satSel.selectedIndex = i;
            break;
        }
    }
    calculateRow(idx);
}

function calculateRow(idx) {
    const row = document.getElementById('row_' + idx);
    if (!row) return;
    const jumlah = parseFloat(row.querySelector('.jumlah').value) || 0;
    const harga = parseFloat(row.querySelector('.harga').value) || 0;
    row.querySelector('.subtotal').textContent = 'Rp ' + (jumlah * harga).toLocaleString('id-ID');
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const j = parseFloat(row.querySelector('.jumlah')?.value) || 0;
        const h = parseFloat(row.querySelector('.harga')?.value) || 0;
        total += j * h;
    });
    document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function removeRow(idx) {
    const row = document.getElementById('row_' + idx);
    if (row && document.querySelectorAll('#itemsBody tr').length > 1) {
        row.remove();
        calculateTotal();
    }
}

document.getElementById('formPembelian').addEventListener('submit', function(e) {
    if (document.querySelectorAll('#itemsBody tr').length === 0) {
        e.preventDefault();
        alert('Tambahkan minimal 1 item!');
    }
});
</script>
@endsection
