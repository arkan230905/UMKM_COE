@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tambah Bahan Pendukung</h1>
        <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.bahan-pendukung.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                                   value="{{ old('nama_bahan') }}" placeholder="Contoh: Gas LPG, Minyak Goreng" required>
                            @error('nama_bahan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategoris as $kat)
                                        <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('master-data.kategori-bahan-pendukung.index') }}" class="btn btn-outline-secondary" title="Kelola Kategori">
                                    <i class="fas fa-cog"></i>
                                </a>
                            </div>
                            @error('kategori_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="satuan_id" class="form-select @error('satuan_id') is-invalid @enderror" required>
                                <option value="">Pilih Satuan</option>
                                @foreach($satuans as $satuan)
                                    <option value="{{ $satuan->id }}" {{ old('satuan_id') == $satuan->id ? 'selected' : '' }}>
                                        {{ $satuan->nama }} ({{ $satuan->kode }})
                                    </option>
                                @endforeach
                            </select>
                            @error('satuan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Harga per Satuan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga_satuan" class="form-control @error('harga_satuan') is-invalid @enderror" 
                                       value="{{ old('harga_satuan', 0) }}" min="0" step="100" required>
                            </div>
                            @error('harga_satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Stok Awal</label>
                            <input type="number" name="stok" class="form-control @error('stok') is-invalid @enderror" 
                                   value="{{ old('stok', 0) }}" min="0" step="0.01">
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Kosongkan jika belum ada stok</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Stok Minimum</label>
                            <input type="number" name="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror" 
                                   value="{{ old('stok_minimum', 0) }}" min="0" step="0.01">
                            @error('stok_minimum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Batas minimum untuk notifikasi</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi bahan pendukung (opsional)">{{ old('deskripsi') }}</textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
