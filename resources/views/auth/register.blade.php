<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Owner SIMCOST</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            padding-bottom: 50px; /* Space for fixed footer */
        }
        
        /* Hide scrollbar but keep functionality */
        body::-webkit-scrollbar {
            display: none;
        }
        
        body {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        .video-bg {
            position: fixed;
            top: -5%;
            left: 0;
            width: 100%;
            height: 110%;
            object-fit: cover;
            z-index: -2;
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

        .register-container {
            max-width: 380px;
            width: 100%;
            margin: 1% auto;
            padding: 0.8rem;
            background: rgba(245, 243, 239, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(222, 184, 135, 0.3);
            color: #3e2723;
        }

        .form-input {
            width: 100%;
            padding: 0.35rem 0.5rem;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(139, 69, 19, 0.2);
            border-radius: 8px;
            color: #3e2723;
            transition: all 0.3s;
            font-size: 0.65rem;
        }

        select.form-input {
            background-color: #ffffff;
            color: #000000;
        }

        select.form-input option {
            color: #000000;
            background-color: #ffffff;
        }

        .form-input:focus {
            outline: none;
            border-color: #8b6f47;
            box-shadow: 0 0 0 3px rgba(139, 111, 71, 0.2);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-input::placeholder {
            color: #8b6f47;
        }

        .btn-register {
            background: linear-gradient(135deg, #8b6f47 0%, #6d5637 100%);
            color: white;
            padding: 0.35rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(139, 111, 71, 0.3);
            font-size: 0.7rem;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #a08060 0%, #8b6f47 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 111, 71, 0.4);
            transform: translateY(-2px);
        }

        .error-message {
            color: #f87171;
            font-size: 0.55rem;
            margin-top: 0.1rem;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            z-index: 10;
        }

        .form-group {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.6rem;
            font-weight: 500;
            color: #e2e8f0;
        }

        .terms-label {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.6rem;
            color: #94a3b8;
            margin-top: 0.6rem;
        }

        .terms-label a {
            color: #60a5fa;
            text-decoration: none;
        }

        .terms-label a:hover {
            text-decoration: underline;
        }

        .login-link {
            text-align: center;
            margin-top: 0.6rem;
            color: #94a3b8;
            font-size: 0.6rem;
        }

        .login-link a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .register-container {
                margin: 5%;
                padding: 1.5rem;
                max-width: 95vw;
            }
        }

        /* Logo Styling for Register Page */
        .logo-section {
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .logo-main-reg {
            width: 45px;
            height: 45px;
            object-fit: contain;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.1);
            padding: 12px;
            backdrop-filter: blur(8px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .logo-main-reg:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .logo-partners-reg {
            display: flex;
            gap: 0.3rem;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo-partner-reg {
            width: 28px;
            height: 28px;
            object-fit: contain;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            animation: fadeIn 1s ease-out;
        }

        .logo-partner-reg:hover {
            transform: translateY(-2px) scale(1.1);
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
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
            width: 50px;
            height: 40px;
        }

        .logo-external-partner {
            width: 60px;
            height: 60px;
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
        }

        /* Animations for Register Page */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(25px);
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

        /* Responsive adjustments for register */
        @media (max-width: 640px) {
            .register-container {
                margin: 5%;
                padding: 1rem;
            }
            
            .logo-main-reg {
                width: 50px;
                height: 50px;
            }
            
            .logo-partner-reg {
                width: 30px;
                height: 30px;
            }
            
            .logo-partners-reg {
                gap: 0.4rem;
            }

            /* Adjust body padding for mobile */
            body, html {
                padding-bottom: 70px; /* More space for mobile footer */
            }
        }
    </style>
</head>
<body>
    <!-- Video Background -->
    <video autoplay muted loop playsinline preload="auto" class="video-bg">
        <source src="{{ asset('umkm.mp4') }}" type="video/mp4">
        Browser Anda tidak mendukung video.
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

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="register-container w-full">
            <div class="text-center mb-3">
                <h1 class="text-xs font-extrabold text-amber-950 mt-0">Daftar Sebagai Owner</h1>
                <p class="text-amber-800 mt-0 text-xs" style="font-size: 0.55rem;">Buat akun owner untuk mengelola bisnis Anda</p>
                <div class="mt-0.5 p-0.5 bg-amber-100 rounded-lg border border-amber-300">
                    <p class="text-xs text-amber-800" style="font-size: 0.5rem; line-height: 1.2;">
                        <strong>Catatan:</strong> Hanya owner yang dapat mendaftar di halaman ini. 
                        Admin, pegawai, dan kasir dapat login langsung di halaman login dengan email dan kode perusahaan.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-900/50 border border-red-500 text-red-100 px-2 py-1.5 rounded mb-3 text-xs">
                    <div class="font-bold text-xs">Perhatian!</div>
                    <ul class="list-disc list-inside mt-0.5">
                        @foreach ($errors->all() as $error)
                            <li class="text-xs">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Hidden field untuk role owner -->
                <input type="hidden" name="role" value="owner">

                <div id="after-role-section">

                <div id="common-fields-section">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="form-group">
                        <label class="form-label" for="name" style="color: #8b4513; font-size: 0.6rem;">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="form-input @error('name') border-red-500 @enderror"
                            placeholder="Nama lengkap"
                        >
                        @error('name')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username" style="color: #8b4513; font-size: 0.6rem;">Username <span class="text-red-500">*</span></label>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            class="form-input @error('username') border-red-500 @enderror"
                            placeholder="Username unik"
                        >
                        @error('username')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email" style="color: #8b4513; font-size: 0.6rem;">Email <span class="text-red-500">*</span></label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="form-input @error('email') border-red-500 @enderror"
                        placeholder="contoh@email.com"
                    >
                    @error('email')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                </div> <!-- end common-fields-section -->

                <div class="form-group">
                    <label class="form-label" for="phone" style="color: #8b4513; font-size: 0.6rem;">Nomor Telepon <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="form-input @error('phone') border-red-500 @enderror"
                            required
                            placeholder="0812-3456-7890"
                        >
                        <i class="fas fa-phone absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    @error('phone')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div id="company-owner-section" class="space-y-1.5" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="company_nama" style="color: #8b4513; font-size: 0.6rem;">Nama Perusahaan <span class="text-red-500">*</span></label>
                        <input id="company_nama" type="text" name="company_nama" value="{{ old('company_nama') }}" class="form-input @error('company_nama') border-red-500 @enderror" placeholder="Nama perusahaan">
                        @error('company_nama')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_alamat" style="color: #8b4513; font-size: 0.6rem;">Alamat Perusahaan <span class="text-red-500">*</span></label>
                        <textarea id="company_alamat" name="company_alamat" rows="1" class="form-input @error('company_alamat') border-red-500 @enderror" placeholder="Alamat perusahaan">{{ old('company_alamat') }}</textarea>
                        @error('company_alamat')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_email" style="color: #8b4513; font-size: 0.6rem;">Email Perusahaan <span class="text-red-500">*</span></label>
                        <input id="company_email" type="email" name="company_email" value="{{ old('company_email') }}" class="form-input @error('company_email') border-red-500 @enderror" placeholder="email@perusahaan.com">
                        @error('company_email')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company_telepon" style="color: #8b4513; font-size: 0.6rem;">Telepon Perusahaan <span class="text-red-500">*</span></label>
                        <input id="company_telepon" type="text" name="company_telepon" value="{{ old('company_telepon') }}" class="form-input @error('company_telepon') border-red-500 @enderror" placeholder="Nomor telepon perusahaan">
                        @error('company_telepon')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="kode-perusahaan-section" class="form-group" style="display: none;">
                    <label class="form-label" for="kode_perusahaan" style="font-size: 0.6rem;">Kode Perusahaan <span class="text-amber-900">*</span></label>
                    <input
                        id="kode_perusahaan"
                        type="text"
                        name="kode_perusahaan"
                        value="{{ old('kode_perusahaan') }}"
                        class="form-input @error('kode_perusahaan') border-red-500 @enderror"
                        placeholder="Masukkan kode perusahaan dari owner"
                    >
                    @error('kode_perusahaan')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="form-group">
                        <label class="form-label" for="password" style="color: #8b4513; font-size: 0.6rem;">Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                class="form-input @error('password') border-red-500 @enderror pr-10"
                                placeholder="Minimal 8 karakter"
                            >
                            <i class="password-toggle fas fa-eye-slash absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePassword('password')"></i>
                        </div>
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation" style="color: #8b4513; font-size: 0.6rem;">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                class="form-input pr-10"
                                placeholder="Ketik ulang password"
                            >
                            <i class="password-toggle fas fa-eye-slash absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePassword('password_confirmation')"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="terms-label">
                        <input
                            type="checkbox"
                            name="terms"
                            class="rounded border-gray-400 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mt-1"
                            required
                        >
                        <span style="color: #8b4513; font-size: 0.6rem;">
                            Saya setuju dengan <a href="#" class="hover:underline">Syarat & Ketentuan</a> dan <a href="#" class="hover:underline">Kebijakan Privasi</a> <span class="text-red-500">*</span>
                        </span>
                    </label>
                    @error('terms')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-register mt-3" style="font-size: 0.7rem; padding: 0.35rem;">
                    Daftar Sekarang
                </button>

                <div class="login-link" style="font-size: 0.6rem;">
                    Sudah punya akun? <a href="{{ route('login') }}" class="hover:underline">Masuk disini</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        function handleRoleChange() {
            const ownerSection = document.getElementById('company-owner-section');
            const kodeSection = document.getElementById('kode-perusahaan-section');
            const commonSection = document.getElementById('common-fields-section');
            const afterRoleSection = document.getElementById('after-role-section');

            if (!ownerSection || !kodeSection || !commonSection || !afterRoleSection) return;

            // Selalu tampilkan form karena hanya owner yang bisa register
            afterRoleSection.style.display = 'block';
            commonSection.style.display = 'block';
            
            // Selalu tampilkan owner section
            ownerSection.style.display = 'block';
            // Enable fields
            document.getElementById('company_nama').disabled = false;
            document.getElementById('company_alamat').disabled = false;
            document.getElementById('company_email').disabled = false;
            document.getElementById('company_telepon').disabled = false;
            // Restore name attribute and require company fields
            document.getElementById('company_nama').setAttribute('name', 'company_nama');
            document.getElementById('company_alamat').setAttribute('name', 'company_alamat');
            document.getElementById('company_email').setAttribute('name', 'company_email');
            document.getElementById('company_telepon').setAttribute('name', 'company_telepon');
            document.getElementById('company_nama').required = true;
            document.getElementById('company_alamat').required = true;
            document.getElementById('company_email').required = true;
            document.getElementById('company_telepon').required = true;
            
            // Sembunyikan kode perusahaan section - tidak diperlukan untuk owner
            kodeSection.style.display = 'none';
            document.getElementById('kode_perusahaan').disabled = true;
            document.getElementById('kode_perusahaan').removeAttribute('name');
            document.getElementById('kode_perusahaan').required = false;
            document.getElementById('kode_perusahaan').value = '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Auto-trigger form untuk owner
            handleRoleChange();
        });
    </script>

    <!-- Developer Credits Footer -->
    <div class="developer-credits" style="
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, rgba(62, 39, 35, 0.95) 0%, rgba(93, 64, 55, 0.95) 100%);
        backdrop-filter: blur(10px);
        padding: 0.35rem 0;
        border-top: 1px solid rgba(212, 165, 116, 0.3);
        z-index: 10;
        animation: slideUp 0.8s ease-out;
    ">
        <div class="credits-container" style="
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 0.5rem;
            text-align: center;
        ">
            <div class="credits-title" style="
                color: #d4a574;
                font-size: 0.5rem;
                font-weight: 600;
                margin-bottom: 0.25rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                opacity: 0.9;
            ">
                Developed By
            </div>
            <div class="credits-list" style="
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
                align-items: center;
            ">
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.5rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Dr. Nelsi Wisna, S.E., M.Si.
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.5rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Chindi Lestari
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.5rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Ghitha Nadhirah Yasin
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.5rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Muhammad Arkan Abiyyu
                </div>
                <div class="developer-item" style="
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 0.5rem;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    cursor: default;
                ">
                    Nayla Dzakira Yusuf
                </div>
            </div>
            <div class="credits-divider" style="
                width: 20px;
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(212, 165, 116, 0.5), transparent);
                margin: 0.25rem auto;
            "></div>
            <div class="credits-version" style="
                color: rgba(255, 255, 255, 0.6);
                font-size: 0.45rem;
                font-style: italic;
            ">
                © 2026 SIMACOST - Sistem Manufaktur Process Costing
            </div>
        </div>
    </div>
</body>
</html>
