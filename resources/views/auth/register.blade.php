<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - UMKM Management</title>
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
        }

        .video-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.4) 100%);
            z-index: -1;
        }

        .register-container {
            max-width: 500px;
            margin: 2% auto;
            padding: 2.5rem;
            background: rgba(15, 23, 42, 0.9);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #e2e8f0;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .btn-register {
            background: #3b82f6;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
            border: none;
            cursor: pointer;
        }

        .btn-register:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .error-message {
            color: #f87171;
            font-size: 0.875rem;
            margin-top: 0.25rem;
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
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #e2e8f0;
        }

        .terms-label {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #94a3b8;
            margin-top: 1.5rem;
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
            margin-top: 1.5rem;
            color: #94a3b8;
            font-size: 0.875rem;
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
            }
        }
    </style>
</head>
<body>
    <!-- Video Background -->
    <video autoplay muted loop playsinline class="video-bg">
        <source src="{{ asset('umkm.mp4') }}" type="video/mp4">
        Browser Anda tidak mendukung video.
    </video>
    <div class="overlay"></div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="register-container w-full">
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="UMKM Logo" class="h-16 mx-auto mb-4">
                <h1 class="text-2xl font-bold text-white">Daftar Akun Baru</h1>
                <p class="text-gray-300 mt-2">Buat akun untuk memulai mengelola bisnis UMKM Anda</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-900/50 border border-red-500 text-red-100 px-4 py-3 rounded mb-6">
                    <div class="font-bold">Perhatian!</div>
                    <ul class="list-disc list-inside mt-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Lengkap <span class="text-red-400">*</span></label>
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
                        <label class="form-label" for="username">Username <span class="text-red-400">*</span></label>
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
                    <label class="form-label" for="email">Email <span class="text-red-400">*</span></label>
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

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon</label>
                    <div class="relative">
                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="form-input @error('phone') border-red-500 @enderror"
                            placeholder="0812-3456-7890"
                        >
                        <i class="fas fa-phone absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    @error('phone')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span class="text-red-400">*</span></label>
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
                        <label class="form-label" for="password_confirmation">Konfirmasi Password <span class="text-red-400">*</span></label>
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
                        <span>
                            Saya setuju dengan <a href="#" class="hover:underline">Syarat & Ketentuan</a> dan <a href="#" class="hover:underline">Kebijakan Privasi</a> <span class="text-red-400">*</span>
                        </span>
                    </label>
                    @error('terms')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-register mt-6">
                    Daftar Sekarang
                </button>

                <div class="login-link">
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
    </script>
</body>
</html>
