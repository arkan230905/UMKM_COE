@extends('layouts.app')

@section('content')
<h2>Detail Laporan Penjualan #{{ $penjualan->id }}</h2>

<p><strong>Tanggal:</strong> {{ $penjualan->tanggal }}</p>
<p><strong>Customer:</strong> {{ $penjualan->customer }}</p>
<p><strong>Total:</strong> Rp {{ number_format($penjualan->total, 0, ',', '.') }}</p>

<table class="table table-dark table-striped mt-3">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($penjualan->detail as $item)
            <tr>
                <td>{{ $item->produk->nama_produk }}</td>
                <td>{{ $item->jumlah }}</td>
                <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
