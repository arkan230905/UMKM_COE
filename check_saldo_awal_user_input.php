<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Check Saldo Awal User Input vs Current System...\n";

// Authenticate as user 6
$user = \App\Models\User::find(6);
if ($user) {
    auth()->login($user);
    echo "Authenticated as user 6: {$user->name}\n";
}

echo "\n=== USER INPUT (Expected) ===\n";
echo "Kas Bank (111): Rp 100.000.000\n";
echo "Kas (112): Rp 75.000.000\n";
echo "Total Expected Cash: Rp 175.000.000\n";

echo "\n=== CURRENT SYSTEM VALUES ===\n";
$coa111 = \App\Models\Coa::where('kode_akun', '111')->where('user_id', 6)->first();
$coa112 = \App\Models\Coa::where('kode_akun', '112')->where('user_id', 6)->first();

if ($coa111) {
    echo "Kas Bank (111): Rp " . number_format($coa111->saldo_awal ?? 0, 0, ',', '.') . "\n";
} else {
    echo "Kas Bank (111): NOT FOUND\n";
}

if ($coa112) {
    echo "Kas (112): Rp " . number_format($coa112->saldo_awal ?? 0, 0, ',', '.') . "\n";
} else {
    echo "Kas (112): NOT FOUND\n";
}

// Check current trial balance
echo "\n=== CURRENT TRIAL BALANCE ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$cash111 = 0;
$cash112 = 0;

foreach ($trialBalance['accounts'] as $account) {
    if ($account['kode_akun'] == '111') {
        $cash111 = $account['debit'];
        echo "Kas Bank (111) in Trial Balance: Rp " . number_format($cash111, 0, ',', '.') . "\n";
    } elseif ($account['kode_akun'] == '112') {
        $cash112 = $account['debit'];
        echo "Kas (112) in Trial Balance: Rp " . number_format($cash112, 0, ',', '.') . "\n";
    }
}

echo "Current Total Cash: Rp " . number_format($cash111 + $cash112, 0, ',', '.') . "\n";

// Check balance status
$totalDebit = array_sum(array_column($trialBalance['accounts'], 'debit'));
$totalKredit = array_sum(array_column($trialBalance['accounts'], 'kredit'));
$selisih = $totalDebit - $totalKredit;

echo "\n=== BALANCE STATUS ===\n";
echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n";
echo "Status: " . ($selisih == 0 ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";

if ($selisih != 0) {
    echo "\n=== PROBLEM ANALYSIS ===\n";
    
    $expectedCash111 = 100000000;
    $expectedCash112 = 75000000;
    $expectedTotalCash = $expectedCash111 + $expectedCash112;
    
    $actualCash111 = $cash111;
    $actualCash112 = $cash112;
    $actualTotalCash = $actualCash111 + $actualCash112;
    
    echo "Expected vs Actual:\n";
    echo "Kas Bank: Rp " . number_format($expectedCash111, 0, ',', '.') . " vs Rp " . number_format($actualCash111, 0, ',', '.') . "\n";
    echo "Kas: Rp " . number_format($expectedCash112, 0, ',', '.') . " vs Rp " . number_format($actualCash112, 0, ',', '.') . "\n";
    echo "Total: Rp " . number_format($expectedTotalCash, 0, ',', '.') . " vs Rp " . number_format($actualTotalCash, 0, ',', '.') . "\n";
    
    $cashDifference = $expectedTotalCash - $actualTotalCash;
    echo "Cash Difference: Rp " . number_format($cashDifference, 0, ',', '.') . "\n";
    
    if (abs($cashDifference) == abs($selisih)) {
        echo "CONCLUSION: The selisih is caused by incorrect cash account values!\n";
        echo "Solution: Update cash accounts to match user input\n";
    }
}
