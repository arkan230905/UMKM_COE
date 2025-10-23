@extends('layouts.app')
@section('content')
<h2>Tambah Pembelian</h2>
@if ($errors->any())
<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('transaksi.pembelian.store') }}" method="POST">
@csrf
<div class="mb-3"><label>Vendor</label>
<select name="vendor_id" class="form-control" required>
@foreach($vendors as $v)
<option value="{{ $v->id }}">{{ $v->nama }}</option>
@endforeach
</select></div>
<div class="mb-3"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" required></div>
<div class="mb-3"><label>Total</label><input type="number" step="0.01" name="total" class="form-control" required></div>
<button class="btn btn-success">Simpan</button>
<a href="{{ route('transaksi.pembelian.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
