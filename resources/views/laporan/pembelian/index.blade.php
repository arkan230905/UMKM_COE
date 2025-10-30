@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Laporan Pembelian</h3>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Tanggal</th>
                    <th>Vendor</th>
                    <th class="text-end">Total</th>
                    <th style="width:15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pembelian as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ optional($p->tanggal)->format('d-m-Y') ?? $p->tanggal }}</td>
                        <td>{{ $p->vendor->nama_vendor ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('laporan.pembelian.invoice', $p->id) }}">
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
