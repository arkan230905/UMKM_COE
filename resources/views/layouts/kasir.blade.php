<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kasir') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: calc(100vh - 76px);
            padding: 2rem 0;
        }
        .navbar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('kasir.pos') }}">
                <i class="fas fa-cash-register"></i> Kasir POS
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> 
                        @if(session('kasir_nama'))
                            {{ session('kasir_nama') }}
                        @else
                            Kasir
                        @endif
                    </a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Informasi Kasir</h6></li>
                        <li><span class="dropdown-item-text">
                            <strong>Nama:</strong> {{ session('kasir_nama', 'N/A') }}<br>
                            <strong>Kode:</strong> {{ session('kasir_kode', 'N/A') }}<br>
                            <strong>Email:</strong> {{ session('kasir_email', 'N/A') }}
                        </span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="{{ route('kasir.logout') }}">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>