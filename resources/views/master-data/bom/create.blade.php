@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Tambah Bill of Materials (BOM)</h3>

    <form action="{{ route('master-data.bom.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="produk_id" class="form-label">Produk</label>
            <select name="produk_id" id="produk_id" class="form-select" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($produk as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_produk }}</option>
                @endforeach
            </select>
        </div>

        <table class="table table-bordered" id="bomTable">
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th>Satuan</th>
                    <th>Qty</th>
                    <th><button type="button" id="addRow" class="btn btn-success btn-sm">+</button></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="bahan_baku_id[]" class="form-select bahanSelect" required>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($bahan_baku as $b)
                                <option value="{{ $b->id }}" data-satuan="{{ $b->satuan }}">{{ $b->nama_bahan }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="satuan[]" class="form-control satuanField" readonly></td>
                    <td><input type="number" name="kuantitas[]" class="form-control" step="0.01" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">-</button></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary mt-3">Simpan BOM</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // isi otomatis satuan ketika bahan dipilih
    document.body.addEventListener('change', function(e) {
        if (e.target.classList.contains('bahanSelect')) {
            const satuan = e.target.options[e.target.selectedIndex].dataset.satuan;
            e.target.closest('tr').querySelector('.satuanField').value = satuan || '';
        }
    });

    // tambah baris baru
    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#bomTable tbody');
        const newRow = tbody.rows[0].cloneNode(true);
        newRow.querySelectorAll('input, select').forEach(el => el.value = '');
        tbody.appendChild(newRow);
    });

    // hapus baris
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeRow')) {
            const row = e.target.closest('tr');
            const tbody = row.closest('tbody');
            if (tbody.rows.length > 1) row.remove();
        }
    });
});
</script>
@endsection
