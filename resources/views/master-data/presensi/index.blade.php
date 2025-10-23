@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Data Presensi</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('master-data.presensi.create') }}" class="btn btn-primary mb-3">Tambah Presensi</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Pegawai</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presensis as $presensi)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $presensi->pegawai->nama }}</td>
                <td>{{ $presensi->tgl_presensi }}</td>
                <td>{{ $presensi->jam_masuk }}</td>
                <td>{{ $presensi->jam_keluar }}</td>
                <td>{{ $presensi->status }}</td>
                <td>
                    <a href="{{ route('master-data.presensi.edit', $presensi->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('master-data.presensi.destroy', $presensi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
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
