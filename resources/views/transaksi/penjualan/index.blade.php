@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4">Data Penjualan</h4>

    <a href="{{ route('transaksi.penjualan.create') }}" class="btn btn-primary mb-3">Tambah Penjualan</a>

    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th style="width:6%">ID</th>
                <th style="width:12%">Nomor Transaksi</th>
                <th style="width:12%">Tanggal</th>
                <th style="width:10%">Pembayaran</th>
                <th>Produk</th>
                <th class="text-end" style="width:10%">Qty</th>
                <th class="text-end" style="width:12%">Harga/Satuan</th>
                <th class="text-end" style="width:10%">Diskon %</th>
                <th class="text-end" style="width:12%">Diskon (Rp)</th>
                <th class="text-end" style="width:12%">Total</th>
                <th style="width:14%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualans as $penjualan)
            <tr>
                <td>{{ $penjualan->id }}</td>
                <td><strong>{{ $penjualan->nomor_penjualan ?? '-' }}</strong></td>
                <td>{{ optional($penjualan->tanggal)->format('d-m-Y') ?? $penjualan->tanggal }}</td>
                <td>{{ ($penjualan->payment_method ?? 'cash') === 'credit' ? 'Kredit' : 'Tunai' }}</td>
                @php $detailCount = $penjualan->details->count(); @endphp
                <td>
                    @if($detailCount > 1)
                        @foreach($penjualan->details as $d)
                            <div>{{ $d->produk->nama_produk ?? '-' }}</div>
                        @endforeach
                    @elseif($detailCount === 1)
                        {{ $penjualan->details[0]->produk->nama_produk ?? '-' }}
                    @else
                        {{ $penjualan->produk?->nama_produk ?? '-' }}
                    @endif
                </td>
                <td class="text-end">
                    @if($detailCount > 1)
                        @foreach($penjualan->details as $d)
                            <div>{{ rtrim(rtrim(number_format($d->jumlah,4,',','.'),'0'),',') }}</div>
                        @endforeach
                    @elseif($detailCount === 1)
                        {{ rtrim(rtrim(number_format($penjualan->details[0]->jumlah,4,',','.'),'0'),',') }}
                    @else
                        {{ rtrim(rtrim(number_format($penjualan->jumlah,4,',','.'),'0'),',') }}
                    @endif
                </td>
                <td class="text-end">
                    @if($detailCount > 1)
                        @foreach($penjualan->details as $d)
                            <div>Rp {{ number_format($d->harga_satuan ?? 0, 0, ',', '.') }}</div>
                        @endforeach
                    @elseif($detailCount === 1)
                        Rp {{ number_format($penjualan->details[0]->harga_satuan ?? 0, 0, ',', '.') }}
                    @else
                        @php
                            $hdrHarga = $penjualan->harga_satuan;
                            if (is_null($hdrHarga) && ($penjualan->jumlah ?? 0) > 0) {
                                $hdrHarga = ((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0)) / (float)$penjualan->jumlah;
                            }
                        @endphp
                        Rp {{ number_format($hdrHarga ?? 0, 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-end">
                    @if($detailCount > 1)
                        @foreach($penjualan->details as $d)
                            @php $sub = (float)$d->jumlah * (float)$d->harga_satuan; $disc = (float)($d->diskon_nominal ?? 0); $pct = $sub>0 ? ($disc/$sub*100) : 0; @endphp
                            <div>{{ number_format($pct, 2, ',', '.') }}%</div>
                        @endforeach
                    @elseif($detailCount === 1)
                        @php $d=$penjualan->details[0]; $sub=(float)$d->jumlah*(float)$d->harga_satuan; $disc=(float)($d->diskon_nominal??0); $pct=$sub>0?($disc/$sub*100):0; @endphp
                        {{ number_format($pct, 2, ',', '.') }}%
                    @else
                        @php $pct=0; if(($penjualan->jumlah??0)>0){ $hdrHarga=$penjualan->harga_satuan; if(is_null($hdrHarga)){ $hdrHarga=((float)$penjualan->total + (float)($penjualan->diskon_nominal ?? 0))/(float)$penjualan->jumlah; } $subtotal=$penjualan->jumlah*$hdrHarga; $pct=$subtotal>0?(((float)($penjualan->diskon_nominal ?? 0))/$subtotal*100):0; } @endphp
                        {{ number_format($pct, 2, ',', '.') }}%
                    @endif
                </td>
                <td class="text-end">
                    @if($detailCount > 1)
                        @foreach($penjualan->details as $d)
                            <div>Rp {{ number_format($d->diskon_nominal ?? 0, 0, ',', '.') }}</div>
                        @endforeach
                    @elseif($detailCount === 1)
                        Rp {{ number_format($penjualan->details[0]->diskon_nominal ?? 0, 0, ',', '.') }}
                    @else
                        Rp {{ number_format($penjualan->diskon_nominal ?? 0, 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-end">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                <td>
                    <a href="{{ route('transaksi.penjualan.edit', $penjualan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('transaksi.retur-penjualan.create', ['penjualan_id' => $penjualan->id]) }}" class="btn btn-info btn-sm">Retur</a>
                    <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale', 'ref_id' => $penjualan->id]) }}" class="btn btn-outline-primary btn-sm">Jurnal</a>
                    <a href="{{ route('akuntansi.jurnal-umum', ['ref_type' => 'sale_cogs', 'ref_id' => $penjualan->id]) }}" class="btn btn-secondary btn-sm">Jurnal HPP</a>
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
