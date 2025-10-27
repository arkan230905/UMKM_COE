@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Laporan Penjualan</h1>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Tanggal</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualan as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $p->produk->nama_produk ?? '-' }}</td>
                    <td>{{ $p->tanggal }}</td>
                    <td>Rp{{ number_format($p->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
