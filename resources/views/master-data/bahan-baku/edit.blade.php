@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Edit Bahan Baku</h2>

    <form action="{{ route('master-data.bahan-baku.update', $bahanBaku->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama_bahan" class="form-label">Nama Bahan</label>
            <input type="text" name="nama_bahan" class="form-control" value="{{ $bahanBaku->nama_bahan }}" required>
        </div>

        <div class="mb-3">
            <label for="satuan_id" class="form-label">Satuan</label>
            <select name="satuan_id" id="satuan_id" class="form-select bg-dark text-white" required>
                <option value="" disabled>-- Pilih Satuan --</option>
                @foreach($satuans as $satuan)
                    <option value="{{ $satuan->id }}" {{ old('satuan_id', $bahanBaku->satuan_id) == $satuan->id ? 'selected' : '' }}>
                        {{ $satuan->nama }} ({{ $satuan->kode }})
                    </option>
                @endforeach
            </select>
            @error('satuan_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="harga_satuan" class="form-label">Harga Satuan (Rp)</label>
            <input type="number" 
                   name="harga_satuan" 
                   id="harga_satuan"
                   class="form-control @error('harga_satuan') is-invalid @enderror" 
                   value="{{ old('harga_satuan', $bahanBaku->harga_satuan) }}" 
                   step="0.01" 
                   min="0" 
                   required>
            @error('harga_satuan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">Perbarui</button>
        <a href="{{ route('master-data.bahan-baku.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
