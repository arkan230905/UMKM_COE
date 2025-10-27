<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Platform UMKM</title>

<<<<<<< HEAD
@section('content')
<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">Laravel</div>
        <p>Selamat datang di web UMKM.</p>
    </div>
</div>
@endsection
=======
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body, html {
            height: 100%;
            overflow: hidden;
        }

        /* ===== VIDEO BACKGROUND ===== */
        .video-bg {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            object-fit: cover;
            z-index: -2;
            filter: brightness(0.6);
        }

        /* ===== OVERLAY ===== */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7));
            z-index: -1;
        }

        /* ===== MAIN CONTENT ===== */
        .content {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            animation: fadeIn 2s ease forwards;
        }

        h1 {
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 10px;
            animation: slideDown 1.5s ease;
        }

        p {
            font-size: 1.2rem;
            max-width: 700px;
            margin-bottom: 30px;
            color: #f1f1f1;
            animation: fadeIn 2.5s ease;
        }

        /* ===== BUTTON ===== */
        .btn-login {
            display: inline-block;
            padding: 14px 35px;
            background: #ffb703;
            color: #000;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 0 15px rgba(255, 183, 3, 0.6);
            transition: 0.4s ease;
            animation: bounceIn 3s ease;
        }

        .btn-login:hover {
            transform: translateY(-5px);
            background: #ffd166;
            box-shadow: 0 0 25px rgba(255, 209, 102, 0.9);
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from { transform: translateY(-40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes bounceIn {
            0% { transform: scale(0.8); opacity: 0; }
            60% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            h1 { font-size: 2.2rem; }
            p { font-size: 1rem; }
        }
    </style>
</head>
<body>

    <!-- Background Video -->
    <video autoplay muted loop class="video-bg">
        <source src="{{ asset('umkm.mp4') }}" type="video/mp4">
        Browser kamu tidak mendukung video tag.
    </video>

    <!-- Overlay -->
    <div class="overlay"></div>

    <!-- Content -->
    <div class="content">
        <h1>Selamat Datang di Platform UMKM</h1>
        <p>Mari kita dukung pertumbuhan Usaha Mikro, Kecil, dan Menengah melalui transformasi digital yang lebih cerdas dan efisien.</p>
        <a href="{{ route('login') }}" class="btn-login">Masuk ke Akun</a>
    </div>

</body>
</html>
>>>>>>> 73ecd34c0ff44e1b46e8fcae2de615861d360f74
