@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@section('content')
<div class="container">
    <h2 class="mb-3">Tambah Pembelian</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transaksi.pembelian.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="vendor_id" class="form-label">Vendor</label>
            <select name="vendor_id" id="vendor_id" class="form-select" required>
                <option value="">-- Pilih Vendor --</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}">{{ $vendor->nama_vendor }}</option>
                @endforeach
            </select>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="tanggal" class="form-label">Tanggal Pembelian</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" class="form-select" required>
                    <option value="cash" {{ old('payment_method','cash')==='cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="credit" {{ old('payment_method')==='credit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
        </div>

        <h5>Detail Pembelian</h5>
        <table class="table table-bordered" id="detailTable">
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Harga per Satuan</th>
                    <th>Total</th>
                    <th>
                        <button type="button" class="btn btn-success btn-sm" id="addRow">+</button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="bahan_baku_id[]" class="form-select bahan-baku" required>
                            <option value="">-- Pilih Bahan Baku --</option>
                            @foreach ($bahanBakus as $bahan)
                                <option value="{{ $bahan->id }}">{{ $bahan->nama_bahan }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="jumlah[]" class="form-control jumlah" min="1" value="1" required></td>
                    <td>
                        <select name="satuan[]" class="form-select satuan">
                            <option value="">(ikuti satuan bahan)</option>
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="mg">mg</option>
                            <option value="ml">ml</option>
                            <option value="sdt">sdt</option>
                            <option value="sdm">sdm</option>
                            <option value="cup">cup</option>
                            <option value="pcs">pcs</option>
                            <option value="buah">buah</option>
                            <option value="butir">butir</option>
                        </select>
                    </td>
                    <td><input type="number" name="harga_satuan[]" class="form-control harga" min="0" value="0" required></td>
                    <td><input type="text" class="form-control subtotal" value="0" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                </tr>
            </tbody>
        </table>

        <div class="alert alert-info py-2">
            Sistem akan mengonversi kuantitas ke satuan dasar bahan untuk perhitungan stok dan biaya (moving average & FIFO). 
            Contoh: 2 kg akan disimpan sebagai 2000 g bila satuan bahan adalah g. Nilai total mengikuti satuan input.
        </div>

        <div class="mb-3">
            <label for="total" class="form-label">Total</label>
            <input type="text" name="total" id="total" class="form-control" value="0" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
        <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateSubtotal(row) {
        const jumlah = parseFloat(row.querySelector('.jumlah').value) || 0;
        const harga = parseFloat(row.querySelector('.harga').value) || 0;
        const subtotal = jumlah * harga;
        row.querySelector('.subtotal').value = subtotal.toLocaleString();
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(input => {
            total += parseFloat(input.value.replace(/,/g, '')) || 0;
        });
        document.getElementById('total').value = total.toLocaleString();
    }

    // Tambah baris baru
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#detailTable tbody');
        const newRow = tbody.rows[0].cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = input.classList.contains('jumlah') ? 1 : 0);
        newRow.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        newRow.querySelector('.subtotal').value = 0;
        tbody.appendChild(newRow);
    });

    // Hapus baris
    document.querySelector('#detailTable').addEventListener('click', function(e) {
        if(e.target && e.target.classList.contains('removeRow')) {
            const rows = document.querySelectorAll('#detailTable tbody tr');
            if(rows.length > 1) e.target.closest('tr').remove();
            updateTotal();
        }
    });

    // Update subtotal saat input berubah
    document.querySelector('#detailTable').addEventListener('input', function(e) {
        if(e.target && (e.target.classList.contains('jumlah') || e.target.classList.contains('harga'))) {
            const row = e.target.closest('tr');
            updateSubtotal(row);
        }
    });

    // Hitung subtotal awal
    document.querySelectorAll('#detailTable tbody tr').forEach(updateSubtotal);
});
</script>
@endsection
