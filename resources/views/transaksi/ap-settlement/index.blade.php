@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pelunasan Utang</h3>
  </div>

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

  <div class="card mb-4">
    <div class="card-header">Pembelian Kredit Belum Lunas</div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>#</th><th>Tanggal</th><th>Vendor</th><th>Total</th><th>Aksi</th></tr></thead>
        <tbody>
          @foreach($openPurchases as $p)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $p->tanggal }}</td>
            <td>{{ $p->vendor->nama_vendor ?? '-' }}</td>
            <td>Rp {{ number_format($p->total,0,',','.') }}</td>
            <td><a class="btn btn-primary btn-sm" href="{{ route('transaksi.ap-settlement.create', ['pembelian_id'=>$p->id]) }}">Lunasi</a></td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <h5>Riwayat Pelunasan</h5>
  <table class="table table-bordered">
    <thead class="table-dark"><tr><th>#</th><th>Tanggal</th><th>Vendor</th><th>Pembelian</th><th>Dibayar</th><th>Diskon</th><th>Denda</th><th>Aksi</th></tr></thead>
    <tbody>
      @foreach($rows as $r)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $r->tanggal }}</td>
        <td>{{ $r->pembelian->vendor->nama_vendor ?? '-' }}</td>
        <td>#{{ $r->pembelian_id }}</td>
        <td>Rp {{ number_format($r->dibayar_bersih,0,',','.') }}</td>
        <td>Rp {{ number_format($r->diskon,0,',','.') }}</td>
        <td>Rp {{ number_format($r->denda_bunga,0,',','.') }}</td>
        <td><a href="{{ route('transaksi.ap-settlement.show', $r->id) }}" class="btn btn-info btn-sm">Invoice</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
  {{ $rows->links() }}
</div>
@endsection
