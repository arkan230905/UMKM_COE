@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Satuan</h2>

    <form action="{{ route('master-data.satuan.update', $satuan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama" class="form-label">Nama Satuan</label>
            <input type="text" name="nama" class="form-control" value="{{ $satuan->nama }}" required>
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('master-data.satuan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
