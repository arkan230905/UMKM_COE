@extends('layouts.app')

@section('title', 'Tambah Aset')

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">Tambah Aset</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('aset.store') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nama Aset</label>
            <input type="text" name="nama_asset" class="form-control" required value="{{ old('nama_asset') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Tanggal Beli</label>
            <input type="date" name="tanggal_beli" class="form-control" required value="{{ old('tanggal_beli') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Umur Ekonomis (tahun)</label>
            <input type="number" name="umur_ekonomis" class="form-control" min="1" required value="{{ old('umur_ekonomis') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Harga Perolehan (Rp)</label>
            <input type="number" step="0.01" name="harga_perolehan" class="form-control" required value="{{ old('harga_perolehan') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Nilai Sisa (Rp)</label>
            <input type="number" step="0.01" name="nilai_sisa" class="form-control" required value="{{ old('nilai_sisa') }}">
          </div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Simpan</button>
          <a class="btn btn-secondary" href="{{ route('aset.index') }}">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
