@extends('layouts.app')
@section('content')
<h2>Tambah Presensi</h2>
@if ($errors->any())<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form action="{{ route('master-data.presensi.store') }}" method="POST">
@csrf
<div class="mb-3"><label>Pegawai</label>
<select name="id_pegawai" class="form-control">
@foreach($pegawais as $pegawai)
<option value="{{ $pegawai->id }}">{{ $pegawai->nama }}</option>
@endforeach
</select>
</div>
<div class="mb-3"><label>Tanggal</label><input type="date" name="tgl_presensi" class="form-control"></div>
<div class="mb-3"><label>Jam Masuk</label><input type="time" name="jam_masuk" class="form-control"></div>
<div class="mb-3"><label>Jam Keluar</label><input type="time" name="jam_keluar" class="form-control"></div>
<button type="submit" class="btn btn-success">Simpan</button>
</form>
@endsection
