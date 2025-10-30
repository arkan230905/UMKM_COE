@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Produk</h1>

    <form action="{{ route('master-data.produk.update', $produk->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="{{ $produk->nama_produk }}" required>
        </div>
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3">{{ $produk->deskripsi }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Presentase Keuntungan (%)</label>
            <input type="number" step="0.01" name="margin_percent" class="form-control" value="{{ old('margin_percent', $produk->margin_percent) }}">
            <small class="text-muted">Harga jual dihitung otomatis dari Harga BOM × (1 + Margin%).</small>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
