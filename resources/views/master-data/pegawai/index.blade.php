@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Daftar Pegawai</h2>

    <a href="{{ route('master-data.pegawai.create') }}" class="btn btn-primary mb-3">+ Tambah Pegawai</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. Telp</th>
                <th>Alamat</th>
                <th>Jenis Kelamin</th>
                <th>Jabatan</th>
                <th>Gaji</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pegawais as $pegawai)
            <tr>
                <td>{{ $pegawai->id }}</td>
                <td>{{ $pegawai->nama }}</td>
                <td>{{ $pegawai->email }}</td>
                <td>{{ $pegawai->no_telp }}</td>
                <td>{{ $pegawai->alamat }}</td>
                <td>{{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                <td>{{ $pegawai->jabatan }}</td>
                <td>Rp {{ number_format($pegawai->gaji, 0, ',', '.') }}</td>
                <td>
                    <a href="{{ route('master-data.pegawai.edit', $pegawai->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('master-data.pegawai.destroy', $pegawai->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
