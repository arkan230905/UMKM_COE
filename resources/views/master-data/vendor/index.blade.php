@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Data Vendor</h2>
    <a href="{{ route('master-data.vendor.create') }}" class="btn btn-success mb-3">Tambah Vendor</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Vendor</th>
                <th>Kategori</th>
                <th>Alamat</th>
                <th>No. Telp</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
            <tr>
                <td>{{ $vendor->id }}</td>
                <td>{{ $vendor->nama_vendor }}</td>
                <td>{{ $vendor->kategori }}</td>
                <td>{{ $vendor->alamat }}</td>
                <td>{{ $vendor->no_telp }}</td>
                <td>{{ $vendor->email }}</td>
                <td>{{ $vendor->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $vendor->updated_at->format('d-m-Y H:i') }}</td>
                <td>
                    <a href="{{ route('master-data.vendor.edit', $vendor->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('master-data.vendor.destroy', $vendor->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin dihapus?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="9">Belum ada data vendor.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
