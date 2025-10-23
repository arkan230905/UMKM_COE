@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Tambah Pegawai</h3>

    <form action="{{ route('master-data.pegawai.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="no_telp" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Alamat</label>
                <input type="text" name="alamat" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Jabatan</label>
                <input type="text" name="jabatan" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Gaji</label>
                <input type="number" name="gaji" class="form-control" required>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Simpan</button>
            <a href="{{ route('master-data.pegawai.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
