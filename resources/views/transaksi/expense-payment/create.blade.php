@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Tambah Pembayaran Beban</h3>
  
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Error!</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  
  <form action="{{ route('transaksi.expense-payment.store') }}" method="POST">@csrf
    <div class="mb-3">
      <label class="form-label">Tanggal</label>
      <input type="date" name="tanggal" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">COA Beban</label>
      <select name="coa_beban_id" class="form-select" required>
        <option value="">Pilih COA Beban</option>
        @foreach($coas as $c)
          <option value="{{ $c->kode_akun }}">{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
        @endforeach
      </select>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Metode Bayar</label>
        <select name="metode_bayar" class="form-select">
          <option value="cash">Cash</option>
          <option value="bank">Bank</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">COA Kas/Bank</label>
        <select name="coa_kasbank" class="form-select">
          <option value="">Pilih COA Kas/Bank</option>
          @foreach($kasbank as $k)
            <option value="{{ $k->kode_akun }}">{{ $k->kode_akun }} - {{ $k->nama_akun }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Nominal</label>
        <input type="number" step="0.01" min="0" name="nominal" class="form-control" required>
      </div>
    </div>
    <div class="mb-3 mt-3">
      <label class="form-label">Keterangan</label>
      <input type="text" name="deskripsi" class="form-control">
    </div>
    <button class="btn btn-success">Simpan</button>
    <a href="{{ route('transaksi.expense-payment.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>
@endsection
