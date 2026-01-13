<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pegawai Gudang') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Modern Dashboard CSS -->
    <link href="{{ asset('css/modern-dashboard.css') }}?v={{ time() }}" rel="stylesheet">
    
    @stack('styles')
    
    <style>
        /* Ultra Modern Pegawai Gudang Layout */
        :root {
            --primary-gradient: linear-gradient(135deg, #BBAB8C 0%, #8B7355 100%);
            --secondary-gradient: linear-gradient(135deg, #EAD8C0 0%, #BBAB8C 100%);
            --success-gradient: linear-gradient(135deg, #D9CFC7 0%, #A18D6D 100%);
            --warning-gradient: linear-gradient(135deg, #EAD8C0 0%, #A18D6D 100%);
            --info-gradient: linear-gradient(135deg, #BBAB8C 0%, #A18D6D 100%);
            --dark-gradient: linear-gradient(135deg, #8B7355 0%, #6F5A42 100%);
            --danger-gradient: linear-gradient(135deg, #A18D6D 0%, #8B7355 100%);
        }

        body {
            background: linear-gradient(135deg, #EAD8C0 0%, #BBAB8C 100%) !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            min-height: 100vh;
            color: #333 !important;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #A18D6D 0%, #8B7355 100%) !important;
            backdrop-filter: blur(20px) !important;
            border-right: 1px solid rgba(255,255,255,0.1) !important;
            box-shadow: 0 0 50px rgba(0,0,0,0.5), inset 0 0 100px rgba(255,255,255,0.05) !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 250px !important;
            z-index: 1000 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            position: relative;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(187, 171, 140, 0.18) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(161, 141, 109, 0.14) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(139, 115, 85, 0.18) 0%, transparent 50%);
            z-index: 0;
            animation: shimmer 10s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 0.9; }
        }

        .sidebar > * {
            position: relative;
            z-index: 1;
        }

        .sidebar h4 {
            color: white !important;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            background: linear-gradient(45deg, #ffffff, #f8f9fa, #EAD8C0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 1rem 1.5rem !important;
            border-radius: 15px !important;
            margin: 0.5rem 0.75rem !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            position: relative !important;
            overflow: hidden !important;
            font-weight: 500 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            backdrop-filter: blur(10px) !important;
            background: rgba(255,255,255,0.05) !important;
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(187, 171, 140, 0.28), rgba(139, 115, 85, 0.22));
            transition: left 0.5s ease;
            z-index: -1;
        }

        .sidebar .nav-link::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 4px;
            height: 0;
            background: linear-gradient(to bottom, #BBAB8C, #8B7355);
            transform: translateY(-50%);
            transition: height 0.3s ease;
            border-radius: 0 2px 2px 0;
        }

        .sidebar .nav-link:hover::before,
        .sidebar .nav-link.active::before {
            left: 0;
        }

        .sidebar .nav-link:hover::after,
        .sidebar .nav-link.active::after {
            height: 60%;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white !important;
            transform: translateX(8px) scale(1.02) !important;
            box-shadow: 0 8px 25px rgba(171, 141, 120, 0.4), 0 0 20px rgba(171, 141, 120, 0.2) !important;
            border-color: rgba(171, 141, 120, 0.5) !important;
            background: rgba(171, 141, 120, 0.2) !important;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 12px;
            font-size: 1.1rem;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.2));
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            transform: scale(1.1);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }

        .main-content {
            background: transparent !important;
            min-height: 100vh;
            padding: 2rem !important;
            margin-left: 250px !important;
        }

        /* Modern Company Header */
        .sidebar .text-center {
            padding: 2rem 1rem !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
            background: rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .sidebar .text-white-50 {
            color: rgba(255,255,255,0.8) !important;
            font-size: 0.875rem;
            font-weight: 400;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        /* Logout Button Special Style */
        .sidebar .nav-link.text-warning {
            background: linear-gradient(45deg, rgba(255,107,107,0.2), rgba(238,90,82,0.2)) !important;
            border: 1px solid rgba(255,107,107,0.4) !important;
            color: #ffcccb !important;
            margin-top: 1rem !important;
        }

        .sidebar .nav-link.text-warning::before {
            background: linear-gradient(45deg, #ff6b6b, #ee5a52) !important;
        }

        .sidebar .nav-link.text-warning::after {
            background: linear-gradient(to bottom, #ff6b6b, #ee5a52);
        }

        .sidebar .nav-link.text-warning:hover {
            color: white !important;
            background: linear-gradient(45deg, rgba(255,107,107,0.3), rgba(238,90,82,0.3)) !important;
            box-shadow: 0 8px 25px rgba(255,107,107,0.4), 0 0 20px rgba(255,107,107,0.2) !important;
            border-color: rgba(255,107,107,0.6) !important;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed !important;
                top: 0 !important;
                left: -100% !important;
                width: 280px !important;
                height: 100vh !important;
                z-index: 1050 !important;
                transition: left 0.3s ease !important;
            }
            
            .sidebar.show {
                left: 0 !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 1rem !important;
            }
        }

        /* Desktop specific */
        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none !important;
            }
        }

        /* Mobile overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Custom Scrollbar for Sidebar */
        .sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin: 10px 0;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Add floating particles effect */
        .sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(1px 1px at 20px 30px, rgba(187, 171, 140, 0.35), transparent),
                radial-gradient(1px 1px at 40px 70px, rgba(161, 141, 109, 0.28), transparent),
                radial-gradient(1px 1px at 90px 40px, rgba(187, 171, 140, 0.42), transparent),
                radial-gradient(1px 1px at 130px 80px, rgba(139, 115, 85, 0.28), transparent),
                radial-gradient(1px 1px at 160px 30px, rgba(187, 171, 140, 0.35), transparent);
            background-repeat: repeat;
            background-size: 200px 100px;
            animation: sparkle 25s linear infinite;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes sparkle {
            0% { transform: translateY(0); opacity: 0.8; }
            50% { opacity: 1; }
            100% { transform: translateY(-100px); opacity: 0.8; }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="btn btn-primary d-md-none mobile-menu-toggle" id="mobileMenuToggle" style="position: fixed; top: 1rem; left: 1rem; z-index: 1051; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay d-md-none" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar p-3" id="sidebar">
                    <div class="text-center mb-4">
                        <div class="mb-3" style="position: relative;">
                            <div style="width: 60px; height: 60px; margin: 0 auto; background: linear-gradient(45deg, rgba(187, 171, 140, 0.28), rgba(139, 115, 85, 0.22)); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.2);">
                                <i class="fas fa-warehouse" style="font-size: 1.8rem; color: white; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></i>
                            </div>
                        </div>
                        <h4 class="text-white" style="margin-bottom: 0.5rem;">
                            Pegawai Gudang
                        </h4>
                        <small class="text-white-50">{{ config('app.name') }}</small>
                        <div style="width: 50px; height: 2px; background: linear-gradient(to right, transparent, rgba(255,255,255,0.5), transparent); margin: 1rem auto 0;"></div>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('pegawai-gudang.dashboard') }}" class="nav-link {{ request()->routeIs('pegawai-gudang.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('pegawai-gudang.bahan-baku.index') }}" class="nav-link {{ request()->routeIs('pegawai-gudang.bahan-baku.*') ? 'active' : '' }}">
                                <i class="fas fa-boxes"></i> Bahan Baku
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('pegawai-gudang.bahan-pendukung.index') }}" class="nav-link {{ request()->routeIs('pegawai-gudang.bahan-pendukung.*') ? 'active' : '' }}">
                                <i class="fas fa-tools"></i> Bahan Pendukung
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('pegawai-gudang.vendor.index') }}" class="nav-link {{ request()->routeIs('pegawai-gudang.vendor.*') ? 'active' : '' }}">
                                <i class="fas fa-truck"></i> Vendor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('pegawai-gudang.pembelian.index') }}" class="nav-link {{ request()->routeIs('pegawai-gudang.pembelian.*') ? 'active' : '' }}">
                                <i class="fas fa-shopping-cart"></i> Pembelian
                            </a>
                        </li>
                        
                        <hr class="text-white-50">
                        
                        <li class="nav-item">
                            <a href="{{ route('gudang.logout') }}" class="nav-link text-warning">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Mobile Menu Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (mobileToggle && sidebar && overlay) {
                // Toggle sidebar
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
                
                // Close sidebar when clicking overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                            overlay.classList.remove('show');
                        }
                    }
                });

                // Close sidebar on window resize
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                });
            }
        });
    </script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>