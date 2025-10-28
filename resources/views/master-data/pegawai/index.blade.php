@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center">📋 Daftar Pegawai</h2>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <form method="GET" action="{{ route('master-data.pegawai.index') }}" class="d-flex gap-2">
            <select name="jenis" class="form-select" style="width:auto" onchange="this.form.submit()">
                <option value="" {{ empty($jenis) ? 'selected' : '' }}>Semua Kategori</option>
                <option value="btkl" {{ ($jenis ?? '') === 'btkl' ? 'selected' : '' }}>BTKL</option>
                <option value="btktl" {{ ($jenis ?? '') === 'btktl' ? 'selected' : '' }}>BTKTL</option>
            </select>
            <noscript><button type="submit" class="btn btn-secondary">Filter</button></noscript>
        </form>
        <a href="{{ route('master-data.pegawai.create') }}" class="btn btn-primary">➕ Tambah Pegawai</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. Telp</th>
                        <th>Alamat</th>
                        <th>JK</th>
                        <th>Jabatan</th>
                        <th>Kategori</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
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
                        <td>{{ strtoupper($pegawai->jenis_pegawai ?? '-') }}</td>
                        <td>
                            Rp {{ number_format($pegawai->gaji_pokok ?? 0, 0, ',', '.') }}
                            @if(($pegawai->jenis_pegawai ?? '') === 'btkl') /jam @endif
                        </td>
                        <td>Rp {{ number_format($pegawai->tunjangan ?? 0, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('master-data.pegawai.edit', $pegawai->id) }}" class="btn btn-warning btn-sm mb-1">Edit</a>
                            <form action="{{ route('master-data.pegawai.destroy', $pegawai->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Yakin ingin menghapus pegawai ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
