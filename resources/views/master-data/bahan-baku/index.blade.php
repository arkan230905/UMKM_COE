@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Bahan Baku</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('master-data.bahan-baku.create') }}" class="btn btn-primary mb-3">+ Tambah Bahan Baku</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th>Harga Satuan</th>
                <th width="20%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bahanBaku as $bahan)
                <tr>
                    <td>{{ $bahan->id }}</td>
                    <td>{{ $bahan->nama_bahan }}</td>
                    <td>{{ $bahan->satuan ?? '-' }}</td>
                    <td>Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('master-data.bahan-baku.edit', $bahan->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('master-data.bahan-baku.destroy', $bahan->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada data bahan baku.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
