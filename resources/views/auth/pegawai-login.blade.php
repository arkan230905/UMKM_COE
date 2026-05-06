@extends('layouts.guest')

@section('title', 'Login Pegawai')

@section('content')
<div class="container">
    <div class="row justify-content-center min-vh-100 align-items-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <!-- Logo/Header -->
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-person-badge fs-1 text-primary"></i>
                        </div>
                        <h3 class="fw-bold text-primary">Login Pegawai</h3>
                        <p class="text-muted">Masukkan kode perusahaan dan email Anda</p>
                    </div>

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Login Gagal!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('pegawai.login.submit') }}">
                        @csrf

                        <!-- Kode Perusahaan -->
                        <div class="mb-4">
                            <label for="kode_perusahaan" class="form-label fw-semibold">
                                <i class="bi bi-building me-1"></i>
                                Kode Perusahaan
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('kode_perusahaan') is-invalid @enderror" 
                                   id="kode_perusahaan" 
                                   name="kode_perusahaan" 
                                   value="{{ old('kode_perusahaan') }}"
                                   placeholder="Masukkan kode perusahaan"
                                   required 
                                   autofocus
                                   style="text-transform: uppercase;">
                            @error('kode_perusahaan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Kode perusahaan (6-20 karakter)
                            </small>
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i>
                                Email
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="nama@email.com"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk
                            </button>
                        </div>

                        <!-- Divider -->
                        <div class="text-center my-3">
                            <hr class="my-3">
                        </div>

                        <!-- Link to Regular Login -->
                        <div class="text-center">
                            <p class="text-muted mb-2">Bukan pegawai?</p>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Login sebagai Owner/Admin
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-3 border-0 bg-light">
                <div class="card-body text-center py-3">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Login aman tanpa password. Gunakan kode perusahaan yang diberikan oleh admin.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
    border-radius: 10px;
}

#kode_perusahaan {
    letter-spacing: 2px;
    font-weight: 600;
}
</style>

<script>
// Auto uppercase kode perusahaan
document.getElementById('kode_perusahaan').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});
</script>
@endsection
