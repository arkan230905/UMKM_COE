@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Data Pembelian</h4>

    <a href="{{ route('transaksi.pembelian.create') }}" class="btn btn-primary mb-3">Tambah Pembelian</a>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Vendor</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembelians as $pembelian)
            <tr>
                <td>{{ $pembelian->id }}</td>
                <td>{{ $pembelian->tanggal }}</td>
                <td>{{ $pembelian->vendor?->nama_vendor ?? '-' }}</td>
                <td>{{ number_format($pembelian->total, 0, ',', '.') }}</td>
                <td>
                    <a href="{{ route('transaksi.pembelian.edit', $pembelian->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('transaksi.pembelian.destroy', $pembelian->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin hapus?')">
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
