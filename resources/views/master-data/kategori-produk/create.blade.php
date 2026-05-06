@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Tambah Kategori Produk</h1>
            <p class="text-muted mb-0">Tambah kategori baru untuk produk</p>
        </div>
        <a href="{{ route('master-data.kategori-produk.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.kategori-produk.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kode_kategori" class="form-label">Kode Kategori</label>
                            <input type="text" name="kode_kategori" id="kode_kategori" class="form-control" 
                                   value="{{ old('kode_kategori') }}" placeholder="Opsional, misal: KAT-001">
                            @error('kode_kategori')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control" 
                                   value="{{ old('nama') }}" required autofocus>
                            @error('nama')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" 
                              placeholder="Opsional, tambahkan deskripsi kategori">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('master-data.kategori-produk.index') }}" class="btn btn-secondary me-2">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
