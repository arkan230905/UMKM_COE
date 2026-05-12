<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Compare Saldo Awal Methods ===" . PHP_EOL;

echo PHP_EOL . "=== READING NERACA SALDO METHOD ===" . PHP_EOL;

// Extract neracaSaldo method
$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';
$controllerContent = file_get_contents($controllerFile);

// Find neracaSaldo method
if (preg_match('/public function neracaSaldo.*?\{(.*?)\n\}/s', $controllerContent, $matches)) {
    $neracaSaldoMethod = $matches[1];
    
    echo "=== NERACA SALDO METHOD EXTRACTED ===" . PHP_EOL;
    echo substr($neracaSaldoMethod, 0, 2000) . PHP_EOL;
    echo "..." . PHP_EOL;
    
    // Look for key patterns
    if (strpos($neracaSaldoMethod, 'coa_period') !== false) {
        echo "✅ Uses coa_period table" . PHP_EOL;
    }
    
    if (strpos($neracaSaldoMethod, 'saldo_awal') !== false) {
        echo "✅ Has saldo_awal calculation" . PHP_EOL;
    }
    
    if (strpos($neracaSaldoMethod, 'periode') !== false) {
        echo "✅ Has periode logic" . PHP_EOL;
    }
}

echo PHP_EOL . "=== READING BUKU BESAR METHOD ===" . PHP_EOL;

// Find bukuBesar method
if (preg_match('/public function bukuBesar.*?\{(.*?)\n\}/s', $controllerContent, $matches)) {
    $bukuBesarMethod = $matches[1];
    
    echo "=== BUKU BESAR METHOD EXTRACTED ===" . PHP_EOL;
    echo substr($bukuBesarMethod, 0, 2000) . PHP_EOL;
    echo "..." . PHP_EOL;
    
    // Look for key patterns
    if (strpos($bukuBesarMethod, 'coa_period') !== false) {
        echo "✅ Uses coa_period table" . PHP_EOL;
    } else {
        echo "❌ Missing coa_period table usage" . PHP_EOL;
    }
    
    if (strpos($bukuBesarMethod, 'saldo_awal') !== false) {
        echo "✅ Has saldo_awal calculation" . PHP_EOL;
    } else {
        echo "❌ Missing saldo_awal calculation" . PHP_EOL;
    }
    
    if (strpos($bukuBesarMethod, 'periode') !== false) {
        echo "✅ Has periode logic" . PHP_EOL;
    } else {
        echo "❌ Missing periode logic" . PHP_EOL;
    }
}

echo PHP_EOL . "=== COMPARISON ANALYSIS ===" . PHP_EOL;

echo "Neraca Saldo features:" . PHP_EOL;
echo "- Uses coa_period table for saldo awal" . PHP_EOL;
echo "- Has proper periode accounting" . PHP_EOL;
echo "- Correct saldo awal calculation" . PHP_EOL;

echo PHP_EOL . "Buku Besar features:" . PHP_EOL;
echo "- Missing coa_period table usage" . PHP_EOL;
echo "- Missing periode logic" . PHP_EOL;
echo "- Missing proper saldo awal calculation" . PHP_EOL;

echo PHP_EOL . "=== SOLUTION NEEDED ===" . PHP_EOL;
echo "Update bukuBesar method to:" . PHP_EOL;
echo "1. Use same saldo awal logic as neracaSaldo" . PHP_EOL;
echo "2. Use coa_period table" . PHP_EOL;
echo "3. Include periode accounting" . PHP_EOL;
echo "4. Match neracaSaldo calculation exactly" . PHP_EOL;

echo PHP_EOL . "=== NEXT ACTION ===" . PHP_EOL;
echo "Need to extract the exact saldo awal logic from neracaSaldo" . PHP_EOL;
echo "And apply it to bukuBesar method" . PHP_EOL;
