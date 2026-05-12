@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Retur</h1>

    <form action="{{ route('transaksi.retur.update', $retur->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="tanggal">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $retur->tanggal) }}" required>
        </div>

        <div class="mb-3">
            <label for="produk_id">Produk</label>
            <select name="produk_id" class="form-select" required>
                @foreach($produks as $produk)
                    <option value="{{ $produk->id }}" {{ $produk->id == old('produk_id', $retur->produk_id) ? 'selected' : '' }}>
                        {{ $produk->nama_produk }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="pembelian_id">Pembelian</label>
            <select name="pembelian_id" class="form-select" required>
                @foreach($pembelians as $pembelian)
                    <option value="{{ $pembelian->id }}" {{ $pembelian->id == old('pembelian_id', $retur->pembelian_id) ? 'selected' : '' }}>
                        {{ $pembelian->id }} - {{ $pembelian->tanggal }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah">Jumlah</label>
            <input type="number" step="0.01" name="jumlah" class="form-control" value="{{ old('jumlah', $retur->jumlah) }}" required>
        </div>

        <div class="mb-3">
            <label for="keterangan">Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ old('keterangan', $retur->keterangan) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Perbarui</button>
    </form>
</div>
@endsection
