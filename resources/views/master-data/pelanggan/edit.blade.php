@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white">
            <i class="bi bi-pencil"></i> Edit Data Pelanggan
        </h2>
        <a href="{{ route('master-data.pelanggan.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="bi bi-person-gear"></i> Form Edit Pelanggan
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('master-data.pelanggan.update', $pelanggan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-dark text-white @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $pelanggan->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control bg-dark text-white @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $pelanggan->email) }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control bg-dark text-white @error('username') is-invalid @enderror" 
                                   value="{{ old('username', $pelanggan->username) }}" required>
                            @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">No. Telepon</label>
                            <input type="text" name="phone" class="form-control bg-dark text-white @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $pelanggan->phone) }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <hr class="border-secondary">
                        <h6 class="text-white mb-3">
                            <i class="bi bi-key"></i> Ubah Password (Opsional)
                        </h6>
                        <p class="text-muted small">Kosongkan jika tidak ingin mengubah password</p>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Password Baru</label>
                            <input type="password" name="password" class="form-control bg-dark text-white @error('password') is-invalid @enderror">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-white">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control bg-dark text-white">
                            <small class="text-muted">Ulangi password baru</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('master-data.pelanggan.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> 
                <strong>Catatan:</strong>
                <ul class="mb-0 mt-2">
                    <li>Email dan username harus unik (tidak boleh sama dengan user lain)</li>
                    <li>Password hanya diubah jika field password diisi</li>
                    <li>Jika mengubah password, pastikan konfirmasi password sama</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
