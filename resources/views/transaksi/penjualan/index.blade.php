@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Data Penjualan</h4>

    <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-primary mb-3">Tambah Penjualan</a>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualans as $penjualan)
            <tr>
                <td>{{ $penjualan->id }}</td>
                <td>{{ $penjualan->tanggal }}</td>
                <td>{{ $penjualan->produk?->nama_produk ?? '-' }}</td>
                <td>{{ $penjualan->jumlah }}</td>
                <td>{{ number_format($penjualan->total, 0, ',', '.') }}</td>
                <td>
                    <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('transaksi.penjualan.destroy', $penjualan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
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
