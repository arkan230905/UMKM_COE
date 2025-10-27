@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Daftar Satuan</h2>

    <a href="{{ route('master-data.satuan.create') }}" class="btn btn-primary mb-3">Tambah Satuan</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($satuans as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->kode }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>
                        <a href="{{ route('master-data.satuan.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('master-data.satuan.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus data ini?')" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada data satuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
