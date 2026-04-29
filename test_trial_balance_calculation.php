<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Trial Balance Calculation...\n\n";

$tanggalAwal = '2026-04-01';
$tanggalAkhir = '2026-04-30';

// Get trial balance data
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalanceData = $trialBalanceService->calculateTrialBalance($tanggalAwal, $tanggalAkhir);

echo "Trial Balance Results:\n";
echo "====================\n";
echo "Total Debit: " . number_format($trialBalanceData['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: " . number_format($trialBalanceData['total_kredit'], 0, ',', '.') . "\n";
echo "Balance: " . ($trialBalanceData['is_balanced'] ? "BALANCED" : "NOT BALANCED") . "\n";
echo "Selisih: " . number_format($trialBalanceData['total_debit'] - $trialBalanceData['total_kredit'], 0, ',', '.') . "\n\n";

echo "Account Details:\n";
echo "================\n";

foreach ($trialBalanceData['accounts'] as $account) {
    echo "COA: {$account['kode_akun']} - {$account['nama_akun']}\n";
    echo "  Tipe: {$account['tipe_akun']}\n";
    echo "  Debit: " . number_format($account['debit'], 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($account['kredit'], 0, ',', '.') . "\n";
    
    // Simulate NeracaService logic
    $saldo = 0;
    if ($account['debit'] > 0) {
        $saldo = $account['debit']; // Normal debit
    } elseif ($account['kredit'] > 0) {
        $saldo = -$account['kredit']; // Negative for credit accounts
    }
    
    echo "  Saldo (Neraca Logic): " . number_format($saldo, 0, ',', '.') . "\n";
    echo "\n";
}

// Calculate balance sheet totals with current logic
echo "Balance Sheet Calculation (Current Logic):\n";
echo "==========================================\n";

$asetTotal = 0;
$kewajibanTotal = 0;
$ekuitasTotal = 0;

foreach ($trialBalanceData['accounts'] as $account) {
    $saldo = 0;
    if ($account['debit'] > 0) {
        $saldo = $account['debit'];
    } elseif ($account['kredit'] > 0) {
        $saldo = -$account['kredit']; // This is the problem!
    }
    
    $tipe = $account['tipe_akun'];
    
    if (in_array($tipe, ['Asset', 'Aset'])) {
        $asetTotal += $saldo;
    } elseif (in_array($tipe, ['Liability', 'Kewajiban'])) {
        $kewajibanTotal += $saldo; // Becomes negative!
    } elseif (in_array($tipe, ['Equity', 'Modal'])) {
        $ekuitasTotal += $saldo; // Becomes negative!
    }
}

echo "Total Aset: " . number_format($asetTotal, 0, ',', '.') . "\n";
echo "Total Kewajiban: " . number_format($kewajibanTotal, 0, ',', '.') . "\n";
echo "Total Ekuitas: " . number_format($ekuitasTotal, 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: " . number_format($kewajibanTotal + $ekuitasTotal, 0, ',', '.') . "\n";
echo "Selisih: " . number_format($asetTotal - ($kewajibanTotal + $ekuitasTotal), 0, ',', '.') . "\n";
echo "Status: " . (abs($asetTotal - ($kewajibanTotal + $ekuitasTotal)) < 0.01 ? "BALANCED" : "NOT BALANCED") . "\n\n";

// Calculate balance sheet totals with correct logic
echo "Balance Sheet Calculation (Correct Logic):\n";
echo "========================================\n";

$asetTotalCorrect = 0;
$kewajibanTotalCorrect = 0;
$ekuitasTotalCorrect = 0;

foreach ($trialBalanceData['accounts'] as $account) {
    $saldo = 0;
    if ($account['debit'] > 0) {
        $saldo = $account['debit'];
    } elseif ($account['kredit'] > 0) {
        $saldo = $account['kredit']; // Use positive value for balance sheet
    }
    
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

echo "Trial balance calculation test completed!\n";
