@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Daftar Pegawai</h2>

    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('master-data.pegawai.create') }}" class="btn btn-primary">+ Tambah Pegawai</a>

        <!-- Dropdown filter kategori -->
        <form method="GET" action="{{ route('master-data.pegawai.index') }}">
            <div class="input-group">
                <select name="kategori" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    <option value="BTKL" {{ $kategori == 'BTKL' ? 'selected' : '' }}>BTKL</option>
                    <option value="BTKTL" {{ $kategori == 'BTKTL' ? 'selected' : '' }}>BTKTL</option>
                </select>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. Telp</th>
                <th>Alamat</th>
                <th>Jenis Kelamin</th>
                <th>Jabatan</th>
                <th>Kategori</th>
                <th>Gaji</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pegawais as $pegawai)
            <tr>
                <td>{{ $pegawai->id }}</td>
                <td>{{ $pegawai->nama }}</td>
                <td>{{ $pegawai->email }}</td>
                <td>{{ $pegawai->no_telp }}</td>
                <td>{{ $pegawai->alamat }}</td>
                <td>{{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                <td>{{ $pegawai->jabatan }}</td>
                <td>{{ $pegawai->kategori_tenaga_kerja }}</td>
                <td>Rp {{ number_format($pegawai->gaji, 0, ',', '.') }}</td>
                <td>
                    <a href="{{ route('master-data.pegawai.edit', $pegawai->id) }}" class="btn btn-warning btn-sm">Ubah</a>
                    <form action="{{ route('master-data.pegawai.destroy', $pegawai->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data pegawai</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
