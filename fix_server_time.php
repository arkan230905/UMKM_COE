<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Fixing server time synchronization...\n\n";

// Get Windows system time
$windowsTime = null;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    exec('powershell -Command "Get-Date -Format \'yyyy-MM-dd HH:mm:ss\'"', $output);
    if (!empty($output[0])) {
        $windowsTime = $output[0];
        echo "Windows system time: {$windowsTime}\n";
    }
}

echo "PHP time before: " . date('Y-m-d H:i:s') . "\n";
echo "Laravel time before: " . now()->format('Y-m-d H:i:s') . "\n\n";

// Force set PHP timezone
date_default_timezone_set('Asia/Jakarta');

echo "After timezone reset:\n";
echo "PHP time after: " . date('Y-m-d H:i:s') . "\n";
echo "Laravel time after: " . now()->format('Y-m-d H:i:s') . "\n\n";

// Calculate offset if we have Windows time
if ($windowsTime) {
    $phpTime = new DateTime();
    $winTime = new DateTime($windowsTime);
    $diff = $winTime->diff($phpTime);
    
    echo "Time difference: ";
    if ($diff->h > 0) {
        echo "{$diff->h} hours ";
    }
    if ($diff->i > 0) {
        echo "{$diff->i} minutes ";
    }
    echo "\n";
    
    if ($diff->h > 0 || $diff->i > 5) {
        echo "⚠️  Significant time difference detected!\n";
        echo "📝 Manual correction needed:\n";
        echo "1. Restart your web server (Apache/Nginx)\n";
        echo "2. Restart PHP-FPM if using it\n";
        echo "3. Check Windows time service\n";
    } else {
        echo "✅ Time difference is acceptable\n";
    }
}

echo "\n🎉 Time fix attempt complete!\n";