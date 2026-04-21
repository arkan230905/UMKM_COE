<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Saldo Awal Buku Besar vs Neraca Saldo ===" . PHP_EOL;

echo PHP_EOL . "User requirement:" . PHP_EOL;
echo "- Buku Besar saldo awal harus sama dengan Neraca Saldo" . PHP_EOL;
echo "- Neraca Saldo sudah benar" . PHP_EOL;
echo "- Buku Besar perlu disesuaikan" . PHP_EOL;

echo PHP_EOL . "=== CHECKING NERACA SALDO METHOD ===" . PHP_EOL;

// Look for neraca-saldo method in AkuntansiController
$controllerFile = __DIR__ . '/app/Http/Controllers/AkuntansiController.php';
$controllerContent = file_get_contents($controllerFile);

if (preg_match('/public function neracaSaldo.*?\{.*?return view\(\'akuntansi\.neraca-saldo\'/s', $controllerContent, $matches)) {
    echo "Found neracaSaldo method" . PHP_EOL;
    
    // Look for saldo awal calculation
    if (strpos($matches[0], 'saldo_awal') !== false) {
        echo "✅ Contains saldo_awal calculation" . PHP_EOL;
    }
    
    if (strpos($matches[0], 'periode') !== false) {
        echo "✅ Contains periode logic" . PHP_EOL;
    }
    
    if (strpos($matches[0], 'coa_period') !== false) {
        echo "✅ Uses coa_period table" . PHP_EOL;
    }
}

echo PHP_EOL . "=== CHECKING BUKU BESAR METHOD ===" . PHP_EOL;

if (preg_match('/public function bukuBesar.*?\{.*?return view\(\'akuntansi\.buku-besar\'/s', $controllerContent, $matches)) {
    echo "Found bukuBesar method" . PHP_EOL;
    
    // Look for saldo awal calculation
    if (strpos($matches[0], 'saldo_awal') !== false) {
        echo "✅ Contains saldo_awal calculation" . PHP_EOL;
    } else {
        echo "❌ Missing saldo_awal calculation" . PHP_EOL;
    }
    
    if (strpos($matches[0], 'periode') !== false) {
        echo "✅ Contains periode logic" . PHP_EOL;
    } else {
        echo "❌ Missing periode logic" . PHP_EOL;
    }
    
    if (strpos($matches[0], 'coa_period') !== false) {
        echo "✅ Uses coa_period table" . PHP_EOL;
    } else {
        echo "❌ Missing coa_period table usage" . PHP_EOL;
    }
}

echo PHP_EOL . "=== ANALYSIS ===" . PHP_EOL;

echo "Expected behavior:" . PHP_EOL;
echo "1. Both pages should use same saldo awal calculation" . PHP_EOL;
echo "2. Both should read from coa_period table" . PHP_EOL;
echo "3. Both should consider periode accounting" . PHP_EOL;

echo PHP_EOL . "Possible issues:" . PHP_EOL;
echo "1. Buku Besar uses different saldo awal calculation" . PHP_EOL;
echo "2. Buku Besar doesn't use coa_period table" . PHP_EOL;
echo "3. Buku Besar uses hardcoded saldo awal" . PHP_EOL;
echo "4. Buku Besar missing periode logic" . PHP_EOL;

echo PHP_EOL . "=== RECOMMENDATIONS ===" . PHP_EOL;
echo "1. Check bukuBesar method in AkuntansiController" . PHP_EOL;
echo "2. Compare saldo awal calculation with neracaSaldo" . PHP_EOL;
echo "3. Update bukuBesar to use same logic" . PHP_EOL;
echo "4. Test with sample account" . PHP_EOL;

echo PHP_EOL . "=== NEXT STEPS ===" . PHP_EOL;
echo "1. Read AkuntansiController bukuBesar method" . PHP_EOL;
echo "2. Read AkuntansiController neracaSaldo method" . PHP_EOL;
echo "3. Compare the saldo awal calculations" . PHP_EOL;
echo "4. Fix bukuBesar to match neracaSaldo" . PHP_EOL;
echo "5. Test the fix" . PHP_EOL;
