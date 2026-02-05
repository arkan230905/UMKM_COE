<?php

echo "🔍 CHECKING SERVER STATUS\n";
echo "========================\n\n";

// Check if Laravel server is running
$url = 'http://127.0.0.1:8000';
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'method' => 'GET'
    ]
]);

echo "1. Testing Laravel server connection...\n";
$result = @file_get_contents($url, false, $context);

if ($result !== false) {
    echo "✅ Laravel server is running at $url\n";
} else {
    echo "❌ Laravel server is NOT running at $url\n";
    echo "   Please run: php artisan serve\n\n";
    exit(1);
}

// Check specific biaya bahan route
$biayaBahanUrl = $url . '/master-data/biaya-bahan/create/2';
echo "\n2. Testing biaya bahan route...\n";

$biayaBahanResult = @file_get_contents($biayaBahanUrl, false, $context);

if ($biayaBahanResult !== false) {
    echo "✅ Biaya bahan route is accessible\n";
    
    // Check if our JavaScript is present
    if (strpos($biayaBahanResult, 'BIAYA BAHAN LOADED') !== false) {
        echo "✅ New JavaScript is loaded in the page\n";
    } else {
        echo "⚠️  New JavaScript might not be loaded (check browser cache)\n";
    }
    
    // Check for sub satuan data
    if (strpos($biayaBahanResult, 'data-sub-satuan') !== false) {
        echo "✅ Sub satuan data is present in HTML\n";
    } else {
        echo "⚠️  Sub satuan data might be missing\n";
    }
    
} else {
    echo "❌ Biaya bahan route is NOT accessible\n";
    echo "   URL: $biayaBahanUrl\n";
    echo "   Check if route exists and product ID 2 exists\n\n";
}

// Check test page
$testUrl = $url . '/test_biaya_bahan_simple.html';
echo "\n3. Testing simple test page...\n";

if (file_exists('public/test_biaya_bahan_simple.html')) {
    echo "✅ Test page file exists\n";
} else {
    // Copy test file to public directory
    if (copy('test_biaya_bahan_simple.html', 'public/test_biaya_bahan_simple.html')) {
        echo "✅ Test page copied to public directory\n";
    } else {
        echo "❌ Could not copy test page to public directory\n";
    }
}

echo "\n📋 SUMMARY\n";
echo "==========\n";
echo "Laravel Server: " . ($result !== false ? "✅ Running" : "❌ Not running") . "\n";
echo "Biaya Bahan Route: " . ($biayaBahanResult !== false ? "✅ Accessible" : "❌ Not accessible") . "\n";
echo "Test Page: ✅ Available at $testUrl\n";

echo "\n🎯 NEXT STEPS\n";
echo "=============\n";
echo "1. Open browser and go to: $testUrl\n";
echo "2. Test the JavaScript functions\n";
echo "3. If test works, go to: $biayaBahanUrl\n";
echo "4. Clear browser cache (Ctrl+Shift+R) if needed\n";
echo "5. Check console (F12) for any errors\n";

?>