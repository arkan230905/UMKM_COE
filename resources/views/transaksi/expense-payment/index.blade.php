@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Pembayaran Beban</h3>
    <a href="{{ route('transaksi.expense-payment.create') }}" class="btn btn-primary">Tambah</a>
  </div>

  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>#</th><th>Tanggal</th><th>COA Beban</th><th>Nominal</th><th>Keterangan</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $r)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $r->tanggal }}</td>
        <td>{{ $r->coa->kode_akun }} - {{ $r->coa->nama_akun }}</td>
        <td>Rp {{ number_format($r->nominal,0,',','.') }}</td>
        <td>{{ $r->deskripsi }}</td>
        <td><a href="{{ route('transaksi.expense-payment.show', $r->id) }}" class="btn btn-info btn-sm">Invoice</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{ $rows->links() }}
</div>
@endsection
