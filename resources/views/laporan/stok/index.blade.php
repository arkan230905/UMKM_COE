@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Laporan Stok Produk</h1>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Stok</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produk as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $p->nama_produk }}</td>
                    <td>{{ $p->stok }}</td>
                    <td>Rp{{ number_format($p->harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
