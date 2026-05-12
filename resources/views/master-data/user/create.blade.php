@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-user-plus me-2"></i>Tambah User Baru
        </h2>
        <a href="{{ route('master-data.user.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Form User Baru
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('master-data.user.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                @error('password')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                @error('password_confirmation')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="owner">Owner</option>
                                    <option value="pelanggan">Pelanggan</option>
                                    <option value="pegawai_pembelian">Pegawai Gudang</option>
                                    <option value="kasir">Kasir</option>
                                </select>
                                @error('role')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="perusahaan_id" class="form-label">Perusahaan (Opsional)</label>
                                <select class="form-select" id="perusahaan_id" name="perusahaan_id">
                                    <option value="">Pilih Perusahaan</option>
                                    @foreach(\App\Models\Perusahaan::all() as $perusahaan)
                                        <option value="{{ $perusahaan->id }}">{{ $perusahaan->nama }}</option>
                                    @endforeach
                                </select>
                                @error('perusahaan_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan User
                                </button>
                                <a href="{{ route('master-data.user.index') }}" class="btn btn-secondary">
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
