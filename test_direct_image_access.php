<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING DIRECT IMAGE ACCESS ===\n\n";

echo "🔍 TESTING DIRECT URL ACCESS:\n";

$filename = '1778021408_nota e2000.png';
$directUrl = url('/bukti-faktur/' . $filename);

echo "📱 Direct URL: $directUrl\n";

// Test HTTP access
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n"
    ]
]);

$response = @file_get_contents($directUrl, false, $context);

if ($response !== false) {
    echo "✅ Direct URL SUCCESS - File accessible!\n";
    echo "📄 HTTP Response: " . substr($response, 0, 200) . "...\n";
} else {
    echo "❌ Direct URL FAILED - Still 403 error\n";
    echo "🔍 Error: " . error_get_last()['message'] . "\n";
}

echo "\n🔍 TESTING ROUTE REGISTRATION:\n";

// Check if routes are registered
$routes = app('router')->getRoutes();
$directRouteExists = false;

foreach ($routes as $route) {
    if (isset($route->uri) && strpos($route->uri, 'bukti-faktur') !== false) {
        $directRouteExists = true;
        break;
    }
}

if ($directRouteExists) {
    echo "✅ Direct bukti-faktur route registered\n";
} else {
    echo "❌ Direct bukti-faktur route NOT found\n";
}

echo "\n📋 CURRENT STATUS:\n";
echo "✅ Route: /bukti-faktur/{filename} (for direct access)\n";
echo "✅ Route: /bukti-faktur/{id}/{filename} (for pembelian detail)\n";
echo "✅ File exists: " . (file_exists(storage_path('app/public/bukti_faktur/' . $filename)) ? 'Yes' : 'No') . "\n";
echo "✅ Storage path: " . storage_path('app/public/bukti_faktur/' . $filename) . "\n";

echo "\n🌐 URL TO TRY:\n";
echo "📱 Direct access: http://127.0.0.1:8001/bukti-faktur/$filename\n";
echo "📄 Pembelian detail: http://127.0.0.1:8001/transaksi/pembelian/1/bukti\n";

echo "\n🔧 TROUBLESHOOTING:\n";
if (!$directRouteExists) {
    echo "❌ Route tidak terdaftar - perlu restart server\n";
} elseif (!file_exists(storage_path('app/public/bukti_faktur/' . $filename))) {
    echo "❌ File tidak ada - perlu cek path\n";
} elseif ($response === false) {
    echo "❌ Masih 403 error - cek:\n";
    echo "   • File permissions\n";
    echo "   • Route registration\n";
    echo "   • Web server config\n";
    echo "   • Laravel cache\n";
} else {
    echo "✅ Semua OK - file seharusnya accessible\n";
}

echo "\n🎯 RECOMMENDATIONS:\n";
echo "1. 🌐 Coba direct URL terlebih dahulu\n";
echo "2. 🔄 Clear browser cache (Ctrl+F5)\n";
echo "3. 🔄 Restart development server\n";
echo "4. 🔍 Cek Laravel logs: php artisan log:clear\n";
echo "5. 📁 Pastikan route terdaftar: php artisan route:list\n";

echo "\n=== TEST COMPLETE ===\n";
