<?php
/**
 * Complete Storage Test
 * Test semua aspek storage route untuk memastikan semuanya berfungsi
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          COMPLETE STORAGE ROUTE TEST                           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$allPassed = true;

// Test 1: File Existence
echo "📁 TEST 1: File Existence Check\n";
echo "─────────────────────────────────────────────────────────────────\n";
$testFile = 'storage/app/public/bukti_faktur/1/1778021408_nota e2000.png';
if (file_exists($testFile)) {
    echo "✅ File exists: $testFile\n";
    echo "   Size: " . number_format(filesize($testFile)) . " bytes\n";
    echo "   Mime: " . mime_content_type($testFile) . "\n";
} else {
    echo "❌ File NOT found: $testFile\n";
    $allPassed = false;
}
echo "\n";

// Test 2: Route File
echo "🛣️  TEST 2: Route Configuration\n";
echo "─────────────────────────────────────────────────────────────────\n";
if (file_exists('routes/storage.php')) {
    echo "✅ routes/storage.php exists\n";
    $content = file_get_contents('routes/storage.php');
    if (strpos($content, "Route::get('/storage/{path}'") !== false) {
        echo "✅ Storage route defined\n";
    } else {
        echo "❌ Storage route NOT found in file\n";
        $allPassed = false;
    }
} else {
    echo "❌ routes/storage.php NOT found\n";
    $allPassed = false;
}
echo "\n";

// Test 3: Web.php Include
echo "🔗 TEST 3: Route Include Check\n";
echo "─────────────────────────────────────────────────────────────────\n";
$webContent = file_get_contents('routes/web.php');
if (strpos($webContent, "require_once __DIR__ . '/storage.php'") !== false ||
    strpos($webContent, "require __DIR__ . '/storage.php'") !== false) {
    echo "✅ storage.php is included in web.php\n";
} else {
    echo "❌ storage.php NOT included in web.php\n";
    $allPassed = false;
}
echo "\n";

// Test 4: View Updates
echo "👁️  TEST 4: View Updates Check\n";
echo "─────────────────────────────────────────────────────────────────\n";
$viewsToCheck = [
    'resources/views/transaksi/pembelian/partials/pembelian-content.blade.php',
    'resources/views/transaksi/pembelian/show.blade.php',
    'resources/views/transaksi/penjualan/show.blade.php',
    'resources/views/transaksi/penjualan/index.blade.php',
];

foreach ($viewsToCheck as $view) {
    if (file_exists($view)) {
        $content = file_get_contents($view);
        // Check if using url() instead of asset() for storage
        $hasUrl = strpos($content, "url('/storage/") !== false;
        $hasAsset = strpos($content, "asset('storage/") !== false;
        
        if ($hasUrl && !$hasAsset) {
            echo "✅ " . basename($view) . " - Using url()\n";
        } elseif ($hasUrl && $hasAsset) {
            echo "⚠️  " . basename($view) . " - Mixed (has both url() and asset())\n";
        } else {
            echo "❌ " . basename($view) . " - Still using asset()\n";
            $allPassed = false;
        }
    } else {
        echo "⚠️  " . basename($view) . " - File not found\n";
    }
}
echo "\n";

// Test 5: Database Check
echo "💾 TEST 5: Database Check\n";
echo "─────────────────────────────────────────────────────────────────\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=eadt_umkm', 'root', '');
    $stmt = $pdo->query("SELECT id, nomor_pembelian, bukti_faktur FROM pembelians WHERE id = 1");
    $pembelian = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pembelian) {
        echo "✅ Database connection OK\n";
        echo "✅ Pembelian ID: {$pembelian['id']}\n";
        echo "✅ Nomor: {$pembelian['nomor_pembelian']}\n";
        echo "✅ Bukti Path: {$pembelian['bukti_faktur']}\n";
        
        // Verify file path matches
        $dbPath = 'storage/app/public/' . $pembelian['bukti_faktur'];
        if (file_exists($dbPath)) {
            echo "✅ File exists at database path\n";
        } else {
            echo "❌ File NOT found at database path: $dbPath\n";
            $allPassed = false;
        }
    } else {
        echo "⚠️  Pembelian ID 1 not found (might be OK if data different)\n";
    }
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    $allPassed = false;
}
echo "\n";

// Test 6: Security Check
echo "🔒 TEST 6: Security Configuration\n";
echo "─────────────────────────────────────────────────────────────────\n";
$storageContent = file_get_contents('routes/storage.php');
$securityChecks = [
    'allowedExtensions' => strpos($storageContent, 'allowedExtensions') !== false,
    'file_exists check' => strpos($storageContent, 'file_exists') !== false,
    'realpath check' => strpos($storageContent, 'realpath') !== false,
    'abort on error' => strpos($storageContent, 'abort(') !== false,
];

foreach ($securityChecks as $check => $passed) {
    echo ($passed ? "✅" : "❌") . " $check\n";
    if (!$passed) $allPassed = false;
}
echo "\n";

// Summary
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST SUMMARY                            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

if ($allPassed) {
    echo "🎉 ALL TESTS PASSED! 🎉\n\n";
    echo "✅ Storage route is properly configured\n";
    echo "✅ Views are updated to use url() helper\n";
    echo "✅ Security checks are in place\n";
    echo "✅ Files exist and are accessible\n\n";
    
    echo "🚀 NEXT STEPS:\n";
    echo "1. Restart your development server:\n";
    echo "   - Stop current server (Ctrl+C)\n";
    echo "   - Run: php artisan serve\n\n";
    echo "2. Test in browser:\n";
    echo "   - Open: http://127.0.0.1:8000/transaksi/pembelian/1\n";
    echo "   - Click 'Lihat Bukti' button\n";
    echo "   - Image should open in new tab\n\n";
    echo "3. Test direct URL:\n";
    echo "   - Open: http://127.0.0.1:8000/storage/bukti_faktur/1/1778021408_nota%20e2000.png\n";
    echo "   - Image should display directly\n\n";
} else {
    echo "⚠️  SOME TESTS FAILED\n\n";
    echo "Please review the failed tests above and fix the issues.\n";
    echo "Then run this test again to verify.\n\n";
}

echo "📚 Documentation: See STORAGE_FIX_DOCUMENTATION.md for details\n\n";
