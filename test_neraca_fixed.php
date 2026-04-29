<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Neraca with Fixed TrialBalance...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

$tanggalAwal = '2026-04-01';
$tanggalAkhir = '2026-04-30';

// Get trial balance data (now fixed)
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalanceData = $trialBalanceService->calculateTrialBalance($tanggalAwal, $tanggalAkhir);

echo "Trial Balance Results:\n";
echo "====================\n";
echo "Total Debit: " . number_format($trialBalanceData['total_debit'], 0, ',', '.') . "\n";
echo "Total Kredit: " . number_format($trialBalanceData['total_kredit'], 0, ',', '.') . "\n";
echo "Balance: " . ($trialBalanceData['is_balanced'] ? "BALANCED" : "NOT BALANCED") . "\n";
echo "Account Count: " . count($trialBalanceData['accounts']) . "\n\n";

echo "Account Details:\n";
echo "================\n";

foreach ($trialBalanceData['accounts'] as $account) {
    echo "COA: {$account['kode_akun']} - {$account['nama_akun']}\n";
    echo "  Tipe: {$account['tipe_akun']}\n";
    echo "  Debit: " . number_format($account['debit'], 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($account['kredit'], 0, ',', '.') . "\n";
    
    // Current NeracaService logic (WRONG)
    $saldoWrong = 0;
    if ($account['debit'] > 0) {
        $saldoWrong = $account['debit'];
    } elseif ($account['kredit'] > 0) {
        $saldoWrong = -$account['kredit']; // Negative for credit accounts
    }
    
    // Correct logic for balance sheet
    $saldoCorrect = abs($saldoWrong);
    
    echo "  Saldo (Current): " . number_format($saldoWrong, 0, ',', '.') . "\n";
    echo "  Saldo (Correct): " . number_format($saldoCorrect, 0, ',', '.') . "\n";
    echo "\n";
}

// Calculate balance sheet with current logic (WRONG)
echo "Balance Sheet - Current Logic (WRONG):\n";
echo "=====================================\n";

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

// Calculate balance sheet with correct logic
echo "Balance Sheet - Correct Logic:\n";
echo "==============================\n";

$asetTotalCorrect = 0;
$kewajibanTotalCorrect = 0;
$ekuitasTotalCorrect = 0;

foreach ($trialBalanceData['accounts'] as $account) {
    $saldo = 0;
    if ($account['debit'] > 0) {
        $saldo = $account['debit'];
    } elseif ($account['kredit'] > 0) {
        $saldo = $account['kredit']; // Use positive value
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

echo "SOLUTION:\n";
echo "=========\n";
echo "Fix NeracaService line 92: Change\n";
echo "  \$saldo = -\$account['kredit'];\n";
echo "To:\n";
echo "  \$saldo = \$account['kredit'];\n";

echo "\nNeraca fixed test completed!\n";
