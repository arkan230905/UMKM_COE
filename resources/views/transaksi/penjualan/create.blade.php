@extends('layouts.app')
@section('content')
<h2>Tambah Penjualan</h2>
@if ($errors->any())
<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('transaksi.penjualan.store') }}" method="POST">
@csrf
<div class="mb-3"><label>Produk</label>
<select name="produk_id" class="form-control" required>
@foreach($produks as $p)
<option value="{{ $p->id }}">{{ $p->nama }}</option>
@endforeach
</select></div>
<div class="mb-3"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" required></div>
<div class="mb-3"><label>Jumlah</label><input type="number" step="0.01" name="jumlah" class="form-control" required></div>
<div class="mb-3"><label>Total</label><input type="number" step="0.01" name="total" class="form-control" required></div>
<button class="btn btn-success">Simpan</button>
<a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
