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
            <div class="col-md-4">
                <label for="tanggal" class="form-label">Tanggal Pembelian</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Metode Pembayaran</label>
                <select name="payment_method" id="payment_method" class="form-select" required>
                    <option value="cash" {{ old('payment_method','cash')==='cash' ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ old('payment_method')==='transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="credit" {{ old('payment_method')==='credit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
            <div class="col-md-4" id="sumber_dana_wrapper">
                <label class="form-label">Sumber Dana</label>
                <select name="sumber_dana" id="sumber_dana" class="form-select">
                    @foreach($kasbank as $kb)
                        <option value="{{ $kb->kode_akun }}" {{ old('sumber_dana', '1101') == $kb->kode_akun ? 'selected' : '' }}>
                            {{ $kb->nama_akun }} ({{ $kb->kode_akun }})
                        </option>
                    @endforeach
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
                            @foreach(($satuans ?? []) as $sat)
                                <option value="{{ $sat->kode }}">{{ $sat->kode }} ({{ $sat->nama }})</option>
                            @endforeach
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
    // Show/hide sumber dana based on payment method
    function toggleSumberDana() {
        const paymentMethod = document.getElementById('payment_method').value;
        const sumberDanaWrapper = document.getElementById('sumber_dana_wrapper');
        const sumberDana = document.getElementById('sumber_dana');
        
        if (paymentMethod === 'cash' || paymentMethod === 'transfer') {
            sumberDanaWrapper.style.display = 'block';
            sumberDana.required = true;
            
            // Update options based on payment method
            if (paymentMethod === 'cash') {
                sumberDana.innerHTML = `
                    <option value="1101">Kas Kecil (1101)</option>
                    <option value="101">Kas (101)</option>
                `;
            } else if (paymentMethod === 'transfer') {
                sumberDana.innerHTML = `
                    <option value="1102">Kas di Bank (1102)</option>
                    <option value="102">Bank (102)</option>
                `;
            }
        } else {
            sumberDanaWrapper.style.display = 'none';
            sumberDana.required = false;
        }
    }
    
    // Initial toggle
    toggleSumberDana();
    
    // Listen to payment method changes
    document.getElementById('payment_method').addEventListener('change', toggleSumberDana);
    
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
