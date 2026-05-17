<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Daftar Pelanggan - {{ \App\Models\Perusahaan::first()->nama ?? 'Toko Kami' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('/images/latar login pelanggan.jpg') center center / cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 600px;
            width: 90%;
        }
        
        .auth-left {
            background: linear-gradient(135deg, #d4a574 0%, #b8935f 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .auth-right {
            padding: 1.5rem;
        }
        
        .brand-logo {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-align: center;
            line-height: 1.3;
            word-break: break-word;
        }
        
        .brand-tagline {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            font-size: 0.75rem;
        }
        
        .feature-list li {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
        }
        
        .feature-list i {
            margin-right: 0.6rem;
            font-size: 0.85rem;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 1rem;
        }
        
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .nav-tabs .nav-link:hover {
            color: #d4a574;
            border-bottom-color: #d4a574;
        }
        
        .nav-tabs .nav-link.active {
            color: #d4a574;
            border-bottom-color: #d4a574;
            background: none;
        }
        
        .form-control {
            border-radius: 6px;
            border: 2px solid #e9ecef;
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #d4a574;
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }
        
        .btn-auth {
            background: linear-gradient(135deg, #d4a574 0%, #b8935f 100%);
            border: none;
            border-radius: 6px;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(212, 165, 116, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        @media (max-width: 768px) {
            .auth-left {
                display: none;
            }
            
            .auth-right {
                padding: 1.5rem;
            }
        }
        
        .form-label {
            font-size: 0.75rem;
            margin-bottom: 0.2rem;
        }
        
        .form-check-label {
            font-size: 0.75rem;
        }
        
        h3 {
            font-size: 1.1rem !important;
        }
        
        p.text-muted {
            font-size: 0.75rem;
            margin-bottom: 0.8rem;
        }
        
        .mb-3 {
            margin-bottom: 0.6rem !important;
        }
        .mb-4 {
            margin-bottom: 1rem !important;
        }
        
        .btn-outline-secondary {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="row g-0 h-100">
            <!-- Left Side - Branding -->
            <div class="col-md-5 auth-left">
                <div class="brand-logo">
                    <i class="fas fa-shopping-bag me-2"></i>
                    {{ \App\Models\Perusahaan::first()->nama ?? 'Toko Kami' }}
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
            
            <!-- Right Side - Tabs -->
            <div class="col-md-7 auth-right">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-content" type="button" role="tab">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-content" type="button" role="tab">
                            <i class="fas fa-user-plus me-2"></i>Daftar
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- LOGIN TAB -->
                    <div class="tab-pane fade show active" id="login-content" role="tabpanel">
                        <div class="text-center mb-4">
                            <h3 class="mb-2">Login Pelanggan</h3>
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
                                <label for="login-email" class="form-label fw-bold">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="login-email" 
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
                                <label for="login-password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="login-password" 
                                           name="password" 
                                           placeholder="Masukkan password Anda" 
                                           required 
                                           autocomplete="current-password">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="login-password" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
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
                                <button type="submit" class="btn btn-auth btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login
                                </button>
                            </div>

                            <!-- Back to Catalog -->
                            <div class="text-center">
                                <a href="{{ route('pelanggan.dashboard') }}" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Kembali ke Katalog
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- REGISTER TAB -->
                    <div class="tab-pane fade" id="register-content" role="tabpanel">
                        <div class="text-center mb-4">
                            <h3 class="mb-2">Daftar Pelanggan</h3>
                            <p class="text-muted">Buat akun baru untuk berbelanja</p>
                        </div>

                        @if(session('register_error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('register_error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Registration Form -->
                        <form method="POST" action="{{ route('pelanggan.register.post') }}">
                            @csrf

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="register-name" class="form-label fw-bold">
                                    <i class="fas fa-user me-2"></i>Nama Lengkap
                                </label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="register-name"
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
                                <label for="register-email" class="form-label fw-bold">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="register-email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="Masukkan email"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="register-phone" class="form-label fw-bold">
                                    <i class="fas fa-phone me-2"></i>Nomor Telepon
                                </label>
                                <input type="tel"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="register-phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="Masukkan nomor telepon"
                                       required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="register-password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="register-password"
                                           name="password"
                                           placeholder="Masukkan password"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="register-password" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="register-password-confirm" class="form-label fw-bold">
                                    <i class="fas fa-lock me-2"></i>Konfirmasi Password
                                </label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="register-password-confirm"
                                           name="password_confirmation"
                                           placeholder="Konfirmasi password"
                                           required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="register-password-confirm" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Saya setuju dengan syarat dan ketentuan
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-auth btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Daftar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mt-3 text-center">
                    <button onclick="document.getElementById('waModal').style.display='flex'" style="background:#25D366;border:none;border-radius:50px;padding:0.4rem 1rem;color:white;font-weight:600;cursor:pointer;font-size:0.75rem;">
                        <i class="fab fa-whatsapp me-1"></i> Hubungi kami di WhatsApp
                    </button>
                </div>

                <!-- WA Modal -->
                <div id="waModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
                    <div style="background:white;border-radius:16px;padding:2rem;max-width:360px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                        <div style="font-size:3rem;margin-bottom:0.5rem;">💬</div>
                        <h5 style="font-weight:700;margin-bottom:0.3rem;">Pilih Nomor WhatsApp</h5>
                        <p style="color:#888;font-size:0.85rem;margin-bottom:1.5rem;">Pilih salah satu nomor untuk menghubungi kami</p>
                        <div style="display:flex;flex-direction:column;gap:0.75rem;">
                            <a href="https://wa.me/6289561985919" target="_blank" style="background:#25D366;color:white;padding:0.75rem 1rem;border-radius:50px;text-decoration:none;font-weight:600;display:flex;align-items:center;justify-content:center;gap:0.5rem;"><i class="fab fa-whatsapp"></i> 0895619859193</a>
                            <a href="https://wa.me/6282118959085" target="_blank" style="background:#25D366;color:white;padding:0.75rem 1rem;border-radius:50px;text-decoration:none;font-weight:600;display:flex;align-items:center;justify-content:center;gap:0.5rem;"><i class="fab fa-whatsapp"></i> 082118959085</a>
                            <a href="https://wa.me/6285659739659" target="_blank" style="background:#25D366;color:white;padding:0.75rem 1rem;border-radius:50px;text-decoration:none;font-weight:600;display:flex;align-items:center;justify-content:center;gap:0.5rem;"><i class="fab fa-whatsapp"></i> 085659739659</a>
                            <a href="https://wa.me/6281298226841" target="_blank" style="background:#25D366;color:white;padding:0.75rem 1rem;border-radius:50px;text-decoration:none;font-weight:600;display:flex;align-items:center;justify-content:center;gap:0.5rem;"><i class="fab fa-whatsapp"></i> 081298226841</a>
                        </div>
                        <button onclick="document.getElementById('waModal').style.display='none'" style="margin-top:1.25rem;background:none;border:1px solid #ddd;padding:0.5rem 2rem;border-radius:50px;cursor:pointer;color:#666;">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    </script>
</body>
</html>
