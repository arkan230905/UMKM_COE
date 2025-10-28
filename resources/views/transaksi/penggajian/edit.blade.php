@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Edit Data Penggajian</h4>

    <form action="{{ route('transaksi.penggajian.update', $penggajian->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="pegawai" class="form-label">Pegawai</label>
            <input type="text" value="{{ $penggajian->pegawai->nama }}" class="form-control" disabled>
        </div>

        <div class="mb-3">
            <label for="tunjangan" class="form-label">Tunjangan</label>
            <input type="number" name="tunjangan" class="form-control" value="{{ $penggajian->tunjangan }}">
        </div>

        <div class="mb-3">
            <label for="potongan" class="form-label">Potongan</label>
            <input type="number" name="potongan" class="form-control" value="{{ $penggajian->potongan }}">
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('transaksi.penggajian.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
