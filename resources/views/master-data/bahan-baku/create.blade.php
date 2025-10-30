@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Bahan Baku</h2>

    <form action="{{ route('master-data.bahan-baku.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nama_bahan" class="form-label">Nama Bahan</label>
            <input type="text" name="nama_bahan" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="satuan_id" class="form-label">Satuan</label>
            <select name="satuan_id" id="satuan_id" class="form-select bg-dark text-white" required>
                <option value="" disabled selected>-- Pilih Satuan --</option>
                @foreach($satuans as $satuan)
                    <option value="{{ $satuan->id }}" {{ old('satuan_id') == $satuan->id ? 'selected' : '' }}>
                        {{ $satuan->nama }} ({{ $satuan->kode }})
                    </option>
                @endforeach
            </select>
            @error('satuan_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="stok" class="form-label">Stok Awal</label>
            <input type="number" 
                   name="stok" 
                   id="stok"
                   class="form-control @error('stok') is-invalid @enderror" 
                   value="{{ old('stok', 0) }}" 
                   min="0" 
                   required>
            @error('stok')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="harga_satuan" class="form-label">Harga Satuan (Rp)</label>
            <input type="number" 
                   name="harga_satuan" 
                   id="harga_satuan"
                   class="form-control @error('harga_satuan') is-invalid @enderror" 
                   value="{{ old('harga_satuan', 0) }}" 
                   step="0.01" 
                   min="0" 
                   required>
            @error('harga_satuan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('master-data.bahan-baku.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
