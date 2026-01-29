<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Presensi') - UMKM COE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 80px;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .sidebar.collapsed .sidebar-header h3 {
            font-size: 1rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0;
        }
        
        .sidebar-menu .nav-link:hover,
        .sidebar-menu .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        
        .sidebar.collapsed .nav-link span {
            display: none;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 80px;
        }
        
        .top-navbar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            border: none;
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-success {
            background: var(--success-color);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-warning {
            background: var(--warning-color);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .btn-danger {
            background: var(--danger-color);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stats-card .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stats-card .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-card .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--dark-color);
            cursor: pointer;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: var(--light-color);
            border: none;
            font-weight: 600;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header h3 {
                font-size: 1rem;
            }
            
            .sidebar-menu .nav-link span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
                padding: 15px;
            }
            
            .stats-card {
                margin-bottom: 15px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-fingerprint me-2"></i>Presensi</h3>
        </div>
        <nav class="sidebar-menu">
            <a href="{{ route('presensi.dashboard') }}" class="nav-link {{ request()->routeIs('presensi.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('presensi.check.in') }}" class="nav-link {{ request()->routeIs('presensi.check.in') ? 'active' : '' }}">
                <i class="fas fa-sign-in-alt"></i>
                <span>Check In</span>
            </a>
            <a href="{{ route('presensi.check.out') }}" class="nav-link {{ request()->routeIs('presensi.check.out') ? 'active' : '' }}">
                <i class="fas fa-sign-out-alt"></i>
                <span>Check Out</span>
            </a>
            <a href="{{ route('presensi.history') }}" class="nav-link {{ request()->routeIs('presensi.history') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Riwayat</span>
            </a>
            <a href="{{ route('presensi.logout') }}" class="nav-link">
                <i class="fas fa-power-off"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <button class="toggle-sidebar me-3" id="toggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0">@yield('title', 'Dashboard')</h5>
                </div>
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::guard('presensi')->user()->nama_lengkap, 0, 2)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ Auth::guard('presensi')->user()->nama_lengkap }}</div>
                        <small class="text-muted">{{ Auth::guard('presensi')->user()->jabatan }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Get current time for display
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const timeElements = document.querySelectorAll('.current-time');
            const dateElements = document.querySelectorAll('.current-date');
            
            timeElements.forEach(el => el.textContent = timeString);
            dateElements.forEach(el => el.textContent = dateString);
        }
        
        // Update time every second
        setInterval(updateTime, 1000);
        updateTime();
        
        // Get user location
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    
                    // Set location values to hidden inputs
                    const latitudeInputs = document.querySelectorAll('input[name="latitude"]');
                    const longitudeInputs = document.querySelectorAll('input[name="longitude"]');
                    
                    latitudeInputs.forEach(input => input.value = latitude);
                    longitudeInputs.forEach(input => input.value = longitude);
                }, function(error) {
                    console.log('Error getting location:', error);
                });
            }
        }
        
        // Get location on page load
        getLocation();
    </script>
    @stack('scripts')
</body>
</html>
