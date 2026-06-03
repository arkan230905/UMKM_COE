<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $currentCompany = request()->attributes->get('perusahaan') ?? \App\Models\Perusahaan::find(session('perusahaan_id')) ?? \App\Models\Perusahaan::first();
    @endphp
    <title>{{ $currentCompany->nama ?? 'SIMCOST' }} - @yield('title', 'Belanja Online')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, #d4a574 0%, #b8935f 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .btn-cart {
            position: relative;
            background: #ffffff;
            border: 1px solid #e6e9f0;
            color: #1f2937;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            font-weight: 600;
        }
        
        .btn-cart:hover {
            background: #f7f9fc;
            color: #0f172a;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,.12);
        }
        
        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
        }
        
        .main-content {
            min-height: calc(100vh - 120px);
            padding-top: 2rem;
            padding-bottom: 40px;
            background-color: white;
            display: block;
        }
        
        .footer {
            background: linear-gradient(135deg, #8b5a2b 0%, #5c3917 100%);
            color: rgba(255, 255, 255, 0.9);
            padding: 50px 0 20px 0;
            margin-top: 60px;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.05);
        }
        
        .footer-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: white;
            margin-bottom: 1.2rem;
            letter-spacing: 0.5px;
        }
        
        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: white;
            text-decoration: underline;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            margin-top: 2rem;
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            pointer-events: none;
        }

        .toast-notification {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
            pointer-events: auto;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid;
            font-size: 0.9rem;
            font-weight: 500;
            max-width: 350px;
        }

        .toast-notification.success {
            border-left-color: #10b981;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #065f46;
        }

        .toast-notification.error {
            border-left-color: #ef4444;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #7f1d1d;
        }

        .toast-notification.info {
            border-left-color: #3b82f6;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            color: #0c2340;
        }

        .toast-notification.warning {
            border-left-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            color: #78350f;
        }

        .toast-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .toast-close {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            opacity: 0.6;
            transition: opacity 0.2s;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }

        .toast-close:hover {
            opacity: 1;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .toast-notification.removing {
            animation: slideOutRight 0.3s ease-in forwards;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height:36px;width:auto;">
                <img src="{{ asset('images/logo_telkom.png') }}" alt="Telkom" style="height:36px;width:auto;">
                <img src="{{ asset('images/logo_eadt.png') }}" alt="EADT" style="height:36px;width:auto;">
                <span class="ms-2 d-none d-md-inline" style="font-size: 1rem; font-weight: 700; color: white;">{{ $currentCompany->nama ?? '' }}</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center justify-content-end">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('dashboard') }}">
                            <i class="bi bi-house-door"></i> Beranda
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('favorites') }}">
                            <i class="bi bi-heart"></i> Favorit
                        </a>
                    </li>

                    <li class="nav-item mx-2">
                        <a href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('cart') }}" class="btn btn-cart">
                            <i class="bi bi-cart3"></i> Keranjang
                            <span id="cart-badge-header" class="cart-badge" style="display: {{ (isset($cartCount) && $cartCount > 0) ? 'block' : 'none' }};">
                                {{ isset($cartCount) ? $cartCount : 0 }}
                            </span>
                        </a>
                    </li>
                    
                    @if(Auth::guard('pelanggan')->check())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::guard('pelanggan')->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('orders') }}">
                                    <i class="bi bi-box-seam"></i> Pesanan Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('returns.create') }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Retur
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item ms-1">
                        <a href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('login') }}" class="btn btn-outline-light d-flex align-items-center gap-2" style="border-radius: 25px; font-weight: 600; padding: 7px 20px;">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            @php 
                $waNumber = config('services.whatsapp.number') ?? env('WHATSAPP_NUMBER', '6281234567890');
                $waLink = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $waNumber) . '?text=' . urlencode('Halo, saya butuh bantuan mengenai layanan e-commerce Anda.');
            @endphp
            <div class="row gy-4 mb-4">
                <div class="col-lg-5 col-md-6">
                    <h5 class="footer-title">{{ $currentCompany->nama ?? config('app.name') }}</h5>
                    <p class="mb-3" style="font-size: 0.95rem; line-height: 1.6;">
                        Platform e-commerce terpercaya untuk UMKM. Kami menyediakan berbagai produk berkualitas langsung dari produsen ke tangan Anda. Belanja mudah, aman, dan terpercaya.
                    </p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="text-white fs-5 opacity-75 hover-opacity-100 transition"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white fs-5 opacity-75 hover-opacity-100 transition"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white fs-5 opacity-75 hover-opacity-100 transition"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 offset-lg-1">
                    <h5 class="footer-title">Tautan Bantuan</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 0.95rem;">
                        <li><a href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('dashboard') }}" class="footer-link">Beranda</a></li>
                        <li><a href="{{ \App\Helpers\PerusahaanHelper::pelangganRoute('cart') }}" class="footer-link">Keranjang Belanja</a></li>
                        <li><a href="#" class="footer-link">Kebijakan Privasi</a></li>
                        <li><a href="#" class="footer-link">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-12">
                    <h5 class="footer-title">Pusat Bantuan</h5>
                    <p style="font-size: 0.95rem; margin-bottom: 1rem;">
                        Butuh bantuan? Silakan hubungi Call Center kami melalui WhatsApp.
                    </p>
                    <a href="{{ $waLink }}" target="_blank" class="btn btn-light rounded-pill px-4 py-2 mt-2" style="font-weight: 600; color: #5c3917; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="bi bi-whatsapp fs-5 text-success me-2" style="vertical-align: text-bottom;"></i> Hubungi Kami
                    </a>
                </div>
            </div>
            
            <div class="footer-bottom text-center">
                <p class="mb-0" style="font-size: 0.85rem;">&copy; {{ date('Y') }} {{ $currentCompany->nama ?? config('app.name') }}. All rights reserved.</p>
                <small class="text-white-50 mt-1 d-block">Mendukung pertumbuhan UMKM Indonesia</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    
    <script>
        // Toast Notification System
        function showToast(message, type = 'info', duration = 3000) {
            const container = document.getElementById('toastContainer');
            
            const icons = {
                success: '✓',
                error: '✕',
                info: 'ℹ',
                warning: '⚠'
            };

            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <span class="toast-icon">${icons[type]}</span>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            `;

            container.appendChild(toast);

            // Auto remove after duration
            if (duration > 0) {
                setTimeout(() => {
                    toast.classList.add('removing');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>
