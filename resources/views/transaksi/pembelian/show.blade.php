@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detail Pembelian</h3>
        <div class="d-flex gap-2">
            @if(auth()->user() && auth()->user()->hasAnyRole(['owner','admin','pegawai_pembelian']))
                <a href="{{ route('transaksi.purchase-returns.create', $pembelian->id) }}" class="btn btn-warning">
                    Retur Pembelian
                </a>
            @endif
            <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Tanggal:</strong> {{ $pembelian->tanggal?->format('d-m-Y') }}</div>
                <div class="col-md-4"><strong>Vendor:</strong> {{ $pembelian->vendor->nama_vendor ?? '-' }}</div>
                <div class="col-md-4"><strong>Total:</strong> Rp {{ number_format($pembelian->total,0,',','.') }}</div>
                <div class="col-md-4"><strong>Pembayaran:</strong> {{ ($pembelian->payment_method ?? 'cash')==='credit' ? 'Kredit' : 'Tunai' }}</div>
            </div>
        </div>
    </div>

    <h5 class="mb-2">Rincian Barang</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Nama Bahan</th>
                    <th class="text-end">Kuantitas</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga per Satuan</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($pembelian->details ?? []) as $i => $d)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $d->bahanBaku->nama_bahan ?? '-' }}</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</td>
                    <td>{{ $d->satuan ?: ($d->bahanBaku->satuan ?? '-') }}</td>
                    <td class="text-end">Rp {{ number_format($d->harga_satuan,0,',','.') }}</td>
                    <td class="text-end">Rp {{ number_format($d->subtotal,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'purchase', 'ref_id' => $pembelian->id]) }}" class="btn btn-outline-primary btn-sm">Lihat Jurnal (Pembelian)</a>
        <a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
