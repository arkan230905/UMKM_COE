@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2>Tambah Aset Baru</h2>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Terjadi kesalahan!</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('master-data.aset.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Aset</label>
                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Tanah" {{ old('kategori') == 'Tanah' ? 'selected' : '' }}>Tanah</option>
                        <option value="Bangunan" {{ old('kategori') == 'Bangunan' ? 'selected' : '' }}>Bangunan</option>
                        <option value="Furniture" {{ old('kategori') == 'Furniture' ? 'selected' : '' }}>Furniture</option>
                        <option value="Peralatan Dapur" {{ old('kategori') == 'Peralatan Dapur' ? 'selected' : '' }}>Peralatan Dapur</option>
                        <option value="Kas" {{ old('kategori') == 'Kas' ? 'selected' : '' }}>Kas</option>
                        <option value="Piutang Usaha" {{ old('kategori') == 'Piutang Usaha' ? 'selected' : '' }}>Piutang Usaha</option>
                        <option value="Persediaan Barang Dagang" {{ old('kategori') == 'Persediaan Barang Dagang' ? 'selected' : '' }}>Persediaan Barang Dagang</option>
                        <option value="Perlengkapan Habis Pakai" {{ old('kategori') == 'Perlengkapan Habis Pakai' ? 'selected' : '' }}>Perlengkapan Habis Pakai</option>
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jenis_aset" class="form-label">Jenis Aset</label>
                    <select class="form-select @error('jenis_aset') is-invalid @enderror" id="jenis_aset" name="jenis_aset" required>
                        <option value="">-- Pilih Jenis Aset --</option>
                        <option value="Aset Tetap" {{ old('jenis_aset') == 'Aset Tetap' ? 'selected' : '' }}>Aset Tetap</option>
                        <option value="Aset Tidak Tetap" {{ old('jenis_aset') == 'Aset Tidak Tetap' ? 'selected' : '' }}>Aset Tidak Tetap</option>
                    </select>
                    @error('jenis_aset')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="harga" class="form-label">Harga</label>
                    <input type="number" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga" value="{{ old('harga') }}" required>
                    @error('harga')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tanggal_beli" class="form-label">Tanggal Pembelian</label>
                    <input type="date" class="form-control @error('tanggal_beli') is-invalid @enderror" id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli') }}" required>
                    @error('tanggal_beli')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('master-data.aset.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
