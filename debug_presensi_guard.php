<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG PRESensi GUARD ===\n";

// 1. Check auth configuration
echo "\n=== 1. AUTH CONFIGURATION ===\n";
$config = config('auth');
echo "Guards available: " . implode(', ', array_keys($config['guards'])) . "\n";
echo "Providers available: " . implode(', ', array_keys($config['providers'])) . "\n";

if (isset($config['guards']['presensi'])) {
    echo "✅ Presensi guard found:\n";
    echo "   Driver: " . $config['guards']['presensi']['driver'] . "\n";
    echo "   Provider: " . $config['guards']['presensi']['provider'] . "\n";
} else {
    echo "❌ Presensi guard NOT found\n";
}

if (isset($config['providers']['presensi_users'])) {
    echo "✅ Presensi users provider found:\n";
    echo "   Model: " . $config['providers']['presensi_users']['model'] . "\n";
} else {
    echo "❌ Presensi users provider NOT found\n";
}

// 2. Check if model exists
echo "\n=== 2. MODEL CHECK ===\n";
try {
    $presensiUser = new \App\Models\PresensiUser();
    echo "✅ PresensiUser model instantiated\n";
    echo "   Table: " . $presensiUser->getTable() . "\n";
    echo "   Key: " . $presensiUser->getKeyName() . "\n";
} catch (Exception $e) {
    echo "❌ PresensiUser model error: " . $e->getMessage() . "\n";
}

// 3. Test guard directly
echo "\n=== 3. GUARD TEST ===\n";
try {
    $guard = auth('presensi');
    echo "✅ Guard 'presensi' resolved: " . get_class($guard) . "\n";
} catch (Exception $e) {
    echo "❌ Guard 'presensi' error: " . $e->getMessage() . "\n";
}

// 4. Test Auth facade
echo "\n=== 4. AUTH FACADE TEST ===\n";
try {
    $auth = \Illuminate\Support\Facades\Auth::guard('presensi');
    echo "✅ Auth::guard('presensi') resolved: " . get_class($auth) . "\n";
} catch (Exception $e) {
    echo "❌ Auth::guard('presensi') error: " . $e->getMessage() . "\n";
}

// 5. Check service container
echo "\n=== 5. SERVICE CONTAINER ===\n";
try {
    $authManager = $app['auth'];
    echo "✅ Auth manager resolved: " . get_class($authManager) . "\n";
    
    if ($authManager->guard('presensi')) {
        echo "✅ Guard 'presensi' available in manager\n";
    } else {
        echo "❌ Guard 'presensi' NOT available in manager\n";
    }
} catch (Exception $e) {
    echo "❌ Service container error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
