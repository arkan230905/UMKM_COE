@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Retur Pembelian {{ $retur->return_number }}</h3>
        <a href="{{ route('transaksi.pembelian.show', $retur->pembelian_id) }}" class="btn btn-secondary">Kembali ke Pembelian</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>No Retur:</strong> {{ $retur->return_number }}</div>
                <div class="col-md-4"><strong>Tanggal Retur:</strong> {{ $retur->return_date?->format('d-m-Y') }}</div>
                <div class="col-md-4"><strong>Status:</strong> {{ ucfirst($retur->status) }}</div>
                <div class="col-md-4"><strong>Pembelian ID:</strong> {{ $retur->pembelian_id }}</div>
                <div class="col-md-4"><strong>Vendor:</strong> {{ $retur->pembelian->vendor->nama_vendor ?? '-' }}</div>
                <div class="col-md-4"><strong>Total Nilai Retur:</strong> Rp {{ number_format($retur->total_return_amount,0,',','.') }}</div>
                <div class="col-md-12"><strong>Alasan:</strong> {{ $retur->reason ?? '-' }}</div>
                <div class="col-md-12"><strong>Catatan:</strong> {{ $retur->notes ?? '-' }}</div>
            </div>
        </div>
    </div>

    <h5 class="mb-2">Rincian Barang Diretur</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Nama Bahan</th>
                    <th class="text-end">Qty Retur</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Satuan</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($retur->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->bahanBaku->nama_bahan ?? '-' }}</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format($item->quantity,4,',','.'),'0'),',') }}</td>
                        <td>{{ $item->unit ?? ($item->bahanBaku->satuan ?? '-') }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_price,0,',','.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($retur->status !== 'completed' && auth()->user() && auth()->user()->hasAnyRole(['owner','admin','pegawai_pembelian']))
        <form action="{{ route('transaksi.purchase-returns.approve', $retur->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Approve retur ini dan update stok?');">
            @csrf
            <button type="submit" class="btn btn-success">Approve & Update Stok</button>
        </form>
    @endif
</div>
@endsection
