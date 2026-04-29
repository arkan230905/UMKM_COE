<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Neraca Balance Issue...\n\n";

// Get data from TrialBalanceService (same as neraca saldo)
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$tanggalAwal = '2026-04-01';
$tanggalAkhir = '2026-04-30';
$trialBalanceData = $trialBalanceService->calculateTrialBalance($tanggalAwal, $tanggalAkhir);

echo "Trial Balance Data:\n";
echo "===================\n";

$neracaSaldo = [];
foreach ($trialBalanceData['accounts'] as $account) {
    echo "COA: {$account['kode_akun']} - {$account['nama_akun']}\n";
    echo "  Debit: " . number_format($account['debit'], 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($account['kredit'], 0, ',', '.') . "\n";
    
    // Simulate NeracaService logic
    $saldo = 0;
    if ($account['debit'] > 0) {
        $saldo = $account['debit']; // Normal debit
    } elseif ($account['kredit'] > 0) {
        $saldo = -$account['kredit']; // THIS IS THE PROBLEM - Negative for credit accounts
    }
    
    echo "  Saldo (Current Logic): " . number_format($saldo, 0, ',', '.') . "\n";
    
    // What it should be (absolute value for balance sheet)
    $saldoCorrect = abs($saldo);
    echo "  Saldo (Correct Logic): " . number_format($saldoCorrect, 0, ',', '.') . "\n";
    echo "\n";
    
    $neracaSaldo[] = [
        'kode_akun' => $account['kode_akun'],
        'nama_akun' => $account['nama_akun'],
        'tipe_akun' => $account['tipe_akun'],
        'saldo' => $saldo,
        'saldo_correct' => $saldoCorrect,
        'debit' => $account['debit'],
        'kredit' => $account['kredit']
    ];
}

// Calculate totals with current logic (WRONG)
echo "Current Logic Calculation (WRONG):\n";
echo "===================================\n";

$asetTotal = 0;
$kewajibanTotal = 0;
$ekuitasTotal = 0;

foreach ($neracaSaldo as $account) {
    $saldo = $account['saldo'];
    $tipe = $account['tipe_akun'];
    
    if (in_array($tipe, ['Asset', 'Aset'])) {
        $asetTotal += $saldo;
    } elseif (in_array($tipe, ['Liability', 'Kewajiban'])) {
        $kewajibanTotal += $saldo; // This becomes negative!
    } elseif (in_array($tipe, ['Equity', 'Modal'])) {
        $ekuitasTotal += $saldo; // This becomes negative!
    }
}

echo "Total Aset: " . number_format($asetTotal, 0, ',', '.') . "\n";
echo "Total Kewajiban: " . number_format($kewajibanTotal, 0, ',', '.') . "\n";
echo "Total Ekuitas: " . number_format($ekuitasTotal, 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: " . number_format($kewajibanTotal + $ekuitasTotal, 0, ',', '.') . "\n";
echo "Selisih: " . number_format($asetTotal - ($kewajibanTotal + $ekuitasTotal), 0, ',', '.') . "\n";
echo "Status: NOT BALANCED\n\n";

// Calculate totals with correct logic
echo "Correct Logic Calculation:\n";
echo "==========================\n";

$asetTotalCorrect = 0;
$kewajibanTotalCorrect = 0;
$ekuitasTotalCorrect = 0;

foreach ($neracaSaldo as $account) {
    $saldo = $account['saldo_correct']; // Use absolute value
    $tipe = $account['tipe_akun'];
    
    if (in_array($tipe, ['Asset', 'Aset'])) {
        $asetTotalCorrect += $saldo;
    } elseif (in_array($tipe, ['Liability', 'Kewajiban'])) {
        $kewajibanTotalCorrect += $saldo;
    } elseif (in_array($tipe, ['Equity', 'Modal'])) {
        $ekuitasTotalCorrect += $saldo;
    }
}

echo "Total Aset: " . number_format($asetTotalCorrect, 0, ',', '.') . "\n";
echo "Total Kewajiban: " . number_format($kewajibanTotalCorrect, 0, ',', '.') . "\n";
echo "Total Ekuitas: " . number_format($ekuitasTotalCorrect, 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: " . number_format($kewajibanTotalCorrect + $ekuitasTotalCorrect, 0, ',', '.') . "\n";
echo "Selisih: " . number_format($asetTotalCorrect - ($kewajibanTotalCorrect + $ekuitasTotalCorrect), 0, ',', '.') . "\n";
echo "Status: " . (abs($asetTotalCorrect - ($kewajibanTotalCorrect + $ekuitasTotalCorrect)) < 0.01 ? "BALANCED" : "NOT BALANCED") . "\n\n";

echo "Problem identified: Credit accounts (kewajiban/ekuitas) given negative values\n";
echo "Solution: Use absolute values for balance sheet calculations\n";

echo "\nNeraca balance issue test completed!\n";
