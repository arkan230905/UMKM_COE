@extends('layouts.pegawai-pembelian')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">
            <i class="bi bi-plus-circle"></i> Tambah Bahan Baku
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pegawai-pembelian.bahan-baku.index') }}">Bahan Baku</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-seam"></i> Form Bahan Baku
            </div>
            <div class="card-body">
                <form action="{{ route('pegawai-pembelian.bahan-baku.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nama_bahan" class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_bahan') is-invalid @enderror" 
                               id="nama_bahan" name="nama_bahan" value="{{ old('nama_bahan') }}" required>
                        @error('nama_bahan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="satuan_id" class="form-label">Satuan <span class="text-danger">*</span></label>
                        <select class="form-select @error('satuan_id') is-invalid @enderror" 
                                id="satuan_id" name="satuan_id" required>
                            <option value="">-- Pilih Satuan --</option>
                            @foreach($satuans as $satuan)
                            <option value="{{ $satuan->id }}" {{ old('satuan_id') == $satuan->id ? 'selected' : '' }}>
                                {{ $satuan->nama_satuan }}
                            </option>
                            @endforeach
                        </select>
                        @error('satuan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok Awal <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('stok') is-invalid @enderror" 
                               id="stok" name="stok" value="{{ old('stok', 0) }}" required>
                        @error('stok')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="harga_satuan" class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('harga_satuan') is-invalid @enderror" 
                               id="harga_satuan" name="harga_satuan" value="{{ old('harga_satuan', 0) }}" required>
                        @error('harga_satuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('pegawai-pembelian.bahan-baku.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
