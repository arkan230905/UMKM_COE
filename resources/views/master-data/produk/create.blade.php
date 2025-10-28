@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah Produk</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi Kesalahan!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master-data.produk.store') }}" method="POST">
        @csrf
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label for="nama_produk" class="form-label fw-semibold">Nama Produk</label>
                    <input type="text" name="nama_produk" id="nama_produk" class="form-control" value="{{ old('nama_produk') }}" required>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label fw-semibold">Deskripsi Produk (Opsional)</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="harga_jual" class="form-label fw-semibold">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" id="harga_jual" class="form-control" value="" readonly>
                    <small class="text-muted">Harga jual akan dihitung otomatis setelah menambahkan BOM.</small>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </form>
</div>
@endsection
