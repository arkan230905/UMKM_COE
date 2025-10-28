@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah BOM</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master-data.bom.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="produk_id" class="form-label">Produk</label>
            <select name="produk_id" id="produk_id" class="form-control" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($produks as $produk)
                    <option value="{{ $produk->id }}" {{ request('produk_id') == $produk->id ? 'selected' : '' }}>
                        {{ $produk->nama_produk }}
                    </option>
                @endforeach
            </select>
        </div>

        <h5>Bahan Baku</h5>
        <table class="table table-bordered" id="bomTable">
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th>Jumlah</th>
                    <th>Satuan Resep</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="bahan_baku_id[]" class="form-control" required>
                            <option value="">-- Pilih Bahan Baku --</option>
                            @foreach($bahan_bakus as $bahan)
                                <option value="{{ $bahan->id }}">{{ $bahan->nama_bahan }} (Rp {{ number_format($bahan->harga_satuan,0,',','.') }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="jumlah[]" class="form-control" min="0.0001" step="0.0001" value="1" required>
                    </td>
                    <td>
                        <select name="satuan_resep[]" class="form-control">
                            <option value="">(ikuti satuan bahan)</option>
                            <option value="g">gram (g)</option>
                            <option value="kg">kilogram (kg)</option>
                            <option value="mg">miligram (mg)</option>
                            <option value="ml">mililiter (ml)</option>
                            <option value="sdt">sendok teh (sdt)</option>
                            <option value="sdm">sendok makan (sdm)</option>
                            <option value="cup">cup</option>
                            <option value="pcs">pcs</option>
                            <option value="buah">buah</option>
                            <option value="butir">butir</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm removeRow">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary mb-3" id="addRow">Tambah Baris</button>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">BTKL (opsional)</label>
                <input type="number" name="btkl" min="0" step="0.01" class="form-control" placeholder="Masukkan total BTKL untuk produk ini">
            </div>
            <div class="col-md-6">
                <label class="form-label">BOP (opsional)</label>
                <input type="number" name="bop" min="0" step="0.01" class="form-control" placeholder="Masukkan total BOP untuk produk ini">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Simpan BOM & Hitung Harga Jual</button>
            <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<script>
document.getElementById('addRow').addEventListener('click', function() {
    let table = document.getElementById('bomTable').getElementsByTagName('tbody')[0];
    let newRow = table.rows[0].cloneNode(true);
    newRow.querySelectorAll('input').forEach(el => {
        if (el.name === 'jumlah[]') { el.value = '1'; } else { el.value = ''; }
    });
    newRow.querySelectorAll('select').forEach(el => { el.selectedIndex = 0; });
    table.appendChild(newRow);
});

document.querySelector('#bomTable').addEventListener('click', function(e) {
    if(e.target.classList.contains('removeRow')) {
        let row = e.target.closest('tr');
        if(document.querySelectorAll('#bomTable tbody tr').length > 1) {
            row.remove();
        }
    }
});
</script>
@endsection
