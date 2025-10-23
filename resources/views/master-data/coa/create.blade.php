@extends('layouts.app')
@section('content')
<h2>Tambah COA</h2>
@if ($errors->any())
<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('master-data.coa.store') }}" method="POST">
@csrf
<div class="mb-3"><label>Kode Akun</label><input type="text" name="kode" class="form-control" required></div>
<div class="mb-3"><label>Nama Akun</label><input type="text" name="nama" class="form-control" required></div>
<div class="mb-3"><label>Jenis</label>
<select name="jenis" class="form-control" required>
<option value="Aset">Aset</option>
<option value="Kewajiban">Kewajiban</option>
<option value="Ekuitas">Ekuitas</option>
<option value="Pendapatan">Pendapatan</option>
<option value="Beban">Beban</option>
</select></div>
<button class="btn btn-success">Simpan</button>
<a href="{{ route('master-data.coa.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
