@extends('layouts.app')

@section('title', 'Tambah Data Pegawai')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">➕ Tambah Data Pegawai</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master-data.pegawai.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama Pegawai</label>
                <input type="text" name="nama" id="nama" class="form-control" value="{{ old('nama') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="no_telp" class="form-label">No. Telepon</label>
                <input type="text" name="no_telp" id="no_telp" class="form-control" value="{{ old('no_telp') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" rows="2" required>{{ old('alamat') }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin')=='L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin')=='P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" class="form-control" value="{{ old('jabatan') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="kategori_tenaga_kerja" class="form-label">Kategori Tenaga Kerja</label>
                <select name="kategori_tenaga_kerja" id="kategori_tenaga_kerja" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="BTKL" {{ old('kategori_tenaga_kerja')=='BTKL' ? 'selected' : '' }}>BTKL (Buruh Tenaga Kerja Langsung)</option>
                    <option value="BTKTL" {{ old('kategori_tenaga_kerja')=='BTKTL' ? 'selected' : '' }}>BTKTL (Buruh Tenaga Kerja Tidak Langsung)</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="{{ old('tanggal_masuk') }}" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="status_aktif" class="form-label">Status Aktif</label>
                <select name="status_aktif" id="status_aktif" class="form-select" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="1" {{ old('status_aktif')==='1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ old('status_aktif')==='0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>

            <!-- Gaji Pokok (untuk BTKTL) -->
            <div class="col-md-6 mb-3">
                <label for="gaji_pokok" class="form-label">Gaji Pokok (Rp)</label>
                <input type="number" name="gaji_pokok" id="gaji_pokok" class="form-control" value="{{ old('gaji_pokok', 0) }}" min="0">
            </div>

            <!-- Tarif per Jam (untuk BTKL) -->
            <div class="col-md-6 mb-3">
                <label for="tarif_per_jam" class="form-label">Tarif per Jam (Rp) (BTKL)</label>
                <input type="number" name="tarif_per_jam" id="tarif_per_jam" class="form-control" value="{{ old('tarif_per_jam', 0) }}" min="0">
            </div>

            <!-- Tunjangan (opsional) -->
            <div class="col-md-6 mb-3">
                <label for="tunjangan" class="form-label">Tunjangan (Rp)</label>
                <input type="number" name="tunjangan" id="tunjangan" class="form-control" value="{{ old('tunjangan', 0) }}" min="0">
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('master-data.pegawai.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
