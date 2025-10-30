<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'UMKM COE') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/js/app.js'])
    <style>
        :root {
            --primary-bg: #1e1e2f;
            --secondary-bg: #2c2c3e;
            --content-bg: #0f1420;
            --accent-color: #6c63ff;
            --text-color: #ffffff;
            --text-muted: #a8a8b3;
        }
        
        body {
            background: var(--primary-bg);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }
        
        .sidebar {
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            background: var(--secondary-bg);
            padding: 1rem;
            overflow-y: auto;
            box-shadow: 3px 0 10px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        
        .main {
            margin-left: 250px;
            min-height: 100vh;
            background: var(--content-bg);
            padding: 1.5rem;
        }
        
        .nav-link {
            color: var(--text-color) !important;
            padding: 0.5rem 1rem;

        /* Table styling */
        .table {
            color: #e2e8f0;
        }

        .table thead th {
            background-color: #1e293b;
            border-bottom: 2px solid #334155;
        }

        .table tbody tr {
            background-color: #1e293b;
        }

        .table tbody tr:hover {
            background-color: #1e3a8a;
        }

        /* Card styling */
        .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
        }

        .card-header {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
        }
            
        /* Style untuk tombol agar teks selalu terlihat */
        .btn {
            color: #fff !important;
        }
        
        .btn-outline-primary {
            color: #6c63ff !important;
            border-color: #6c63ff;
        }
        
        .btn-outline-primary:hover {
            color: #fff !important;
            background-color: #6c63ff;
        }
            margin: 0.2rem 0;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: var(--accent-color);
            color: white !important;
        }
        
        .sidebar .text-muted {
            color: var(--text-muted) !important;
        }
        
        /* Table Styles */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .table {
            background: var(--secondary-bg);
            color: var(--text-color);
            margin-bottom: 0;
        }
        
        .table thead th {
            background: var(--accent-color);
            color: white;
            border: none;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        
        .table tbody td {
            vertical-align: middle;
            padding: 1rem;
            border-color: rgba(255, 255, 255, 0.05);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(108, 99, 255, 0.1);
        }
        
        /* Responsive Table */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main {
                margin-left: 0;
            }
            
            .table-responsive {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table {
                min-width: 800px; /* Lebar minimum tabel untuk mobile */
            }
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--secondary-bg);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--accent-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #5a52d4;
        }
        
        /* Card Styling */
        .card {
            background: var(--secondary-bg);
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: rgba(108, 99, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1.5rem;
        }
    </style>
</head>
<body>
    @include('layouts.sidebar')
    <div class="main">
        <div class="d-flex justify-content-end align-items-center gap-2 px-3 py-2 border-bottom" style="background:#111729; position:sticky; top:0; z-index:1040;">
            <span class="small text-light me-2"><i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name ?? 'Admin' }}</span>
            <a href="{{ route('profil-admin') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-person-badge me-1"></i> Profil</a>
            <a href="{{ route('tentang-perusahaan') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-building me-1"></i> Perusahaan</a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
            </form>
        </div>
        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
