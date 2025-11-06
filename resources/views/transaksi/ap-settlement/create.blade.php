@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Pelunasan Utang Pembelian #{{ $pembelian->id }}</h3>
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <form action="{{ route('transaksi.ap-settlement.store') }}" method="POST">@csrf
    <input type="hidden" name="pembelian_id" value="{{ $pembelian->id }}">
    <div class="mb-3">
      <label class="form-label">Tanggal</label>
      <input type="date" name="tanggal" class="form-control" required>
    </div>
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Total Tagihan</label>
        <input type="number" step="0.01" min="0" name="total_tagihan" class="form-control" value="{{ $pembelian->total }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Diskon</label>
        <input type="number" step="0.01" min="0" name="diskon" class="form-control" value="0">
      </div>
      <div class="col-md-3">
        <label class="form-label">Denda/Bunga</label>
        <input type="number" step="0.01" min="0" name="denda_bunga" class="form-control" value="0">
      </div>
      <div class="col-md-3">
        <label class="form-label">Dibayar Bersih</label>
        <input type="number" step="0.01" min="0" name="dibayar_bersih" id="dibayar_bersih" class="form-control" required>
      </div>
    </div>
    <div class="row g-3 mt-2">
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
          <option value="101">101 - Kas</option>
        </select>
      </div>
    </div>
    <div class="mb-3 mt-2">
      <label class="form-label">Keterangan</label>
      <input type="text" name="keterangan" class="form-control">
    </div>
    <button class="btn btn-success">Simpan</button>
    <a href="{{ route('transaksi.ap-settlement.index') }}" class="btn btn-secondary">Batal</a>
  </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const total = document.querySelector('input[name="total_tagihan"]');
  const diskon = document.querySelector('input[name="diskon"]');
  const denda = document.querySelector('input[name="denda_bunga"]');
  const bersih = document.getElementById('dibayar_bersih');
  function recalc(){
    const t = parseFloat(total.value || '0')||0;
    const d = parseFloat(diskon.value || '0')||0;
    const f = parseFloat(denda.value || '0')||0;
    const val = Math.max(t - d + f, 0);
    bersih.value = val.toFixed(2);
  }
  [total,diskon,denda].forEach(el=>{ if(el){ el.addEventListener('input', recalc); }});
  recalc();
});
</script>
@endsection
