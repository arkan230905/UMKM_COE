<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - UMKM Management System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
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
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        /* Video Background */
        .video-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .gradient-bg {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow: hidden;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.4) 100%);
            z-index: 0;
        }

        .welcome-container {
            display: flex;
            max-width: 1200px;
            width: 100%;
            background: rgba(15, 23, 42, 0.85); /* Dark semi-transparent background */
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        .welcome-left {
            flex: 1;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: transparent;
            position: relative;
            z-index: 2;
            color: #e2e8f0;
        }

        .welcome-right {
            flex: 1;
            background: rgba(30, 41, 59, 0.7);
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
            text-align: center;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(5px);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            max-width: 180px;
            height: auto;
            margin: 0 auto 1.5rem;
            display: block;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logo:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .welcome-text {
            font-size: 1.1rem;
            color: #e2e8f0;
            margin-bottom: 2.5rem;
            line-height: 1.7;
            opacity: 0.9;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0.5rem 0;
            width: 100%;
            max-width: 250px;
        }

        .btn-primary {
            background: #3b82f6; /* Brighter blue */
            color: white;
            border: 2px solid #3b82f6;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 56, 202, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            transform: translateY(-2px);
        }

        .feature-list {
            margin: 2rem 0;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #e2e8f0;
            opacity: 0.9;
        }

        .feature-icon {
            width: 24px;
            height: 24px;
            margin-right: 1rem;
            color: #60a5fa; /* Lighter blue for better visibility */
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #9ca3af;
            font-size: 0.875rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
            margin: 0 1rem;
        }

        .testimonial {
            margin-top: 2rem;
            font-style: italic;
            color: #e5e7eb;
            position: relative;
            padding: 0 1.5rem;
        }

        .testimonial::before {
            content: '"';
            font-size: 4rem;
            position: absolute;
            left: -10px;
            top: -20px;
            color: rgba(255, 255, 255, 0.2);
            font-family: serif;
        }

        .testimonial-author {
            display: block;
            margin-top: 1rem;
            font-weight: 600;
            color: white;
            font-style: normal;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .welcome-container {
                flex-direction: column;
                max-width: 500px;
            }

            .welcome-left, .welcome-right {
                padding: 2.5rem;
            }

            .welcome-right {
                display: none;
            }
        }

        @media (max-width: 640px) {
            h1 {
                font-size: 2rem;
            }

            .welcome-text {
                font-size: 1rem;
            }

            .btn {
                padding: 0.7rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

    <div class="gradient-bg">
        <video autoplay muted loop playsinline class="video-bg">
            <source src="{{ asset('umkm.mp4') }}" type="video/mp4">
            Browser Anda tidak mendukung video.
        </video>
        <div class="overlay"></div>
        <div class="welcome-container animate-fade-in">
            <!-- Left Side - Welcome Content -->
            <div class="welcome-left">
                <div class="logo-container">
                    <img src="{{ asset('images/logo.png') }}" alt="UMKM Management Logo" class="logo">
                </div>
                <h1>Selamat Datang di UMKM Management</h1>
                <p class="welcome-text">Kelola bisnis UMKM Anda dengan lebih mudah dan efisien. Mulai perjalanan bisnis Anda bersama kami.</p>
                
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Manajemen inventaris yang mudah</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Laporan keuangan terintegrasi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Monitor penjualan real-time</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                    </a>
                    
                    <div class="divider">atau</div>
                    
                    <a href="{{ route('register') }}" class="btn btn-outline">
                        <i class="fas fa-user-plus mr-2"></i> Daftar Akun Baru
                    </a>
                </div>
            </div>

            <!-- Right Side - Image/Illustration -->
            <div class="welcome-right">
                <div>
                    <h2 class="text-2xl font-bold mb-4">Bergabung dengan Ratusan UMKM Lainnya</h2>
                    <p class="mb-6">Tingkatkan penjualan dan kelola bisnis Anda dengan lebih efisien menggunakan platform kami.</p>
                    
                    <div class="testimonial">
                        "Sistem ini telah membantu saya mengelola inventaris dan keuangan dengan lebih baik. Sangat direkomendasikan untuk UMKM!"
                        <span class="testimonial-author">- Ahmad, Pemilik Toko ABC</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Add any additional JavaScript here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Add any initialization code here
        });
    </script>
</body>
</html>
