<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Ke SIMCOST</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Hide scrollbar but keep functionality - COMPREHENSIVE */
        html::-webkit-scrollbar {
            display: none;
        }
        
        body::-webkit-scrollbar {
            display: none;
        }
        
        *::-webkit-scrollbar {
            display: none;
        }
        
        html {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
            overflow: hidden;
        }
        
        body {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
            overflow: hidden;
            height: 100vh;
            position: fixed;
            width: 100%;
            padding-bottom: 120px; /* Space for fixed footer */
        }
        
        /* Hide scrollbars on all elements */
        * {
            -ms-overflow-style: none !important;  /* IE and Edge */
            scrollbar-width: none !important;  /* Firefox */
        }
        
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        
        /* IMPORTANT: Override Bootstrap completely */
        #loginButton, #loginButton:hover, #loginButton:focus, #loginButton:active,
        #loginButton.focus, #loginButton:focus:active, #loginButton.active:focus,
        #loginButton:not(:disabled):not(.disabled):active,
        #loginButton:not(:disabled):not(.disabled).active {
            background: linear-gradient(135deg, #d4a574 0%, #c19a6b 100%) !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            color: #fff !important;
        }

        #loginButton:hover {
            background: linear-gradient(135deg, #e6b885 0%, #d4a574 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        /* Remove any possible focus rings or borders */
        #loginButton:focus-visible {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.3) !important;
        }

        /* Video background */
        video#bg-video {
            position: fixed;
            top: -5%;
            left: 0;
            width: 100%;
            height: 110%;
            z-index: -2;
            object-fit: cover;
            filter: contrast(1.1) brightness(1.05) saturate(1.1);
            transform: scale(1.02);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.1) 100%);
            z-index: -1;
        }

        /* Overlay & form */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin-top: 30px;
        }

        .login-box {
            background: rgba(245, 243, 239, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 900px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(222, 184, 135, 0.3);
            color: #3e2723;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            color: #fff;
        }

        .welcome-title {
            text-align: center;
            margin-bottom: 1rem;
            font-weight: 700;
            font-size: 2.2rem;
            color: #3e2723;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.8s ease-out;
        }

        .form-label {
            font-weight: 600;
            color: #3e2723;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.9) !important;
            border: 2px solid rgba(139, 69, 19, 0.3) !important;
            color: #3e2723 !important;
            border-radius: 8px;
            padding: 1.2rem 1.2rem !important;
            transition: all 0.3s;
            font-size: 1.1rem !important;
            height: 60px !important;
            line-height: 1.4 !important;
            box-sizing: border-box;
            display: block;
            width: 100%;
            font-weight: 500 !important;
        }

        /* Khusus untuk dropdown Masuk Ke Halaman */
        #login_role {
            background: rgba(255, 255, 255, 0.9) !important;
            border: 2px solid rgba(139, 69, 19, 0.3) !important;
            color: #3e2723 !important;
            font-size: 1.1rem !important;
            font-weight: 500 !important;
            height: 60px !important;
            padding: 1.2rem 1.2rem !important;
            line-height: 1.4 !important;
        }

        #login_role:focus {
            background: rgba(255, 255, 255, 1) !important;
            border-color: #8b6f47 !important;
            box-shadow: 0 0 0 4px rgba(139, 111, 71, 0.3) !important;
        }

        /* Dropdown options styling */
        #login_role option {
            background: #ffffff !important;
            color: #3e2723 !important;
            font-size: 1.2rem !important;
            font-weight: 500 !important;
            padding: 1rem !important;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.9) !important;
            border-color: #8b6f47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.2);
            color: #3e2723 !important;
            outline: none;
        }

        .form-control::placeholder {
            color: #8b6f47 !important;
            opacity: 1;
        }

        /* Dropdown options styling */
        .form-select option {
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0.75rem;
            border: none;
        }

        .form-select option:hover {
            background: #ffffff !important;
            color: #000000 !important;
        }

        /* Fix dropdown background */
        .form-select {
            background: rgba(255, 255, 255, 0.7) !important;
            color: #3e2723 !important;
            height: 48px !important;
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 0.9) !important;
            color: #3e2723 !important;
            height: 48px !important;
        }

        /* Better form spacing */
        .mb-3 {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
        }

        /* Remember me checkbox styling */
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            margin: 0;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #8b6f47;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .form-check-input:checked {
            background: #8b6f47;
            border-color: #8b6f47;
        }

        .form-check-input:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .form-check-input:hover {
            border-color: #6d5637;
            box-shadow: 0 0 0 2px rgba(139, 111, 71, 0.2);
        }

        .form-check-input:focus {
            outline: none;
            border-color: #6d5637;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.3);
        }

        .form-check-label {
            margin: 0;
            cursor: pointer;
            color: #3e2723;
            font-size: 1.1rem;
        }

        button, .btn {
            background: linear-gradient(135deg, #d4a574 0%, #c19a6b 100%) !important;
            border: none !important;
            width: 100%;
            padding: 1.2rem 1.2rem;
            border-radius: 8px;
            color: #fff !important;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.2rem;
            height: 60px;
            line-height: 1.4;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            outline: none !important;
            box-shadow: none !important;
        }

        button:hover, .btn:hover {
            background: linear-gradient(135deg, #e6b885 0%, #d4a574 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            border: none !important;
            outline: none !important;
        }

        button:focus, .btn:focus {
            background: linear-gradient(135deg, #d4a574 0%, #c19a6b 100%) !important;
            border: none !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.3) !important;
        }

        button:active, .btn:active {
            background: linear-gradient(135deg, #c19a6b 0%, #b8935f 100%) !important;
            transform: translateY(0);
            border: none !important;
            outline: none !important;
        }

        /* Remove all Bootstrap button borders and outlines */
        .btn-primary, .btn-primary:hover, .btn-primary:focus, .btn-primary:active,
        .btn-primary.focus, .btn-primary:focus:active, .btn-primary.active:focus,
        .btn-primary:not(:disabled):not(.disabled):active,
        .btn-primary:not(:disabled):not(.disabled).active {
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            background: linear-gradient(135deg, #d4a574 0%, #c19a6b 100%) !important;
        }

        /* Remove focus ring completely */
        button:focus-visible, .btn:focus-visible {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.3) !important;
        }

        /* Override Bootstrap focus states */
        .btn:focus, .btn.focus {
            outline: 0 !important;
            box-shadow: none !important;
        }

        /* Override Bootstrap active states */
        .btn:not(:disabled):not(.disabled):active, .btn:not(:disabled):not(.disabled).active {
            box-shadow: none !important;
        }

        a {
            color: #3e2723;
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        a:hover {
            color: #5d4037;
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            border: none;
            font-size: 0.875rem;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.9);
            color: white;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.9);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        .text-danger {
            color: #fca5a5;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Remove floating animation */
        .login-box {
            animation: none;
        }

        /* Input group styling */
        .mb-3 {
            margin-bottom: 1.25rem;
        }

        /* Remember me checkbox */
        .form-check {
            padding-left: 0;
            margin-bottom: 1rem;
        }

        .form-check-input {
            margin-top: 0.25rem;
        }

        /* Forgot password link */
        .text-center.mt-3 {
            margin-top: 1rem !important;
        }

        /* External Logo Section */
        .logo-external {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideInRight 0.8s ease-out;
        }

        .logo-external-main {
            width: 190px;
            height: 150px;
        }

        .logo-external-partner {
            width: 220px;
            height: 220px;
            object-fit: contain;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: block;
            background: transparent;
        }

        .logo-external-main:hover {
            transform: translateY(-3px) scale(1.05);
        }

        .logo-external-partners {
            display: flex;
            gap: 1rem;
        }

        .logo-external-partner:hover {
            transform: translateY(-2px) scale(1.1);
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .logo-external {
                top: 20px;
                right: 20px;
            }

            .logo-external-main {
                width: 170px;
                height: 130px;
            }

            .logo-external-partner {
                width: 180px;
                height: 180px;
            }

            .logo-external-partners {
                gap: 0.8rem;
            }
        }

        @media (max-width: 640px) {
            .login-box {
                max-width: 95vw;
                padding: 1.5rem;
            }
            
            .logo-external {
                top: 15px;
                right: 15px;
            }

            .logo-external-main {
                width: 120px;
                height: 95px;
            }

            .logo-external-partner {
                width: 130px;
                height: 130px;
            }

            .logo-external-partners {
                gap: 0.6rem;
            }

            /* Adjust body padding for mobile */
            body {
                padding-bottom: 140px; /* More space for mobile footer */
            }
        }

        .subtitle {
            color: #6d4c41;
            font-size: 1.1rem;
            margin: 0.3rem auto;
            font-weight: 500;
            letter-spacing: 0.5px;
            animation: fadeIn 1.2s ease-out;
            max-width: 85%;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Developer Credits Hover Effects */
        .developer-item:hover {
            color: #d4a574 !important;
            transform: translateY(-2px);
            text-shadow: 0 2px 8px rgba(212, 165, 116, 0.3);
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .logo-main {
                width: 100px;
                height: 100px;
            }
            
            .logo-partner {
                width: 50px;
                height: 50px;
            }
            
            .logo-partners {
                gap: 0.8rem;
            }
        }
    </style>
</head>
<body>

    <!-- Background video -->
    <video autoplay muted loop playsinline preload="auto" id="bg-video">
        <source src="{{ asset('umkm.mp4') }}" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <!-- Logo Section Outside Container -->
    <div class="logo-external">
        <img src="{{ asset('images/logo.png') }}" alt="UMKM Logo" class="logo-external-main">
        <div class="logo-external-partners">
            <img src="{{ asset('images/logo_telkom.png') }}" alt="Telkom Logo" class="logo-external-partner" title="Supported by Telkom">
            <img src="{{ asset('images/logo_eadt.png') }}" alt="EADT Logo" class="logo-external-partner" title="Powered by EADT">
        </div>
    </div>

    <div class="login-container px-3">
        <div class="login-box">
            <!-- Welcome Section -->
            <div class="text-center mb-4">
                <h1 class="welcome-title">Selamat Datang</h1>
                <p class="subtitle">Sistem Manajemen Manufaktur Proces Costing Terpadu - Kelola bisnis Anda dengan mudah dan efisien</p>
            </div>
            
            @if (session('status'))
                <div class="alert alert-success py-2 mb-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <!-- Debug CSRF Token -->
                <input type="hidden" name="debug_token" value="{{ csrf_token() }}">
                
            @if ($errors->any() && old('_token'))
                <div class="alert alert-danger mb-3">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                <div class="mb-3">
                    <label for="login_role" class="form-label">Masuk Ke Halaman <span style="color: red;">*</span></label>
                    <select id="login_role" name="login_role" class="form-select" required>
                        <option value="" selected disabled>Pilih halaman</option>
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
                        <option value="pegawai">Pegawai</option>
                        <option value="pegawai_pembelian">Pegawai Gudang</option>
                        <option value="kasir">Kasir</option>
                    </select>
                    @error('login_role')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div id="login-fields" style="display: none;">
                    <!-- Field Kode Perusahaan (untuk admin, pegawai, kasir) -->
                    <div id="kode_perusahaan_field" class="mb-3" style="display: none;">
                        <label for="kode_perusahaan" class="form-label">Kode Perusahaan <span style="color: red;">*</span></label>
                        <input id="kode_perusahaan" type="text" name="kode_perusahaan" value="{{ old('kode_perusahaan') }}" class="form-control" placeholder="Masukkan kode perusahaan">
                        @error('kode_perusahaan')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field Email (untuk semua role) -->
                    <div id="email_field" class="mb-3" style="display: none;">
                        <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Masukkan email Anda">
                        @error('email')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field Password (hanya untuk owner) -->
                    <div id="password_field" class="mb-3" style="display: none;">
                        <label for="password" class="form-label">Password <span style="color: red;">*</span></label>
                        <input id="password" type="password" name="password" class="form-control" placeholder="Masukkan password">
                        @error('password')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check" style="display: none;">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="loginButton">LOGIN</button>

                    <div class="text-center mt-3">
                        <span style="color: #3e2723;">Belum punya akun?</span>
                        <a href="{{ route('register') }}" style="color: #3e2723; font-weight: bold;">Daftar sekarang</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Developer Credits Footer -->
    <div class="developer-credits" style="
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, rgba(62, 39, 35, 0.95) 0%, rgba(93, 64, 55, 0.95) 100%);
        backdrop-filter: blur(10px);
        padding: 1.5rem 0;
        border-top: 1px solid rgba(212, 165, 116, 0.3);
        z-index: 10;
        animation: slideUp 0.8s ease-out;
    ">
        <div class="credits-container" style="
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            text-align: center;
        ">
            <div class="credits-title" style="
                color: #d4a574;
                font-size: 0.9rem;
                font-weight: 600;
                margin-bottom: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 2px;
                opacity: 0.9;
            ">
                Developed By
            </div>
            <div class="credits-list" style="
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 2rem;
                align-items: center;
            ">
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.95rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Dr. Nelsi Wisna, S.E., M.Si.
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.95rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Chindi Lestari
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.95rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Ghitha Nadhirah Yasin
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.95rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Muhammad Arkan Abiyyu
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.95rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Nayla Dzakira Yusuf
                </div>
            </div>
            <div class="credits-divider" style="
                width: 60px;
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(212, 165, 116, 0.5), transparent);
                margin: 1rem auto;
            "></div>
            <div class="credits-version" style="
                color: rgba(255, 255, 255, 0.6);
                font-size: 0.8rem;
                font-style: italic;
            ">
                © 2026 SIMACOST - Sistem Manufaktur Process Costing
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Login page loaded');
            
            const loginRoleSelect = document.getElementById('login_role');
            
            if (!loginRoleSelect) {
                console.error('Login role select not found');
                return;
            }
            
            loginRoleSelect.addEventListener('change', function() {
                console.log('Role changed to:', this.value);
                
                const role = this.value;
                const loginFields = document.getElementById('login-fields');
                const kodePerusahaanField = document.getElementById('kode_perusahaan_field');
                const emailField = document.getElementById('email_field');
                const passwordField = document.getElementById('password_field');
                const rememberMeField = document.querySelector('.form-check');
                
                // Pastikan semua elemen ada
                if (!loginFields || !kodePerusahaanField || !emailField || !passwordField || !rememberMeField) {
                    console.error('Some form elements not found');
                    return;
                }
                
                // Reset semua field
                loginFields.style.display = 'none';
                kodePerusahaanField.style.display = 'none';
                emailField.style.display = 'none';
                passwordField.style.display = 'none';
                rememberMeField.style.display = 'none';
                
                // Reset required attributes
                const kodePerusahaanInput = document.getElementById('kode_perusahaan');
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                // Remove all required first
                if (kodePerusahaanInput) kodePerusahaanInput.removeAttribute('required');
                if (emailInput) emailInput.removeAttribute('required');
                if (passwordInput) passwordInput.removeAttribute('required');
                
                // Tampilkan field sesuai role
                if (role !== '') {
                    loginFields.style.display = 'block';
                    emailField.style.display = 'block';
                    
                    if (emailInput) {
                        emailInput.setAttribute('required', 'required');
                    }

                    // Role owner: email + password
                    if (role === 'owner') {
                        passwordField.style.display = 'block';
                        rememberMeField.style.display = 'flex';
                        if (passwordInput) {
                            passwordInput.setAttribute('required', 'required');
                        }
                    }
                    // Role lainnya: kode perusahaan + email
                    else if (role === 'admin' || role === 'pegawai' || role === 'pegawai_pembelian' || role === 'kasir') {
                        kodePerusahaanField.style.display = 'block';
                        if (kodePerusahaanInput) {
                            kodePerusahaanInput.setAttribute('required', 'required');
                        }
                    }

                    // Focus ke field pertama yang relevan
                    if (role === 'owner') {
                        if (emailInput) {
                            setTimeout(() => emailInput.focus(), 100);
                        }
                    } else {
                        if (kodePerusahaanInput) {
                            setTimeout(() => kodePerusahaanInput.focus(), 100);
                        }
                    }
                }
            });

            // Auto-select role if there's an error
            @if (old('login_role'))
                const oldRole = '{{ old('login_role') }}';
                if (loginRoleSelect) {
                    loginRoleSelect.value = oldRole;
                    loginRoleSelect.dispatchEvent(new Event('change'));
                }
            @endif
            
            // Debug form submission - JANGAN PREVENT DEFAULT
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                console.log('Login form found, attaching submit handler');
                
                loginForm.addEventListener('submit', function(e) {
                    console.log('=== FORM SUBMIT EVENT TRIGGERED ===');
                    
                    // Validasi basic
                    const role = document.getElementById('login_role').value;
                    console.log('Selected role:', role);
                    
                    if (!role) {
                        e.preventDefault();
                        alert('Silakan pilih role terlebih dahulu');
                        console.log('Form submission prevented: no role selected');
                        return false;
                    }

                    // Validasi email untuk semua role
                    const email = document.getElementById('email').value;
                    if (!email) {
                        e.preventDefault();
                        alert('Email wajib diisi');
                        console.log('Form submission prevented: no email');
                        return false;
                    }

                    // Validasi kode perusahaan untuk role selain owner
                    if (['admin', 'pegawai', 'pegawai_pembelian', 'kasir'].includes(role)) {
                        const kodePerusahaan = document.getElementById('kode_perusahaan').value;
                        if (!kodePerusahaan) {
                            e.preventDefault();
                            alert('Kode perusahaan wajib diisi');
                            console.log('Form submission prevented: no kode perusahaan');
                            return false;
                        }
                    }

                    // Validasi password untuk owner
                    if (['owner'].includes(role)) {
                        const password = document.getElementById('password');
                        if (!password || !password.value || password.value.trim() === '') {
                            e.preventDefault();
                            alert('Password wajib diisi');
                            console.log('Form submission prevented: no password');
                            if (password) password.focus();
                            return false;
                        }
                    }

                    // Log form data
                    const formData = new FormData(loginForm);
                    console.log('Form data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`  ${key}: ${value}`);
                    }
                    
                    console.log('Form validation passed, allowing submission...');
                    // JANGAN e.preventDefault() di sini - biarkan form submit secara normal
                    return true;
                });
            } else {
                console.error('Login form not found!');
            }
        });
    </script>
    
    <!-- Bootstrap JS dari CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
