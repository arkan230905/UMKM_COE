<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | UMKM Digital</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Video background */
        video#bg-video {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            filter: brightness(55%);
        }

        /* Overlay & form */
        .reset-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .reset-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            width: 480px;
            max-width: 90vw;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
            color: #fff;
        }

        .form-label {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            border-radius: 8px;
            padding: 0.8rem 1rem;
            transition: all 0.3s;
            font-size: 0.875rem;
            height: 48px;
            line-height: 1.5;
            box-sizing: border-box;
            display: block;
            width: 100%;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
            color: #fff;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
            opacity: 1;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.875rem;
            height: 48px;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

        .text-danger {
            color: #fca5a5;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .mb-3 {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .text-center {
            text-align: center;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-gray-600 {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Remove floating animation */
        .reset-box {
            animation: none;
        }

        /* Back to login link */
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-to-login a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .back-to-login a:hover {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Background video -->
    <video autoplay muted loop id="bg-video">
        <source src="{{ asset('umkm.mp4') }}" type="video/mp4">
    </video>

    <div class="reset-container px-3">
        <div class="reset-box">
            <h1>Reset Password</h1>

            @if (session('status'))
                <div class="alert alert-success py-2 mb-3">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                
                <!-- Debug info -->
                <input type="hidden" name="debug_info" value="Form submitted at {{ now()->format('Y-m-d H:i:s') }}">

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="submit">Send Password Reset Link</button>
                </div>
            </form>

            <div class="back-to-login">
                <a href="{{ route('login') }}">Kembali ke Login</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Password reset page loaded');
            
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('=== PASSWORD RESET FORM SUBMIT ===');
                    console.log('Email:', document.getElementById('email').value);
                    console.log('CSRF Token:', document.querySelector('input[name="_token"]').value);
                    console.log('Debug Info:', document.querySelector('input[name="debug_info"]').value);
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = 'Mengirim...';
                    }
                });
            }
        });
    </script>
    
    <!-- Bootstrap JS dari CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
