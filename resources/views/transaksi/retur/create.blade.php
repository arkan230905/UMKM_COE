@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Retur</h1>

    <form action="{{ route('transaksi.retur.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="tanggal">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
        </div>

        <div class="mb-3">
            <label for="produk_id">Produk</label>
            <select name="produk_id" class="form-select" required>
                @foreach($produks as $produk)
                    <option value="{{ $produk->id }}">{{ $produk->nama_produk }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="pembelian_id">Pembelian</label>
            <select name="pembelian_id" class="form-select" required>
                @foreach($pembelians as $pembelian)
                    <option value="{{ $pembelian->id }}">{{ $pembelian->id }} - {{ $pembelian->tanggal }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah">Jumlah</label>
            <input type="number" step="0.01" name="jumlah" class="form-control" value="{{ old('jumlah') }}" required>
        </div>

        <div class="mb-3">
            <label for="keterangan">Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
