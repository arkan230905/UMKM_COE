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
                <input type="text" name="nama" id="nama" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label for="no_telp" class="form-label">No. Telepon</label>
                <input type="text" name="no_telp" id="no_telp" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-control" rows="2"></textarea>
            </div>

            <div class="col-md-6 mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required>
                    <option value="">-- Pilih --</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="jabatan" class="form-label">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
                <label for="jenis_pegawai" class="form-label">Jenis Pegawai</label>
                <select name="jenis_pegawai" id="jenis_pegawai" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="BTKL">BTKL (Buruh Tenaga Kerja Langsung)</option>
                    <option value="BTKP">BTKP (Buruh Tenaga Kerja Tidak Langsung)</option>
                </select>
            </div>

            <!-- 🟢 Tambahan: Gaji Pokok -->
            <div class="col-md-6 mb-3">
                <label for="gaji_pokok" class="form-label">Gaji Pokok (Rp)</label>
                <input type="number" name="gaji_pokok" id="gaji_pokok" class="form-control" value="0" min="0">
            </div>

            <!-- 🟢 Tambahan: Tunjangan -->
            <div class="col-md-6 mb-3">
                <label for="tunjangan" class="form-label">Tunjangan (Rp)</label>
                <input type="number" name="tunjangan" id="tunjangan" class="form-control" value="0" min="0">
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('master-data.pegawai.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
