<?php
/**
 * Simple test script to verify stock consistency
 * Run this script to check if all stock references are using the correct source
 */

require_once 'vendor/autoload.php';

// Test the PenjualanController API endpoints
echo "=== STOCK CONSISTENCY TEST ===\n\n";

// Test 1: Check if findByBarcode uses correct stock field
echo "Test 1: Checking findByBarcode API endpoint...\n";
$controllerContent = file_get_contents('app/Http/Controllers/PenjualanController.php');

if (strpos($controllerContent, '$produk->stok') !== false && 
    strpos($controllerContent, 'Use stok from produks table') !== false) {
    echo "✅ PASS: findByBarcode uses correct stock source\n";
} else {
    echo "❌ FAIL: findByBarcode may not be using correct stock source\n";
}

// Test 2: Check if searchProducts uses correct stock field
if (strpos($controllerContent, 'Filter products with stok > 0 from produks table') !== false) {
    echo "✅ PASS: searchProducts uses correct stock source\n";
} else {
    echo "❌ FAIL: searchProducts may not be using correct stock source\n";
}

// Test 3: Check view template consistency
echo "\nTest 3: Checking view template consistency...\n";
$viewContent = file_get_contents('resources/views/transaksi/penjualan/create.blade.php');

if (strpos($viewContent, 'data-stok="{{ $p->stok') !== false && 
    strpos($viewContent, 'stok: {{ $p->stok') !== false) {
    echo "✅ PASS: View template uses correct stock field\n";
} else {
    echo "❌ FAIL: View template may have inconsistent stock usage\n";
}

// Test 4: Check for any remaining actual_stok usage
echo "\nTest 4: Checking for problematic actual_stok usage...\n";
if (strpos($controllerContent, '$produk->actual_stok') === false && 
    strpos($controllerContent, '$product->actual_stok') === false) {
    echo "✅ PASS: No problematic actual_stok usage found in controller\n";
} else {
    echo "❌ FAIL: Found actual_stok usage in controller - needs fixing\n";
}

if (strpos($viewContent, 'actual_stok') === false) {
    echo "✅ PASS: No actual_stok usage found in view template\n";
} else {
    echo "❌ FAIL: Found actual_stok usage in view template - needs fixing\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "If all tests pass, stock consistency has been achieved.\n";
echo "If any tests fail, review the STOCK_CONSISTENCY_GUIDELINES.md file.\n";