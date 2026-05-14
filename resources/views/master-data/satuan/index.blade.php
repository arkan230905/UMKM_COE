@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Daftar Satuan</h2>

    <a href="{{ route('master-data.satuan.create') }}" class="btn btn-primary mb-3">Tambah Satuan</a>

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
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('master-data.satuan.edit', $item->id) }}"
                               class="btn btn-outline-primary"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('master-data.satuan.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-outline-danger"
                                        title="Hapus"
                                        onclick="return confirm('Hapus data ini?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
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
