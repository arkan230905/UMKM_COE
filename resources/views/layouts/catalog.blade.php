<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catalog - UMKM COE')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('catalog') }}">
                <i class="fas fa-shopping-bag me-2"></i>
                UMKM COE
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('catalog') ? 'active' : '' }}" href="{{ route('catalog') }}">
                            <i class="fas fa-home me-1"></i>
                            Katalog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pelanggan/login">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Login Pelanggan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">
                            <i class="fas fa-user-shield me-1"></i>
                            Login Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">UMKM COE</h5>
                    <p class="text-muted">Sistem manajemen manufaktur terpadu untuk UMKM</p>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">Kontak</h5>
                    <p class="text-muted">
                        <i class="fas fa-envelope me-2"></i>info@umkmcoe.com<br>
                        <i class="fas fa-phone me-2"></i>+62 812-3456-7890
                    </p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center text-muted">
                <small>&copy; {{ date('Y') }} UMKM COE. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
