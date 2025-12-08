<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo e(config('app.name', 'UMKM COE')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
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
            margin: 0.2rem 0;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .sidebar .text-muted {
            color: var(--text-muted) !important;
        }
        
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
            
        /* Table Styles */
        .table-responsive {
            border-radius: 8px;
            overflow-x: auto;
            overflow-y: visible;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        /* Custom Scroll Wrapper for wide tables */
        .table-scroll-wrapper {
            width: 100%;
            overflow-x: auto !important;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
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
        
        /* User Profile */
        .bg-primary-darker {
            background-color: var(--primary-darkest) !important;
            border-top: 1px solid var(--sidebar-divider);
        }
        
        .position-absolute.bottom-0 {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav {
            padding-bottom: 80px;
        }
        
        .sidebar .fa-user-circle {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .sidebar .small {
            font-size: 0.75rem !important;
        }
        
        /* Custom styles for sidebar */
        .sidebar .nav-link {
            color: var(--text-muted) !important;
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background: var(--accent-color);
            color: white !important;
        }
        
        .sidebar .text-uppercase {
            color: var(--text-muted) !important;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            padding: 0 0.5rem;
        }
        
        .sidebar .nav-item.mt-3 {
            margin-top: 1.5rem !important;
        }
        
        .sidebar .nav-item.mb-1 {
            margin-bottom: 0.5rem !important;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #1e293b;
            color: #e2e8f0;
            border-color: #6c63ff;
            box-shadow: 0 0 0 0.25rem rgba(108, 99, 255, 0.25);
        }
    </style>
</head>
<body>
    <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="main">
        <div class="d-flex justify-content-end align-items-center gap-2 px-3 py-2 border-bottom" style="background:#111729; position:sticky; top:0; z-index:1040;">
            <span class="small text-light me-2"><i class="bi bi-person-circle me-1"></i> <?php echo e(Auth::user()->name ?? 'Admin'); ?></span>
            <a href="<?php echo e(route('profil-admin')); ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-person-badge me-1"></i> Profil</a>
            <a href="<?php echo e(route('tentang-perusahaan')); ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-building me-1"></i> Perusahaan</a>
            <form action="<?php echo e(route('logout')); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
            </form>
        </div>
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\UMKM_COE\resources\views/layouts/app.blade.php ENDPATH**/ ?>