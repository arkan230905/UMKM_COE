<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMACOST - Sistem Manufaktur Proses Costing</title>
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
            padding-bottom: 120px; /* Space for fixed footer */
        }
        
        /* Hide scrollbar but keep functionality */
        body::-webkit-scrollbar {
            display: none;
        }
        
        body {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* Video Background */
        .video-bg {
            position: fixed;
            top: -5%;
            left: 0;
            width: 100%;
            height: 110%;
            object-fit: cover;
            z-index: -1;
            filter: contrast(1.1) brightness(1.05) saturate(1.1);
            transform: scale(1.02);
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
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.1) 100%);
            z-index: 0;
        }

        .welcome-container {
            display: flex;
            max-width: 850px;
            width: 100%;
            background: rgba(245, 243, 239, 0.95); /* Cream semi-transparent background */
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(222, 184, 135, 0.3);
            color: #3e2723;
            margin-top: 100px; /* Optimized for zoom 50% visibility */
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

        .welcome-center {
            flex: 1;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: transparent;
            position: relative;
            z-index: 2;
            color: #3e2723;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .logo-main-welcome {
            max-width: 160px;
            height: 160px;
            margin: 0 auto 1.5rem;
            display: block;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.25);
            transition: all 0.4s ease;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);
            padding: 20px;
            border: 3px solid rgba(59, 130, 246, 0.3);
            object-fit: contain;
            animation: logoFloat 3s ease-in-out infinite;
        }

        .logo-main-welcome:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.35);
            border-color: rgba(59, 130, 246, 0.5);
        }

        .logo-partners-welcome {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            opacity: 0;
            animation: fadeInPartners 1s ease-out 0.5s forwards;
        }

        .logo-partner-welcome {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px;
            backdrop-filter: blur(8px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .logo-partner-welcome:hover {
            transform: translateY(-3px) scale(1.1);
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fadeInPartners {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #3e2723;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1.3;
        }

        .welcome-text {
            font-size: 0.95rem;
            color: #5d4037;
            margin-top: 1.5rem;
            margin-bottom: 2rem;
            line-height: 1.7;
            opacity: 0.9;
        }

        .button-center {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
        }

        .button-row {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-login, .btn-register {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1rem;
            min-width: 140px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before, .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before, .btn-register:hover::before {
            left: 100%;
        }

        .btn-login {
            background: linear-gradient(135deg, #d4a574 0%, #c19660 100%);
            color: white;
            border: 2px solid rgba(212, 165, 116, 0.3);
            box-shadow: 0 10px 30px rgba(212, 165, 116, 0.3);
            transform: translateY(0);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #e6b885 0%, #d4a574 100%);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(212, 165, 116, 0.4);
            border-color: rgba(212, 165, 116, 0.5);
        }

        .btn-register {
            background: linear-gradient(135deg, #8b6f47 0%, #6d5637 100%);
            color: white;
            border: 2px solid rgba(139, 111, 71, 0.3);
            box-shadow: 0 10px 30px rgba(139, 111, 71, 0.3);
            transform: translateY(0);
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #a08060 0%, #8b6f47 100%);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(139, 111, 71, 0.5);
            border-color: rgba(139, 111, 71, 0.5);
        }

        .btn-login:active, .btn-register:active {
            transform: translateY(-1px) scale(1.02);
            transition: all 0.1s;
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

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Developer Credits Hover Effects */
        .developer-item:hover {
            color: #d4a574 !important;
            transform: translateY(-2px);
            text-shadow: 0 2px 8px rgba(212, 165, 116, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .welcome-container {
                flex-direction: column;
                max-width: 500px;
                margin-top: 80px;
            }

            .welcome-center {
                padding: 2.5rem;
            }

            .logo-external {
                top: 20px;
                right: 20px;
            }

            .logo-external-main {
                width: 200px;
                height: 200px;
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
            h1 {
                font-size: 1.6rem;
            }

            .welcome-text {
                font-size: 0.95rem;
            }

            .welcome-container {
                margin-top: 60px;
            }

            .logo-external {
                top: 15px;
                right: 15px;
            }

            .logo-external-main {
                width: 150px;
                height: 150px;
            }

            .logo-external-partner {
                width: 130px;
                height: 130px;
            }

            .logo-external-partners {
                gap: 0.6rem;
            }

            .button-row {
                flex-direction: column;
                gap: 0.8rem;
                width: 100%;
            }

            .btn-login, .btn-register {
                width: 100%;
                max-width: 200px;
            }

            /* Developer Credits Mobile Responsive */
            .developer-credits {
                padding: 1rem 0;
            }

            .credits-container {
                padding: 0 1rem;
            }

            .credits-title {
                font-size: 0.8rem;
                margin-bottom: 0.6rem;
                letter-spacing: 1px;
            }

            .credits-list {
                gap: 1rem;
                flex-direction: column;
            }

            .developer-item {
                font-size: 0.85rem;
            }

            .credits-version {
                font-size: 0.7rem;
            }

            /* Adjust body padding for mobile */
            body, html {
                padding-bottom: 140px; /* More space for mobile footer */
            }
        }
    </style>
</head>
<body>

    <div class="gradient-bg">
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
        
        <div class="welcome-container animate-fade-in">
            <!-- Center Content -->
            <div class="welcome-center">
                <h1><span style="font-size: 2.2rem; font-weight: 800;">Selamat Datang di SIMCOST</span><br><span style="font-size: 1.8rem; font-weight: 700;">(Sistem Manufaktur Process Costing)</span></h1>
                <p class="welcome-text" style="font-size: 1.1rem;">SIMCOST adalah aplikasi berbasis web yang dirancang untuk membantu perusahaan manufaktur dalam mengelola dan menghitung biaya produksi secara otomatis, akurat, dan terintegrasi menggunakan metode process costing.</p>
                
                <div class="feature-list">
                    <div style="color: #3e2723; font-weight: 700; font-size: 1.5rem; margin-bottom: 1.5rem; text-align: center;">Fitur Utama</div>
                    <div class="feature-item" style="color: #3e2723; margin-bottom: 1.5rem;">
                        <div class="feature-icon" style="color: #d4a574; background: rgba(212, 165, 116, 0.1); font-size: 18px; font-weight: 700; width: 32px; height: 32px;">1</div>
                        <span style="font-size: 1.2rem; font-weight: 500;">Otomatisasi perhitungan biaya produksi per departemen</span>
                    </div>
                    <div class="feature-item" style="color: #3e2723; margin-bottom: 1.5rem;">
                        <div class="feature-icon" style="color: #d4a574; background: rgba(212, 165, 116, 0.1); font-size: 18px; font-weight: 700; width: 32px; height: 32px;">2</div>
                        <span style="font-size: 1.2rem; font-weight: 500;">Pemantauan bahan baku, tenaga kerja, dan overhead</span>
                    </div>
                    <div class="feature-item" style="color: #3e2723; margin-bottom: 1.5rem;">
                        <div class="feature-icon" style="color: #d4a574; background: rgba(212, 165, 116, 0.1); font-size: 18px; font-weight: 700; width: 32px; height: 32px;">3</div>
                        <span style="font-size: 1.2rem; font-weight: 500;">Perhitungan Harga Pokok Produksi (HPP) yang akurat</span>
                    </div>
                    <div class="feature-item" style="color: #3e2723; margin-bottom: 1.5rem;">
                        <div class="feature-icon" style="color: #d4a574; background: rgba(212, 165, 116, 0.1); font-size: 18px; font-weight: 700; width: 32px; height: 32px;">4</div>
                        <span style="font-size: 1.2rem; font-weight: 500;">Penyajian laporan yang transparan dan terstruktur</span>
                    </div>
                </div>

                <div class="button-center">
                    <div class="button-row">
                        <a href="{{ route('login') }}" class="btn-login" style="padding: 1rem 2.5rem; font-size: 1.1rem; margin-right: 1rem; min-width: 140px;">
                            <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn-register" style="padding: 1rem 2.5rem; font-size: 1.1rem; margin-left: 1rem; min-width: 140px;">
                            <i class="fas fa-user-plus mr-2"></i> Daftar
                        </a>
                    </div>
                </div>
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
