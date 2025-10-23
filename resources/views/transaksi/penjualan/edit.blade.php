@extends('layouts.app')
@section('content')
<h2>Edit Penjualan</h2>
@if ($errors->any())
<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('transaksi.penjualan.update',$penjualan->id) }}" method="POST">
@csrf @method('PATCH')
<div class="mb-3"><label>Produk</label>
<select name="produk_id" class="form-control" required>
@foreach($produks as $p)
<option value="{{ $p->id }}" {{ $penjualan->produk_id==$p->id?'selected':'' }}>{{ $p->nama }}</option>
@endforeach
</select></div>
<div class="mb-3"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" value="{{ $penjualan->tanggal }}" required></div>
<div class="mb-3"><label>Jumlah</label><input type="number" step="0.01" name="jumlah" class="form-control" value="{{ $penjualan->jumlah }}" required></div>
<div class="mb-3"><label>Total</label><input type="number" step="0.01" name="total" class="form-control" value="{{ $penjualan->total }}" required></div>
<button class="btn btn-success">Update</button>
<a href="{{ route('transaksi.penjualan.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
