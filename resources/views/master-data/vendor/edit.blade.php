@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Vendor</h2>

    <form action="{{ route('master-data.vendor.update', $vendor->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama_vendor" class="form-label">Nama Vendor</label>
            <input type="text" name="nama_vendor" class="form-control" value="{{ old('nama_vendor', $vendor->nama_vendor) }}" required>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori Vendor</label>
            <select name="kategori" class="form-control" required>
                <option value="Bahan Baku" {{ old('kategori', $vendor->kategori) == 'Bahan Baku' ? 'selected' : '' }}>Bahan Baku</option>
                <option value="Aset" {{ old('kategori', $vendor->kategori) == 'Aset' ? 'selected' : '' }}>Aset</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $vendor->alamat) }}">
        </div>

        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telepon</label>
            <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $vendor->no_telp) }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email) }}">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('master-data.vendor.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
