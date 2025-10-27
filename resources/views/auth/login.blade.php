<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UMKM Digital</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            width: 380px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            color: #fff;
        }

        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.8rem;
        }

        input {
            background: rgba(255, 255, 255, 0.8);
            border: none;
            color: #333;
        }

        button {
            background-color: #2563eb;
            border: none;
            width: 100%;
            padding: 0.8rem;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #1e40af;
        }

        a {
            color: #c7d2fe;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Background video -->
    <video autoplay muted loop id="bg-video">
        <source src="{{ asset('umkm.mp4') }}" type="video/mp4">
    </video>

    <div class="login-container">
        <div class="login-box">
            <h1>Login UMKM</h1>

            @if (session('status'))
                <div style="color: #a7f3d0; margin-bottom: 1rem;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full p-2 rounded-md mt-1">
                    @error('email')
                        <p style="color: #f87171;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required class="w-full p-2 rounded-md mt-1">
                    @error('password')
                        <p style="color: #f87171;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1rem;">
                    <label>
                        <input type="checkbox" name="remember"> Remember Me
                    </label>
                </div>

                <button type="submit">LOGIN</button>

                <div style="text-align:center; margin-top:1rem;">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
