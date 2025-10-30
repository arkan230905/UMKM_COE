@extends('layouts.app')
@section('content')
<div class="container">
  <h3>Invoice Pelunasan Utang #{{ $row->id }}</h3>
  <p>Tanggal: {{ $row->tanggal }}</p>
  <p>Pembelian: #{{ $row->pembelian_id }} - Vendor: {{ $row->pembelian->vendor->nama_vendor ?? '-' }}</p>
  <p>Total Tagihan: Rp {{ number_format($row->total_tagihan,0,',','.') }}</p>
  <p>Diskon: Rp {{ number_format($row->diskon,0,',','.') }}</p>
  <p>Denda/Bunga: Rp {{ number_format($row->denda_bunga,0,',','.') }}</p>
  <p>Dibayar Bersih: Rp {{ number_format($row->dibayar_bersih,0,',','.') }}</p>
</div>
@endsection
