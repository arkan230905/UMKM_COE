@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Satuan</h2>

    <form action="{{ route('master-data.satuan.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Satuan</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('master-data.satuan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
