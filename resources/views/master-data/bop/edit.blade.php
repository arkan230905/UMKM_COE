@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Edit Data BOP</h4>

    <form action="{{ route('master-data.bop.update', $bop->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nominal --}}
        <div class="form-group mb-3">
            <label for="nominal" class="form-label">Nominal</label>
            <input type="number" name="nominal" id="nominal"
                   class="form-control"
                   value="{{ old('nominal', $bop->nominal) }}"
                   required>
        </div>

        {{-- Tanggal --}}
        <div class="form-group mb-4">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" name="tanggal" id="tanggal"
                   class="form-control"
                   value="{{ old('tanggal', $bop->tanggal) }}"
                   required>
        </div>

        {{-- Tombol Aksi --}}
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('master-data.bop.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection