@extends('layouts.catalog')

@php
    $namaPerusahaan = \App\Models\Perusahaan::first()->nama ?? 'Toko Kami';
@endphp

@section('title', 'Daftar Pelanggan - ' . $namaPerusahaan)

@push('styles')
<style>
body {
    background: url('/images/latar login pelanggan.jpg') center center / cover no-repeat fixed !important;
    min-height: 100vh;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-user-plus fa-3x text-warning"></i>
                        </div>
                        <h2 class="fw-bold">Daftar Pelanggan</h2>
                        <p class="text-muted">{{ $namaPerusahaan }}</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('pelanggan.register.post') }}">
                        @csrf

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                <i class="fas fa-user me-2"></i>Nama Lengkap
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Masukkan nama lengkap"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="email@example.com"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">
                                <i class="fas fa-phone me-2"></i>No. Telepon
                            </label>
                            <input type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="0812-3456-7890"
                                   required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">
                                <i class="fas fa-map-marker-alt me-2"></i>Alamat
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address"
                                      name="address"
                                      rows="3"
                                      placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Minimal 8 karakter"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Konfirmasi Password
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control @error('password_confirmation') is-invalid @enderror"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Ulangi password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                    <i class="fas fa-eye" id="passwordConfirmIcon"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-warning w-100 py-3 fw-semibold">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </button>
                    </form>

                    <!-- Login Link -->
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">
                            Sudah punya akun?
                            <a href="{{ route('pelanggan.login') }}" class="text-warning text-decoration-none fw-semibold">
                                Login di sini
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 20px;
    margin-top: 2rem;
    margin-bottom: 2rem;
}
.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    transition: all 0.3s;
}
.form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}
.btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    border: none;
    border-radius: 10px;
    transition: all 0.3s;
}
.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
}
.text-warning { color: #d39e00 !important; }
.text-warning:hover { color: #b8941f !important; }
@media (max-width: 768px) {
    .card-body { padding: 2rem !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle password
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    if (togglePassword) {
        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            passwordIcon.classList.toggle('fa-eye', !isPassword);
            passwordIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    }

    // Toggle password confirmation
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const passwordConfirmIcon = document.getElementById('passwordConfirmIcon');

    if (togglePasswordConfirm) {
        togglePasswordConfirm.addEventListener('click', function () {
            const isPassword = passwordConfirmInput.getAttribute('type') === 'password';
            passwordConfirmInput.setAttribute('type', isPassword ? 'text' : 'password');
            passwordConfirmIcon.classList.toggle('fa-eye', !isPassword);
            passwordConfirmIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    }

    // Auto-focus
    document.getElementById('name').focus();
});
</script>
@endsection
