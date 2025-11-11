@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Laporan Penjualan</h3>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Produk</th>
                    <th>Tanggal</th>
                    <th class="text-end">Total</th>
                    <th style="width:15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($penjualan as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($p->details && $p->details->count() > 0)
                                @foreach($p->details as $detail)
                                    {{ $detail->produk->nama_produk ?? $detail->produk->nama ?? '-' }}
                                    ({{ $detail->jumlah ?? $detail->qty ?? 0 }} pcs)
                                    @if(!$loop->last), @endif
                                @endforeach
                            @else
                                {{ $p->produk->nama_produk ?? $p->produk->nama ?? '-' }}
                            @endif
                        </td>
                        <td>{{ optional($p->tanggal)->format('d-m-Y') ?? $p->tanggal }}</td>
                        <td class="text-end">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('laporan.penjualan.invoice', $p->id) }}">
                                Cetak Invoice
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
