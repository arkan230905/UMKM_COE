<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'UMKM COE') }} - Pegawai Pembelian</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --dark-color: #2c3e50;
            --light-bg: #ecf0f1;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Navbar Styling */
        .navbar-custom {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-brand i {
            font-size: 1.8rem;
        }
        
        /* Horizontal Menu */
        .nav-menu {
            display: flex;
            gap: 5px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .nav-menu .nav-item {
            position: relative;
        }
        
        .nav-menu .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-menu .nav-link:hover,
        .nav-menu .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white !important;
            transform: translateY(-2px);
        }
        
        .nav-menu .nav-link i {
            font-size: 1.1rem;
        }
        
        /* User Dropdown */
        .user-dropdown {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 25px;
            padding: 8px 20px;
            color: white;
            transition: all 0.3s;
        }
        
        .user-dropdown:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
            border: none;
            margin-top: 10px;
        }
        
        .dropdown-item {
            padding: 10px 20px;
            transition: all 0.3s;
        }
        
        .dropdown-item:hover {
            background: var(--light-bg);
            padding-left: 25px;
        }
        
        /* Main Content */
        .main-content {
            min-height: calc(100vh - 140px);
            padding: 30px 0;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        /* Stats Cards */
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(45deg);
        }
        
        .stat-card.blue {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .stat-card.green {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }
        
        .stat-card.orange {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }
        
        .stat-card.red {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .stat-icon {
            font-size: 3rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            border: none;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #27ae60 100%);
            border: none;
        }
        
        /* Tables */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            color: white;
        }
        
        .table tbody tr {
            transition: all 0.3s;
        }
        
        .table tbody tr:hover {
            background: rgba(52, 152, 219, 0.1);
            transform: scale(1.01);
        }
        
        /* Footer */
        .footer {
            background: var(--dark-color);
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        /* Alerts */
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('pegawai-pembelian.dashboard') }}">
                <i class="bi bi-cart-check-fill"></i>
                <span>Pegawai Pembelian</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="nav-menu navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pegawai-pembelian.dashboard') ? 'active' : '' }}" 
                           href="{{ route('pegawai-pembelian.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pegawai-pembelian.bahan-baku.*') ? 'active' : '' }}" 
                           href="{{ route('pegawai-pembelian.bahan-baku.index') }}">
                            <i class="bi bi-box-seam"></i>
                            <span>Bahan Baku</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pegawai-pembelian.vendor.*') ? 'active' : '' }}" 
                           href="{{ route('pegawai-pembelian.vendor.index') }}">
                            <i class="bi bi-building"></i>
                            <span>Vendor</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pegawai-pembelian.pembelian.*') ? 'active' : '' }}" 
                           href="{{ route('pegawai-pembelian.pembelian.index') }}">
                            <i class="bi bi-cart-plus"></i>
                            <span>Pembelian</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pegawai-pembelian.retur.*') ? 'active' : '' }}" 
                           href="{{ route('pegawai-pembelian.retur.index') }}">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Retur</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pegawai-pembelian.laporan.*') ? 'active' : '' }}" 
                           href="{{ route('pegawai-pembelian.laporan.pembelian') }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Laporan</span>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle user-dropdown" href="#" id="userDropdown" 
                           role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="bi bi-person"></i> Profile
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
        <div class="container-fluid px-4">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'UMKM COE') }}. All rights reserved.</p>
            <small class="text-muted">Sistem Manajemen Pembelian Bahan Baku</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
