@extends('layouts.app')
@section('content')
<div class="container">
  <h3>Invoice Pembayaran Beban #{{ $row->id }}</h3>
  <p>Tanggal: {{ $row->tanggal }}</p>
  <p>Beban: {{ $row->coa->kode_akun }} - {{ $row->coa->nama_akun }}</p>
  <p>Nominal: Rp {{ number_format($row->nominal,0,',','.') }}</p>
  <p>Keterangan: {{ $row->deskripsi }}</p>
</div>
@endsection
