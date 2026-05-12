<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING FORMAT NUMBER CLEAN FUNCTION ===\n\n";

// Define the function like in the view
function formatNumberClean($number, $decimals = 2) {
    if ($number == 0) return '0';
    
    // Jika nilai adalah integer (tidak ada desimal), tampilkan tanpa desimal
    if ($number == floor($number)) {
        return number_format($number, 0, ',', '.');
    }
    
    // Format dengan desimal
    $formatted = number_format($number, $decimals, ',', '.');
    
    // Hapus trailing zeros setelah koma
    $formatted = rtrim($formatted, '0');
    $formatted = rtrim($formatted, ',');
    
    return $formatted;
}

// Test with BOP data
$totalBopPerProduk = 95.00;
$totalBiayaPerProduk = 261.67;

echo "Testing formatNumberClean function:\n";
echo "Total BOP / produk: " . $totalBopPerProduk . " -> " . formatNumberClean($totalBopPerProduk) . "\n";
echo "Total Biaya / produk: " . $totalBiayaPerProduk . " -> " . formatNumberClean($totalBiayaPerProduk) . "\n";

// Test with component values
$gasPerProduk = 67;
$airKebersihanPerProduk = 28;

echo "\nComponent formatting:\n";
echo "Gas / BBM: " . $gasPerProduk . " -> " . formatNumberClean($gasPerProduk) . "\n";
echo "Air & Kebersihan: " . $airKebersihanPerProduk . " -> " . formatNumberClean($airKebersihanPerProduk) . "\n";

// Test edge cases
echo "\nEdge cases:\n";
echo "Zero: " . formatNumberClean(0) . "\n";
echo "Integer: " . formatNumberClean(100) . "\n";
echo "Decimal: " . formatNumberClean(100.50) . "\n";
echo "Decimal with trailing zeros: " . formatNumberClean(100.00) . "\n";

echo "\nExpected view output:\n";
echo "Gas / BBM: Rp " . formatNumberClean($gasPerProduk) . "\n";
echo "Air & Kebersihan: Rp " . formatNumberClean($airKebersihanPerProduk) . "\n";
echo "Total BOP / produk: Rp " . formatNumberClean($totalBopPerProduk) . "\n";
echo "Biaya / produk: Rp " . formatNumberClean($totalBiayaPerProduk) . "\n";

echo "\n=== FUNCTION TEST COMPLETE! 🎉 ===\n";
