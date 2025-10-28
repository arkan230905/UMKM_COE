@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Transaksi Produksi</h3>
        <a href="{{ route('transaksi.produksi.create') }}" class="btn btn-primary">Tambah Produksi</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Qty Produksi</th>
                <th>Total Biaya</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produksis as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->tanggal }}</td>
                    <td>{{ $p->produk->nama_produk }}</td>
                    <td>{{ rtrim(rtrim(number_format($p->qty_produksi,4,',','.'),'0'),',') }}</td>
                    <td>Rp {{ number_format($p->total_biaya,0,',','.') }}</td>
                    <td><a href="{{ route('transaksi.produksi.show', $p->id) }}" class="btn btn-info btn-sm">Detail</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $produksis->links() }}
</div>
@endsection
