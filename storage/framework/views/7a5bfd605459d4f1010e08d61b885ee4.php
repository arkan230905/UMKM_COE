<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    
    <link href="<?php echo e(asset('css/modern-dashboard.css')); ?>?v=<?php echo e(time()); ?>" rel="stylesheet">

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
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\UMKM_COE\resources\views/layouts/app.blade.php ENDPATH**/ ?>