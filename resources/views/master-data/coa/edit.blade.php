@extends('layouts.app')
@section('content')
<h2>Edit COA</h2>
@if ($errors->any())
<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form action="{{ route('master-data.coa.update',$coa->id) }}" method="POST">
@csrf @method('PATCH')
<div class="mb-3"><label>Kode Akun</label><input type="text" name="kode" class="form-control" value="{{ $coa->kode }}" required></div>
<div class="mb-3"><label>Nama Akun</label><input type="text" name="nama" class="form-control" value="{{ $coa->nama }}" required></div>
<div class="mb-3"><label>Jenis</label>
<select name="jenis" class="form-control" required>
<option value="Aset" {{ $coa->jenis=='Aset'?'selected':'' }}>Aset</option>
<option value="Kewajiban" {{ $coa->jenis=='Kewajiban'?'selected':'' }}>Kewajiban</option>
<option value="Ekuitas" {{ $coa->jenis=='Ekuitas'?'selected':'' }}>Ekuitas</option>
<option value="Pendapatan" {{ $coa->jenis=='Pendapatan'?'selected':'' }}>Pendapatan</option>
<option value="Beban" {{ $coa->jenis=='Beban'?'selected':'' }}>Beban</option>
</select></div>
<button class="btn btn-success">Update</button>
<a href="{{ route('master-data.coa.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
