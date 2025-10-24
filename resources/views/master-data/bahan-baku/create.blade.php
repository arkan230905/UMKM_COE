@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Bahan Baku</h2>

    <form action="{{ route('master-data.bahan-baku.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama_bahan" class="form-label">Nama Bahan</label>
            <input type="text" name="nama_bahan" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" name="satuan" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="harga_satuan" class="form-label">Harga Satuan</label>
            <input type="number" name="harga_satuan" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('master-data.bahan-baku.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
