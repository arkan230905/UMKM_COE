<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Investigate Balance Issues...\n";

// Check current COA structure for cash accounts
echo "\n=== CASH ACCOUNTS INVESTIGATION ===\n";
$cashAccounts = \App\Models\Coa::where(function($query) {
    $query->where('kode_akun', '111')
          ->orWhere('kode_akun', '112');
})->get();

foreach ($cashAccounts as $coa) {
    echo "COA {$coa->kode_akun}: {$coa->nama_akun} (User ID: {$coa->user_id}, Tipe: {$coa->tipe_akun})\n";
}

// Check trial balance data
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "\n=== TRIAL BALANCE CASH ACCOUNTS ===\n";
$cashInTrialBalance = [];
foreach ($trialBalance['accounts'] as $account) {
    if ($account['kode_akun'] == '111' || $account['kode_akun'] == '112') {
        $cashInTrialBalance[] = $account;
        echo "{$account['kode_akun']}: {$account['nama_akun']} - Debit: {$account['debit']}, Kredit: {$account['kredit']}\n";
        echo "  Saldo Awal: " . number_format($account['saldo_awal'] ?? 0, 0, ',', '.') . "\n";
        echo "  Mutasi Debit: " . number_format($account['mutasi_debit'] ?? 0, 0, ',', '.') . "\n";
        echo "  Mutasi Kredit: " . number_format($account['mutasi_kredit'] ?? 0, 0, ',', '.') . "\n\n";
    }
}

// Manual calculation from user's data
echo "\n=== MANUAL CALCULATION FROM USER DATA ===\n";

// From user's neraca saldo data
$userDebitTotal = 395767875;
$userKreditTotal = 295767875;
$userSelisih = 100000000;

echo "User Data:\n";
echo "Total Debit: Rp " . number_format($userDebitTotal, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($userKreditTotal, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($userSelisih, 0, ',', '.') . "\n\n";

// Check what's causing the 100jt difference
echo "=== ANALYZING 100JT DIFFERENCE ===\n";

// The difference might be in cash accounts
$totalCashFromUserData = 93256550 + 72782550; // 111 + 112 from user data
echo "Total Cash from User Data: Rp " . number_format($totalCashFromUserData, 0, ',', '.') . "\n";

// Compare with our trial balance
$ourCashTotal = 0;
foreach ($cashInTrialBalance as $account) {
    $ourCashTotal += $account['debit'];
}
echo "Our Trial Balance Cash Total: Rp " . number_format($ourCashTotal, 0, ',', '.') . "\n";

$cashDifference = $totalCashFromUserData - $ourCashTotal;
echo "Cash Difference: Rp " . number_format($cashDifference, 0, ',', '.') . "\n";

// Check if there are missing accounts or amounts
echo "\n=== CHECKING FOR MISSING ACCOUNTS ===\n";

// Look for accounts that should exist but don't in our calculation
$expectedAccounts = [
    '111' => 'Kas Bank',
    '112' => 'Kas',
    '210' => 'Hutang Usaha',
    '211' => 'Hutang Gaji',
    '212' => 'PPN Keluaran',
    '310' => 'Modal Usaha',
    '41' => 'Penjualan',
    '43' => 'Pendapatan Lain Lainnya',
];

foreach ($expectedAccounts as $kode => $nama) {
    $found = false;
    foreach ($trialBalance['accounts'] as $account) {
        if ($account['kode_akun'] == $kode) {
            $found = true;
            echo "Found {$kode}: {$account['nama_akun']} - D: {$account['debit']}, K: {$account['kredit']}\n";
            break;
        }
    }
    if (!$found) {
        echo "MISSING {$kode}: {$nama}\n";
    }
}
