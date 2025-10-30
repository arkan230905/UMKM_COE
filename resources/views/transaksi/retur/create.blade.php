@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Retur</h1>

    <form action="{{ route('transaksi.retur.store') }}" method="POST">
        @csrf

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipe Retur</label>
                <select name="type" class="form-select" required>
                    <option value="sale">Retur Penjualan</option>
                    <option value="purchase">Retur Pembelian</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kompensasi</label>
                <select name="kompensasi" class="form-select" required>
                    <option value="credit">Kredit/Nota</option>
                    <option value="refund">Refund</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ref. Dokumen (ID)</label>
                <input type="number" name="ref_id" class="form-control" placeholder="ID Invoice/PO" value="{{ old('ref_id') }}" required>
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label class="form-label">Alasan</label>
            <textarea name="alasan" class="form-control" rows="2">{{ old('alasan') }}</textarea>
        </div>

        <div class="card mt-3">
            <div class="card-header">Detail Retur</div>
            <div class="card-body p-2">
                <table class="table align-middle" id="detailTable">
                    <thead>
                        <tr>
                            <th style="width:55%">Produk</th>
                            <th style="width:20%">Qty</th>
                            <th style="width:20%">Harga Asal (opsional)</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="details[0][produk_id]" class="form-select" required>
                                    @foreach($produks as $produk)
                                        <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.0001" min="0.0001" name="details[0][qty]" class="form-control" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="details[0][harga_satuan_asal]" class="form-control" placeholder="opsional">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">-</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">+ Tambah Baris</button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Simpan</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
let idx = 1;
function addRow(){
  const tbody = document.querySelector('#detailTable tbody');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select name="details[${idx}][produk_id]" class="form-select" required>
        @foreach($produks as $produk)
          <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
        @endforeach
      </select>
    </td>
    <td><input type="number" step="0.0001" min="0.0001" name="details[${idx}][qty]" class="form-control" required></td>
    <td><input type="number" step="0.01" name="details[${idx}][harga_satuan_asal]" class="form-control" placeholder="opsional"></td>
    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">-</button></td>
  `;
  tbody.appendChild(tr);
  idx++;
}
function removeRow(btn){
  const tr = btn.closest('tr');
  const tbody = tr.parentNode;
  if (tbody.children.length > 1) tbody.removeChild(tr);
}
</script>
@endpush
