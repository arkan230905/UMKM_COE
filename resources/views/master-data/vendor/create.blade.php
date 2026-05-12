@extends('layouts.app')

@section('title', 'Tambah Vendor')

@section('content')
<div class="container">
    <h2>Tambah Vendor</h2>

    <form action="{{ route('master-data.vendor.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama_vendor" class="form-label">Nama Vendor <span style="color: red;">*</span></label>
            <input type="text" name="nama_vendor" class="form-control" value="{{ old('nama_vendor') }}" required>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori Vendor <span style="color: red;">*</span></label>
            <select name="kategori" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Bahan Baku" {{ old('kategori') == 'Bahan Baku' ? 'selected' : '' }}>Bahan Baku</option>
                <option value="Bahan Pendukung" {{ old('kategori') == 'Bahan Pendukung' ? 'selected' : '' }}>Bahan Pendukung</option>
                <option value="Aset" {{ old('kategori') == 'Aset' ? 'selected' : '' }}>Aset</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat <span style="color: red;">*</span></label>
            <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}" required>
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telepon <span style="color: red;">*</span></label>
            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp') }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('master-data.vendor.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
