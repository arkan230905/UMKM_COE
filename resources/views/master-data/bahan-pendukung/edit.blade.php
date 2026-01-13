@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Bahan Pendukung</h1>
        <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('master-data.bahan-pendukung.update', $bahanPendukung) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="row">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">Kode Bahan</label>
                            <input type="text" class="form-control" value="{{ $bahanPendukung->kode_bahan }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahan" class="form-control @error('nama_bahan') is-invalid @enderror" 
                                   value="{{ old('nama_bahan', $bahanPendukung->nama_bahan) }}" required>
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
                                    @foreach($kategoris as $kat)
                                        <option value="{{ $kat->id }}" {{ ($bahanPendukung->kategori_id ?? '') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
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
                                @foreach($satuans as $satuan)
                                    <option value="{{ $satuan->id }}" {{ $bahanPendukung->satuan_id == $satuan->id ? 'selected' : '' }}>
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
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Harga per Satuan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga_satuan" class="form-control" 
                                       value="{{ old('harga_satuan', $bahanPendukung->harga_satuan) }}" min="0" step="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-control" 
                                   value="{{ old('stok', $bahanPendukung->stok) }}" min="0" step="0.01">
                            <small class="text-muted">Stok saat ini</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Stok Minimum</label>
                            <input type="number" name="stok_minimum" class="form-control" 
                                   value="{{ old('stok_minimum', $bahanPendukung->stok_minimum) }}" min="0" step="0.01">
                            <small class="text-muted">Batas minimum</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                       {{ $bahanPendukung->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $bahanPendukung->deskripsi) }}</textarea>
                </div>

                <hr>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('master-data.bahan-pendukung.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
