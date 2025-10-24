@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Data Chart of Account (COA)</h2>

    <a href="{{ route('master-data.coa.create') }}" class="btn btn-primary mb-3">Tambah Akun</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Tipe Akun</th>
                <th>Dibuat Pada</th>
                <th>Diperbarui Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($coas as $coa)
                <tr>
                    <td>{{ $coa->id }}</td>
                    <td>{{ $coa->kode_akun }}</td>
                    <td>{{ $coa->nama_akun }}</td>
                    <td>{{ $coa->tipe_akun }}</td>
                    <td>{{ $coa->created_at ? $coa->created_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $coa->updated_at ? $coa->updated_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <a href="{{ route('master-data.coa.edit', $coa->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('master-data.coa.destroy', $coa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data COA.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
