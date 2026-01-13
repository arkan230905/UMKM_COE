<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gudang') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #A18D6D 0%, #8B7355 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-warehouse"></i> Gudang
                        </h4>
                        <small class="text-white-50">{{ config('app.name') }}</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('gudang.dashboard') }}" class="nav-link {{ request()->routeIs('gudang.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('gudang.bahan-baku') }}" class="nav-link {{ request()->routeIs('gudang.bahan-baku') ? 'active' : '' }}">
                                <i class="fas fa-boxes"></i> Bahan Baku
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('gudang.bahan-pendukung') }}" class="nav-link {{ request()->routeIs('gudang.bahan-pendukung') ? 'active' : '' }}">
                                <i class="fas fa-tools"></i> Bahan Pendukung
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('gudang.vendor') }}" class="nav-link {{ request()->routeIs('gudang.vendor') ? 'active' : '' }}">
                                <i class="fas fa-truck"></i> Vendor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('gudang.pembelian') }}" class="nav-link {{ request()->routeIs('gudang.pembelian*') ? 'active' : '' }}">
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
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>