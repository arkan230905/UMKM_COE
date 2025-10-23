@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Tambah BOM</h4>

    <form method="POST" action="{{ route('master-data.bom.store') }}">
        @csrf
        <div class="mb-3">
            <label for="produk_id" class="form-label">Pilih Produk</label>
            <select name="produk_id" class="form-control" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($produks as $produk)
                    <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                @endforeach
            </select>
        </div>

        <h5>Rincian Bahan Baku</h5>
        <table class="table table-bordered" id="bahan-table">
            <thead>
                <tr>
                    <th>Bahan Baku</th>
                    <th>Jumlah</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="bahan_baku[]" class="form-control" required>
                            <option value="">-- Pilih Bahan Baku --</option>
                            @foreach($bahanBaku as $bahan)
                                <option value="{{ $bahan->id }}">{{ $bahan->nama_bahan }} (Rp {{ number_format($bahan->harga_satuan,0,',','.') }}/{{ $bahan->satuan }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="jumlah[]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger remove-row">Hapus</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-primary" id="add-row">Tambah Bahan</button>
        <button type="submit" class="btn btn-success">Simpan BOM</button>
    </form>
</div>

<script>
document.getElementById('add-row').addEventListener('click', function(){
    let table = document.getElementById('bahan-table').getElementsByTagName('tbody')[0];
    let newRow = table.rows[0].cloneNode(true);
    newRow.querySelectorAll('input').forEach(input => input.value = '');
    table.appendChild(newRow);
});

document.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('remove-row')){
        let row = e.target.closest('tr');
        if(document.querySelectorAll('#bahan-table tbody tr').length > 1){
            row.remove();
        }
    }
});
</script>
@endsection
