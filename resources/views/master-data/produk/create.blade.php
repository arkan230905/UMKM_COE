@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah Produk</h1>

    {{-- Tampilkan pesan error validasi jika ada --}}
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

    {{-- Form Tambah Produk --}}
    <form action="{{ route('master-data.produk.store') }}" method="POST">
        @csrf
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nama_produk" class="form-label fw-semibold">Nama Produk</label>
                        <input 
                            type="text" 
                            name="nama_produk" 
                            id="nama_produk" 
                            class="form-control @error('nama_produk') is-invalid @enderror" 
                            value="{{ old('nama_produk') }}" 
                            required>
                        @error('nama_produk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="harga_jual" class="form-label fw-semibold">Harga Jual (Rp)</label>
                        <input 
                            type="number" 
                            name="harga_jual" 
                            id="harga_jual" 
                            class="form-control @error('harga_jual') is-invalid @enderror" 
                            value="{{ old('harga_jual') }}" 
                            required>
                        @error('harga_jual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Tambahan opsional: kategori atau deskripsi --}}
                <div class="mb-3">
                    <label for="deskripsi" class="form-label fw-semibold">Deskripsi Produk (Opsional)</label>
                    <textarea 
                        name="deskripsi" 
                        id="deskripsi" 
                        rows="3" 
                        class="form-control">{{ old('deskripsi') }}</textarea>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
                <a href="{{ route('master-data.produk.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Kembali
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
