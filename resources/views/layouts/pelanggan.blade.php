<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'UMKM COE') }} - Belanja Online</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    @yield('styles')
    
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            padding-top: 20px;
            padding-bottom: 40px;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('pelanggan.dashboard') }}">
                <i class="bi bi-shop"></i> {{ config('app.name', 'UMKM COE') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center justify-content-end">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pelanggan.dashboard') }}">
                            <i class="bi bi-house-door"></i> Beranda
                        </a>
                    </li>

                    <li class="nav-item mx-2">
                        <a href="{{ route('pelanggan.cart') }}" class="btn btn-cart">
                            <i class="bi bi-cart3"></i> Keranjang
                            @if(isset($cartCount) && $cartCount > 0)
                            <span class="cart-badge">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('pelanggan.orders') }}">
                                    <i class="bi bi-box-seam"></i> Pesanan Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('pelanggan.favorites') }}">
                                    <i class="bi bi-heart"></i> Favorite
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('pelanggan.returns.create') }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> Retur
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
        @yield('review-section')
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'UMKM COE') }}. All rights reserved.</p>
            <small class="text-muted">Belanja mudah, aman, dan terpercaya</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
