<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem ERP Manufaktur - Solusi Keuangan Terpadu</title>
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
            margin-bottom: 2.5rem;
            position: relative;
        }

        .logo {
            max-width: 160px;
            height: 160px;
            margin: 0 auto 1.5rem;
            display: block;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.25);
            transition: all 0.4s ease;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);
            padding: 20px;
            border: 3px solid rgba(59, 130, 246, 0.3);
            object-fit: contain;
        }

        .logo:hover {
            transform: translateY(-3px) rotate(5deg);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.35);
            border-color: rgba(59, 130, 246, 0.5);
        }

        .logo-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 0.9; transform: translate(-50%, -50%) scale(1.15); }
        }

        h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
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
            margin-bottom: 1.2rem;
            color: #e2e8f0;
            opacity: 0.9;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            opacity: 1;
            transform: translateX(5px);
        }

        .feature-icon {
            width: 28px;
            height: 28px;
            margin-right: 1.2rem;
            color: #60a5fa;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 50%;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
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
            <source src="<?php echo e(asset('umkm.mp4')); ?>" type="video/mp4">
            Browser Anda tidak mendukung video.
        </video>
        <div class="overlay"></div>
        <div class="welcome-container animate-fade-in">
            <!-- Left Side - Welcome Content -->
            <div class="welcome-left">
                <div class="logo-container">
                    <div class="logo-glow"></div>
                    <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Manufacturing ERP Logo" class="logo">
                </div>
                <h1>Sistem Keuangan Manufaktur Terpadu</h1>
                <p class="welcome-text">Solusi lengkap untuk mengelola keuangan dan operasional perusahaan manufaktur Anda. Dari pembelian bahan baku hingga laporan keuangan, semua terintegrasi dalam satu platform.</p>
                
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-industry feature-icon"></i>
                        <span>Manajemen Produksi & BOM (Bill of Materials)</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calculator feature-icon"></i>
                        <span>Perhitungan Harga Pokok Produksi Otomatis</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <span>Laporan Keuangan & Analisis Biaya Real-time</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-boxes feature-icon"></i>
                        <span>Kontrol Inventaris Bahan Baku & Produk Jadi</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                    </a>
                    
                    <div class="divider">atau</div>
                    
                    <a href="<?php echo e(route('register')); ?>" class="btn btn-outline">
                        <i class="fas fa-user-plus mr-2"></i> Daftar Akun Baru
                    </a>
                </div>
            </div>

            <!-- Right Side - Image/Illustration -->
            <div class="welcome-right">
                <div>
                    <h2 class="text-3xl font-bold mb-6">Solusi ERP Manufaktur Terdepan</h2>
                    <p class="mb-8 text-lg">Tingkatkan efisiensi produksi dan kontrol keuangan perusahaan manufaktur Anda dengan sistem ERP yang komprehensif dan mudah digunakan.</p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-3"></div>
                            <span class="text-green-100">Sistem Terintegrasi & Komprehensif</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-400 rounded-full mr-3"></div>
                            <span class="text-blue-100">Interface Modern & User-Friendly</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-purple-400 rounded-full mr-3"></div>
                            <span class="text-purple-100">Laporan Real-time & Akurat</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-400 rounded-full mr-3"></div>
                            <span class="text-yellow-100">Efisiensi Maksimal & Hemat Biaya</span>
                        </div>
                    </div>
                    
                    <div class="mt-8 p-4 bg-gradient-to-r from-blue-600/20 to-purple-600/20 rounded-lg border border-blue-400/30">
                        <p class="text-center text-blue-100 font-medium">
                            "Platform yang dirancang khusus untuk memenuhi kebutuhan industri manufaktur modern dengan teknologi terdepan"
                        </p>
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
<?php /**PATH C:\UMKM_COE\resources\views/welcome.blade.php ENDPATH**/ ?>