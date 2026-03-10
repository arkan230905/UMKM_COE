<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | UMKM Digital</title>
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
            display: block;
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

        .btn {
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

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .alert {
            border-radius: 8px;
            border: none;
            font-size: 0.875rem;
            padding: 1rem;
            margin-bottom: 1rem;
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

        .invalid-feedback {
            color: #fca5a5;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .mb-3 {
            margin-bottom: 1.25rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .text-center {
            text-align: center;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

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

        /* Remove floating animation */
        .reset-box {
            animation: none;
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
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Error:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                
                <!-- Hidden token field -->
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password-confirm" class="form-label">Confirm Password</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>

                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <button type="submit" class="btn">Reset Password</button>
                    </div>
                </div>
            </form>

            <div class="back-to-login">
                <a href="{{ route('login') }}">Kembali ke Login</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Password reset page loaded with video background');
            
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('=== PASSWORD RESET FORM SUBMIT ===');
                    console.log('Email:', document.getElementById('email').value);
                    console.log('Token:', document.querySelector('input[name="token"]').value);
                    
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
