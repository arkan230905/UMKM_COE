@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Daftar Satuan</h2>

    <a href="{{ route('master-data.satuan.create') }}" class="btn btn-primary mb-3">Tambah Satuan</a>

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>ID</th>
                <th>
                    <a href="{{ route('master-data.satuan.index', ['sort_by' => 'kode', 'sort_dir' => (isset($sortBy) && $sortBy == 'kode' && isset($sortDir) && $sortDir == 'asc' ? 'desc' : 'asc')]) }}" class="text-dark text-decoration-none">
                        Kode
                        @if(isset($sortBy) && $sortBy == 'kode')
                            <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @else
                            <i class="fas fa-sort ms-1 text-muted"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('master-data.satuan.index', ['sort_by' => 'nama', 'sort_dir' => (isset($sortBy) && $sortBy == 'nama' && isset($sortDir) && $sortDir == 'asc' ? 'desc' : 'asc')]) }}" class="text-dark text-decoration-none">
                        Nama
                        @if(isset($sortBy) && $sortBy == 'nama')
                            <i class="fas fa-sort-{{ $sortDir == 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @else
                            <i class="fas fa-sort ms-1 text-muted"></i>
                        @endif
                    </a>
                </th>
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
