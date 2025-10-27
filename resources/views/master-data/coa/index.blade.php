@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Data COA</h1>

    <a href="{{ route('master-data.coa.create') }}" class="btn btn-primary mb-3">Tambah COA</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Jenis</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($coas as $coa)
            <tr>
                <td>{{ $coa->id }}</td>
                <td>{{ $coa->kode_akun }}</td>
                <td>{{ $coa->nama_akun }}</td>
                <td>{{ $coa->jenis }}</td>
                <td>
                    <a href="{{ route('master-data.coa.edit', $coa->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('master-data.coa.destroy', $coa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus COA ini?')">
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