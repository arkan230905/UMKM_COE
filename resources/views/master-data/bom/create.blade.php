@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah BOM</h3>

    <form action="{{ route('master-data.bom.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="produk_id" class="form-label">Produk</label>
            <select name="produk_id" id="produk_id" class="form-control" required>
                <option value="">-- Pilih Produk --</option>
                @foreach($produks as $produk)
                    <option value="{{ $produk->id }}" @if(isset($selectedProdukId) && $selectedProdukId==$produk->id) selected @endif>
                        {{ $produk->nama_produk }}
                    </option>
                @endforeach
            </select>
        </div>

        <h5>Bahan Baku</h5>
        <table class="table table-bordered" id="bomTable">
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th>Kuantitas</th>
                    <th>Satuan</th>
                    <th>Harga Utama</th>
                    <th>Harga 1</th>
                    <th>Harga 2</th>
                    <th>Harga 3</th>
                    <th>Kategori</th>
                    <th><button type="button" id="addRow" class="btn btn-success btn-sm">+</button></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="bahan_baku_id[]" class="form-control bahanSelect" required>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($bahanBaku as $bahan)
                                <option value="{{ $bahan->id }}" data-satuan="{{ $bahan->satuan }}" data-harga="{{ $bahan->harga_satuan }}">
                                    {{ $bahan->nama_bahan }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="jumlah[]" class="form-control jumlahInput" value="1" min="0" required></td>
                    <td class="satuanCell"></td>
                    <td class="hargaUtamaCell"></td>
                    <td class="harga1Cell"></td>
                    <td class="harga2Cell"></td>
                    <td class="harga3Cell"></td>
                    <td>
                        <select name="kategori[]" class="form-control" required>
                            <option value="BTKL">BTKL</option>
                            <option value="BOP">BOP</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Simpan BOM</button>
    </form>
</div>

@include('master-data.bom.js')
@endsection
