@extends('layouts.app')
@section('content')
<h2>Edit COA</h2>
@if ($errors->any())
<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('master-data.coa.update',$coa->kode_akun) }}" method="POST">
@csrf @method('PATCH')
<div class="mb-3"><label>Kode Akun</label><input type="text" name="kode_akun" class="form-control" value="{{ $coa->kode_akun }}" required></div>
<div class="mb-3"><label>Nama Akun</label><input type="text" name="nama_akun" class="form-control" value="{{ $coa->nama_akun }}" required></div>
<div class="mb-3"><label>Tipe Akun</label>
<select name="tipe_akun" class="form-control" required>
<option value="Asset" {{ $coa->tipe_akun=='Asset'?'selected':'' }}>Asset</option>
<option value="Liability" {{ $coa->tipe_akun=='Liability'?'selected':'' }}>Liability</option>
<option value="Equity" {{ $coa->tipe_akun=='Equity'?'selected':'' }}>Equity</option>
<option value="Revenue" {{ $coa->tipe_akun=='Revenue'?'selected':'' }}>Revenue</option>
<option value="Expense" {{ $coa->tipe_akun=='Expense'?'selected':'' }}>Expense</option>
<option value="Beban" {{ $coa->tipe_akun=='Beban'?'selected':'' }}>Beban</option>
</select></div>
<button class="btn btn-success">Update</button>
<a href="{{ route('master-data.coa.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
