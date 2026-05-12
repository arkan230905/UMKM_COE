<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING BUKTI FAKTUR DISPLAY ===\n\n";

echo "🔍 CHECKING ROUTES:\n";

// Check if routes are registered
$routes = app('router')->getRoutes();
$buktiRoutes = [];

foreach ($routes as $route) {
    if (isset($route->uri) && strpos($route->uri, 'bukti-faktur') !== false) {
        $buktiRoutes[] = $route->uri;
    }
}

if (!empty($buktiRoutes)) {
    echo "✅ Bukti faktur routes found:\n";
    foreach ($buktiRoutes as $route) {
        echo "   - $route\n";
    }
} else {
    echo "❌ No bukti faktur routes found\n";
}

echo "\n🔍 CHECKING PEMBELIAN DATA:\n";

// Get sample pembelian data
$pembelian = \App\Models\Pembelian::where('user_id', 1)
    ->whereNotNull('bukti_faktur')
    ->first();

if ($pembelian) {
    echo "✅ Sample pembelian found:\n";
    echo "   ID: {$pembelian->id}\n";
    echo "   Nomor: {$pembelian->nomor_pembelian}\n";
    echo "   Bukti: {$pembelian->bukti_faktur}\n";
    echo "   File exists: " . (file_exists(storage_path('app/public/' . $pembelian->bukti_faktur)) ? 'Yes' : 'No') . "\n";
    
    $filename = basename($pembelian->bukti_faktur);
    $testUrl = url("/bukti-faktur/{$pembelian->id}/{$filename}");
    echo "   Test URL: $testUrl\n";
} else {
    echo "❌ No pembelian with bukti found\n";
}

echo "\n🔍 CHECKING STORAGE FILES:\n";

// Check if file exists
$testFile = 'bukti_faktur/1/1778021408_nota e2000.png';
$fullPath = storage_path('app/public/' . $testFile);

if (file_exists($fullPath)) {
    echo "✅ Test file exists: $fullPath\n";
    
    // Test direct file access
    $mimeType = mime_content_type($fullPath);
    echo "   MIME type: $mimeType\n";
    echo "   File size: " . filesize($fullPath) . " bytes\n";
    
    // Test route access
    $routeUrl = url("/bukti-faktur/1/1778021408_nota%20e2000.png");
    echo "   Route URL: $routeUrl\n";
    
    echo "   ✅ File should be accessible via:\n";
    echo "     - Direct storage: /storage/$testFile\n";
    echo "     - Route: /bukti-faktur/1/filename\n";
    
} else {
    echo "❌ Test file not found: $fullPath\n";
}

echo "\n📋 INSTRUCTIONS:\n";
echo "1. 🌐 Test direct route:\n";
echo "   http://127.0.0.1:8001/bukti-faktur/1/1778021408_nota%20e2000.png\n\n";

echo "2. 📱 Test pembelian detail route:\n";
echo "   http://127.0.0.1:8001/transaksi/pembelian/1/bukti\n\n";

echo "3. 🔧 If still 403, check:\n";
echo "   - File permissions\n";
echo "   - Route registration\n";
echo "   - Laravel cache\n";
echo "   - Web server configuration\n\n";

echo "=== TEST COMPLETE ===\n";
