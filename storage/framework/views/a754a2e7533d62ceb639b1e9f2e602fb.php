<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIMCOST - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    <!-- Favicon menggunakan logo asli - PRIORITAS UKURAN BESAR -->
    <link rel="icon" type="image/png" sizes="128x128" href="<?php echo e(asset('images/logo.png')); ?>?v=<?php echo e(time()); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo e(asset('images/logo.png')); ?>?v=<?php echo e(time()); ?>">
    <link rel="icon" type="image/png" sizes="64x64" href="<?php echo e(asset('images/logo.png')); ?>?v=<?php echo e(time()); ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?php echo e(asset('images/logo.png')); ?>?v=<?php echo e(time()); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('images/logo.png')); ?>?v=<?php echo e(time()); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('images/logo.png')); ?>?v=<?php echo e(time()); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('images/logo.png')); ?>?v=<?php echo e(time()); ?>">
    
    <!-- CSS untuk optimasi favicon logo asli -->
    <link href="<?php echo e(asset('css/favicon-fix.css')); ?>" rel="stylesheet">
    
    <!-- Meta untuk Windows dan mobile -->
    <meta name="msapplication-TileImage" content="<?php echo e(asset('images/logo.png')); ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    
    <link href="<?php echo e(asset('css/modern-dashboard.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('css/modern-dashboard.css')); ?>" rel="stylesheet">
    <link href="/css/modern-dashboard.css" rel="stylesheet">
    
    
    <style>
        /* Critical CSS - Always loaded */
        :root {
            --body-bg: #F4F6F9;
            --sidebar-bg: #8A6B48;
            --card-bg: #FFFFFF;
            --border: #E8ECF0;
            --text-primary: #1A1A2E;
            --text-secondary: #6B7280;
            --text-muted: #9CA3AF;
            --brown: #5C3D2E;
            --brown-light: #8B6347;
            --green: #22C55E;
            --green-bg: #DCFCE7;
            --yellow: #F59E0B;
            --yellow-bg: #FEF3C7;
            --blue: #3B82F6;
            --blue-bg: #DBEAFE;
            --red: #EF4444;
            --red-bg: #FEE2E2;
            --purple: #8B5CF6;
            --purple-bg: #EDE9FE;
        }
        
        body {
            font-family: 'Poppins', sans-serif !important;
            background: var(--body-bg) !important;
            color: var(--text-primary) !important;
            font-size: 0.8125rem !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .layout {
            display: flex !important;
            min-height: 100vh !important;
        }
        
        .sidebar {
            width: 220px !important;
            min-height: 100vh !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            bottom: 0 !important;
            z-index: 1000 !important;
            overflow-y: auto !important;
            background: #8A6B48 !important;
            box-shadow: 3px 0 16px rgba(0,0,0,0.10) !important;
        }
        
        .content {
            margin-left: 220px !important;
            flex: 1 !important;
            min-height: 100vh !important;
            background: var(--body-bg) !important;
            padding-top: 90px !important;
        }
        
        .topbar {
            background: var(--body-bg) !important;
            border-bottom: 1px solid var(--border) !important;
            padding: 16px 24px !important;
            position: fixed !important;
            top: 0 !important;
            left: 220px !important;
            right: 0 !important;
            z-index: 999 !important;
        }
        
        .page-wrapper {
            padding: 20px 24px !important;
        }
        
        .kpi-card {
            background: var(--card-bg) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 16px 18px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
        }
        
        .dash-card {
            background: var(--card-bg) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
        }
    </style>
    
    <?php echo $__env->yieldContent('head'); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

<div class="layout">

    
    <aside class="sidebar">
        <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </aside>

    
    <main class="content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<script src="<?php echo e(asset('js/auto-reset.js')); ?>"></script>


<script src="<?php echo e(asset('js/favicon-optimizer.js')); ?>"></script>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampppp\htdocs\UMKM_COE\resources\views/layouts/app.blade.php ENDPATH**/ ?>