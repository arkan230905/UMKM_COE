<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Verify Account Position Fix ===" . PHP_EOL;

// Test the fixed logic
echo PHP_EOL . "Testing Fixed Account Position Logic:" . PHP_EOL;

// Get key accounts that should be affected
$keyAccounts = [
    '310' => 'Modal Usaha', // 3xx = credit normal
    '2101' => 'Hutang Usaha', // 2xx = credit normal  
    '41' => 'PENDAPATAN', // 4xx = credit normal
    '111' => 'Kas Bank', // 1xx = debit normal
    '112' => 'Kas', // 1xx = debit normal
];

foreach ($keyAccounts as $kodeAkun => $nama) {
    echo PHP_EOL . "Testing COA " . $kodeAkun . " - " . $nama . ":" . PHP_EOL;
    
    $coa = DB::table('coas')->where('kode_akun', $kodeAkun)->first();
    if (!$coa) {
        echo "  COA not found" . PHP_EOL;
        continue;
    }
    
    echo "  Type: " . $coa->tipe_akun . PHP_EOL;
    echo "  Saldo Normal: " . $coa->saldo_normal . PHP_EOL;
    
    // Test the new logic
    $firstDigit = substr($coa->kode_akun, 0, 1);
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    
    echo "  First Digit: " . $firstDigit . PHP_EOL;
    echo "  Should Be Debit Normal: " . ($isDebitNormal ? "YES" : "NO") . PHP_EOL;
    echo "  Should Be Credit Normal: " . ($isDebitNormal ? "NO" : "YES") . PHP_EOL;
    
    // Check if this matches the expected
    $shouldBeDebitNormal = in_array($firstDigit, ['1', '5', '6']);
    $shouldBeCreditNormal = in_array($firstDigit, ['2', '3', '4']);
    
    echo "  Expected Debit Normal: " . ($shouldBeDebitNormal ? "YES" : "NO") . PHP_EOL;
    echo "  Expected Credit Normal: " . ($shouldBeCreditNormal ? "YES" : "NO") . PHP_EOL;
    
    $correct = ($isDebitNormal && $shouldBeDebitNormal) || (!$isDebitNormal && $shouldBeCreditNormal);
    echo "  Logic Correct: " . ($correct ? "YES" : "NO") . PHP_EOL;
}

echo PHP_EOL . "=== Balance Sheet Impact ===" . PHP_EOL;

// Calculate expected balance after fix
echo "Expected Balance Sheet After Fix:" . PHP_EOL;

// Assets (1xx) - should remain the same
$assets = [
    '111' => 93867050, // Kas Bank
    '112' => 72398100, // Kas
    '1130' => 396000, // PPN Masukan
    '1141' => 1920000, // Persediaan Ayam Potong
    '1142' => 600000, // Persediaan Ayam Kampung
    '1143' => 2500000, // Persediaan Bebek
    '1152' => 19040000, // Persediaan Tepung Terigu
    '1153' => 19360000, // Persediaan Tepung Maizena
    '1154' => 5040000, // Persediaan Lada
    '1155' => 15936000, // Persediaan Bubuk Kaldu
    '1156' => 26700800, // Persediaan Bubuk Bawang Putih
    '1161' => 3527518, // Persediaan Ayam Crispy Macdi
    '1162' => 3031518, // Persediaan Ayam Goreng Bundo
];

$totalAssets = array_sum($assets);
echo "Total Assets: " . number_format($totalAssets, 0) . PHP_EOL;

// Liabilities (2xx) - should be calculated as credit normal
$liabilities = [
    '2101' => 0, // Hutang Usaha (debit = credit, so 0)
];

$totalLiabilities = array_sum($liabilities);
echo "Total Liabilities: " . number_format($totalLiabilities, 0) . PHP_EOL;

// Equity (3xx) - should be calculated as credit normal
$equity = [
    '310' => 175000000, // Modal Usaha
    '311' => 0, // Prive
];

$totalEquity = array_sum($equity);
echo "Total Equity: " . number_format($totalEquity, 0) . PHP_EOL;

// Revenue (4xx) - should be calculated as credit normal
$revenue = [
    '41' => 4179150, // PENDAPATAN
];

$totalRevenue = array_sum($revenue);
echo "Total Revenue: " . number_format($totalRevenue, 0) . PHP_EOL;

// Calculate retained earnings
$expenses = -3383013; // From previous calculation
$retainedEarnings = $totalRevenue + $expenses; // Revenue is positive, expenses are negative
echo "Retained Earnings: " . number_format($retainedEarnings, 0) . PHP_EOL;

$totalEquityWithRetained = $totalEquity + $retainedEarnings;
echo "Total Equity with Retained: " . number_format($totalEquityWithRetained, 0) . PHP_EOL;

echo PHP_EOL . "=== Balance Check ===" . PHP_EOL;
echo "Assets: " . number_format($totalAssets, 0) . PHP_EOL;
echo "Liabilities + Equity + Retained: " . number_format($totalLiabilities + $totalEquityWithRetained, 0) . PHP_EOL;
$balance = $totalAssets - ($totalLiabilities + $totalEquityWithRetained);
echo "Balance: " . number_format($balance, 0) . PHP_EOL;
echo "Status: " . ($balance == 0 ? "BALANCED" : "NOT BALANCED") . PHP_EOL;

echo PHP_EOL . "=== Expected Results ===" . PHP_EOL;
echo "After this fix, the balance sheet should:" . PHP_EOL;
echo "1. Calculate accounts 2xx, 3xx, 4xx as credit normal" . PHP_EOL;
echo "2. Show correct equity values" . PHP_EOL;
echo "3. Balance the equation: Assets = Liabilities + Equity" . PHP_EOL;
