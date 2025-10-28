<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'UMKM COE') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #1e1e2f;
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background-color: #2c2c3e !important;
        }
        .navbar-brand {
            font-weight: 600;
            color: #ffffff !important;
        }
        .navbar-nav .nav-link {
            color: #ddd !important;
            transition: 0.2s;
        }
        .navbar-nav .nav-link:hover {
            color: #fff !important;
        }
        .dropdown-menu {
            background-color: #2c2c3e;
        }
        .dropdown-item {
            color: #fff !important;
        }
        .dropdown-item:hover {
            background-color: #3b3b4f;
        }
    </style>
</head>
<body>
    <!-- ====== NAVBAR ====== -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-gear-fill me-2"></i> UMKM COE
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <!-- PROFIL ADMIN -->
                    <li class="nav-item me-2">
                        <a class="nav-link" href="{{ route('profil-admin') }}">
                            <i class="bi bi-person-circle me-1"></i> Profil Admin
                        </a>
                    </li>

                    <!-- TENTANG PERUSAHAAN -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('tentang-perusahaan') }}">
                            <i class="bi bi-building me-1"></i> Tentang Perusahaan
                        </a>
                    </li>

                    <!-- DROPDOWN LOGOUT -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-badge"></i> {{ Auth::user()->name ?? 'Admin' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-1"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ====== MAIN CONTENT ====== -->
    <main class="py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
