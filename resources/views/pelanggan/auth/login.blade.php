<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pelanggan - UMKM COE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 90%;
        }
        
        .login-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-right {
            padding: 3rem;
        }
        
        .brand-logo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .brand-tagline {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .feature-list i {
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-register {
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        @media (max-width: 768px) {
            .login-left {
                display: none;
            }
            
            .login-right {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="row g-0 h-100">
            <!-- Left Side - Branding -->
            <div class="col-md-5 login-left">
                <div class="brand-logo">
                    <i class="fas fa-shopping-bag me-2"></i>
                    UMKM COE
                </div>
                <div class="brand-tagline">
                    Temukan produk berkualitas terbaik untuk kebutuhan Anda
                </div>
                <ul class="feature-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span>Produk berkualitas tinggi</span>
                    </li>
                    <li>
                        <i class="fas fa-truck"></i>
                        <span>Pengiriman cepat dan aman</span>
                    </li>
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        <span>Pembayaran terjamin</span>
                    </li>
                    <li>
                        <i class="fas fa-headset"></i>
                        <span>Layanan pelanggan 24/7</span>
                    </li>
                </ul>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="col-md-7 login-right">
                <div class="text-center mb-4">
                    <h2 class="mb-3">Login Pelanggan</h2>
                    <p class="text-muted">
                        @if($productId)
                            Login untuk melanjutkan pemesanan produk
                        @else
                            Masuk ke akun pelanggan Anda
                        @endif
                    </p>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('pelanggan.login.post') }}">
                    @csrf
                    
                    <!-- Hidden fields for redirect -->
                    <input type="hidden" name="redirect" value="{{ $redirect ?? 'pelanggan.dashboard' }}">
                    @if($productId)
                        <input type="hidden" name="product" value="{{ $productId }}">
                    @endif

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">
                            <i class="fas fa-envelope me-2"></i>Email
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Masukkan email Anda" 
                               required 
                               autocomplete="email"
                               autofocus>
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="Masukkan password Anda" 
                               required 
                               autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Ingat saya
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-login btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center">
                        <p class="mb-0">
                            Belum punya akun? 
                            <a href="#" class="text-decoration-none fw-bold">
                                Daftar sekarang
                            </a>
                        </p>
                        <p class="mt-2">
                            <a href="{{ route('catalog') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                Kembali ke Katalog
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
