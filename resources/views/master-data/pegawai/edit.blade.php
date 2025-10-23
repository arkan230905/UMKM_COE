@extends('layouts.app')
@section('content')
<h2>Edit Pegawai</h2>
@if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('master-data.pegawai.update', $pegawai->id) }}" method="POST">
@csrf @method('PUT')
<div class="mb-3"><label>Nama</label><input type="text" name="nama" class="form-control" value="{{ $pegawai->nama }}"></div>
<div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="{{ $pegawai->email }}"></div>
<button type="submit" class="btn btn-success">Update</button>
</form>
@endsection
