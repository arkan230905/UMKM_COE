<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fix Remaining Balance Sheet Selisih Rp 14.059.752...\n";

// Authenticate as user 6
$user = \App\Models\User::find(6);
if ($user) {
    auth()->login($user);
    echo "Authenticated as user 6: {$user->name}\n";
}

// Get current balance sheet
$neracaService = app(\App\Services\NeracaService::class);
$neraca = $neracaService->generateLaporanPosisiKeuangan(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "\nCurrent Balance Sheet Status:\n";
echo "Total Aset: Rp " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: Rp " . number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($neraca['selisih'], 0, ',', '.') . "\n";

// Manual calculation to find the exact issue
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "\n=== MANUAL CALCULATION ===\n";

// Calculate assets (1xx)
$totalAssets = 0;
echo "Assets (1xx):\n";
foreach ($trialBalance['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    if ($firstDigit == '1') {
        $balance = $account['debit'] > 0 ? $account['debit'] : -$account['kredit'];
        if ($account['debit'] > 0 || $account['kredit'] > 0) {
            echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($balance, 0, ',', '.') . "\n";
        }
        $totalAssets += $balance;
    }
}
echo "Total Assets: Rp " . number_format($totalAssets, 0, ',', '.') . "\n\n";

// Calculate liabilities (2xx)
$totalLiabilities = 0;
echo "Liabilities (2xx):\n";
foreach ($trialBalance['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    if ($firstDigit == '2') {
        $balance = $account['kredit'];
        if ($account['kredit'] > 0) {
            echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($balance, 0, ',', '.') . "\n";
        }
        $totalLiabilities += $balance;
    }
}
echo "Total Liabilities: Rp " . number_format($totalLiabilities, 0, ',', '.') . "\n\n";

// Calculate equity (3xx)
$totalEquity = 0;
echo "Equity (3xx):\n";
foreach ($trialBalance['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    if ($firstDigit == '3') {
        $balance = $account['kredit'];
        if ($account['kredit'] > 0) {
            echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($balance, 0, ',', '.') . "\n";
        }
        $totalEquity += $balance;
    }
}

// Calculate profit/loss
$pendapatan = 0;
$biaya = 0;

echo "\nProfit/Loss Calculation:\n";
foreach ($trialBalance['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    if ($firstDigit == '4') {
        $pendapatan += $account['kredit'];
        if ($account['kredit'] > 0) {
            echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($account['kredit'], 0, ',', '.') . " (Pendapatan)\n";
        }
    } elseif ($firstDigit == '5') {
        $biaya += $account['debit'];
        if ($account['debit'] > 0) {
            echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($account['debit'], 0, ',', '.') . " (Beban)\n";
        }
    }
}

$labaRugi = $pendapatan - $biaya;
echo "\nPendapatan Total: Rp " . number_format($pendapatan, 0, ',', '.') . "\n";
echo "Beban Total: Rp " . number_format($biaya, 0, ',', '.') . "\n";
echo "Laba/Rugi: Rp " . number_format($labaRugi, 0, ',', '.') . " (" . ($labaRugi >= 0 ? 'Laba' : 'Rugi') . ")\n";

$totalEquityWithProfit = $totalEquity + $labaRugi;
echo "Total Equity (with profit): Rp " . number_format($totalEquityWithProfit, 0, ',', '.') . "\n\n";

// Compare with NeracaService
echo "=== COMPARISON ===\n";
echo "Manual Calculation:\n";
echo "- Total Assets: Rp " . number_format($totalAssets, 0, ',', '.') . "\n";
echo "- Total Liabilities + Equity: Rp " . number_format($totalLiabilities + $totalEquityWithProfit, 0, ',', '.') . "\n";

$manualSelisih = $totalAssets - ($totalLiabilities + $totalEquityWithProfit);
echo "- Manual Selisih: Rp " . number_format($manualSelisih, 0, ',', '.') . "\n\n";

echo "NeracaService:\n";
echo "- Total Assets: Rp " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n";
echo "- Total Liabilities + Equity: Rp " . number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') . "\n";
echo "- NeracaService Selisih: Rp " . number_format($neraca['selisih'], 0, ',', '.') . "\n\n";

$difference = abs($neraca['selisih'] - $manualSelisih);
echo "Difference between calculations: Rp " . number_format($difference, 0, ',', '.') . "\n";

if ($difference == 0) {
    echo "Both calculations produce the same result\n";
} else {
    echo "There's a difference in calculation methods\n";
}
