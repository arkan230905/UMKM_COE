@extends('layouts.app')

@section('title', 'Edit Data Perusahaan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-building me-2"></i>Edit Data Perusahaan
        </h2>
        <a href="/tentang-perusahaan/detail" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Form Edit Data Perusahaan
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/tentang-perusahaan">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="nama" class="form-label">Nama Perusahaan</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $dataPerusahaan->nama) }}" required>
                                @error('nama')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $dataPerusahaan->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">Email Perusahaan</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $dataPerusahaan->email) }}" required>
                                @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="telepon" name="telepon" value="{{ old('telepon', $dataPerusahaan->telepon) }}" required>
                                @error('telepon')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="/tentang-perusahaan" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
