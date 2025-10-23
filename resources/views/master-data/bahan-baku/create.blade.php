@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Tambah Bahan Baku</h4>
    <form action="{{ route('master-data.bahan-baku.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nama Bahan</label>
            <input type="text" name="nama_bahan" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label>Satuan</label>
            <select name="satuan" class="form-control" required>
                @foreach($satuanOptions as $satuan)
                    <option value="{{ $satuan }}">{{ $satuan }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Harga Satuan</label>
            <input type="number" name="harga_satuan" class="form-control" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('master-data.bahan-baku.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
