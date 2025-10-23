@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Data Retur</h4>

    <a href="{{ route('transaksi.retur.create') }}" class="btn btn-primary mb-3">Tambah Retur</a>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Pembelian</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returs as $retur)
            <tr>
                <td>{{ $retur->id }}</td>
                <td>{{ $retur->tanggal }}</td>
                <td>{{ $retur->produk?->nama_produk ?? '-' }}</td>
                <td>{{ $retur->jumlah }}</td>
                <td>{{ $retur->pembelian?->id ?? '-' }}</td>
                <td>
                    <a href="{{ route('transaksi.retur.edit', $retur->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('transaksi.retur.destroy', $retur->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
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
