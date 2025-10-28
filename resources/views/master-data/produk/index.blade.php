@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Data Produk</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('master-data.produk.create') }}" class="btn btn-primary mb-3">Tambah Produk</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Produk</th>
                <th>Harga Jual (Rp)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produks as $produk)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $produk->nama_produk }}</td>
                    <td>
                        @if($produk->harga_jual)
                            Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}
                        @else
                            <em>Belum dihitung</em>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('master-data.produk.destroy', $produk->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada produk</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
