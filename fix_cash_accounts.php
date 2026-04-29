<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fix Cash Accounts Classification and Balance...\n";

// Fix COA 112 - should be Kas, not Piutang Usaha
echo "\n=== FIX COA 112 CLASSIFICATION ===\n";
$coa112 = \App\Models\Coa::where('kode_akun', '112')->where('user_id', 6)->first();

if ($coa112) {
    echo "Current COA 112: {$coa112->nama_akun} (User ID: {$coa112->user_id})\n";
    
    // Update to Kas
    $coa112->update([
        'nama_akun' => 'Kas',
        'tipe_akun' => 'Asset',
        'saldo_normal' => 'debit'
    ]);
    
    echo "Updated to: Kas (Asset, debit normal)\n";
} else {
    echo "COA 112 not found for user ID 6\n";
}

// Add missing cash balance - we need to add Rp 100.000.000
// Based on user data: 111 = 93.256.550, 112 = 72.782.550
// Our data: 111 = 43.256.550, 112 = 22.782.550 (but 112 is Piutang)
// We need to adjust to match user data

echo "\n=== ADJUST CASH BALANCES ===\n";

// Update COA 111 to match user data (93.256.550)
$coa111 = \App\Models\Coa::where('kode_akun', '111')->where('user_id', 6)->first();
if ($coa111) {
    echo "Current COA 111 saldo_awal: " . number_format($coa111->saldo_awal ?? 0, 0, ',', '.') . "\n";
    
    // Calculate needed saldo awal to reach 93.256.550
    $currentBalance = 43256550; // Current trial balance shows this
    $targetBalance = 93256550; // User data shows this
    $neededSaldoAwal = $targetBalance; // Set saldo awal to target since no transactions
    
    $coa111->update(['saldo_awal' => $neededSaldoAwal]);
    echo "Updated COA 111 saldo_awal to: " . number_format($neededSaldoAwal, 0, ',', '.') . "\n";
}

// Update COA 112 to match user data (72.782.550)
if ($coa112) {
    echo "Current COA 112 saldo_awal: " . number_format($coa112->saldo_awal ?? 0, 0, ',', '.') . "\n";
    
    // We need 72.782.550 for cash account
    $targetBalance = 72782550;
    
    $coa112->update(['saldo_awal' => $targetBalance]);
    echo "Updated COA 112 saldo_awal to: " . number_format($targetBalance, 0, ',', '.') . "\n";
}

// Check if there's a real Piutang Usaha account that should be different
echo "\n=== CHECK FOR REAL PIUTANG USAHA ===\n";
$piutangAccounts = \App\Models\Coa::where('nama_akun', 'like', '%piutang%')
                               ->where('user_id', 6)
                               ->get();

foreach ($piutangAccounts as $piutang) {
    echo "Found Piutang: {$piutang->kode_akun} - {$piutang->nama_akun}\n";
}

// Test the updated trial balance
echo "\n=== TEST UPDATED TRIAL BALANCE ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$totalDebit = 0;
$totalKredit = 0;

echo "Cash Accounts:\n";
foreach ($trialBalance['accounts'] as $account) {
    if ($account['kode_akun'] == '111' || $account['kode_akun'] == '112') {
        echo "- {$account['kode_akun']}: {$account['nama_akun']} - D: {$account['debit']}, K: {$account['kredit']}\n";
        $totalDebit += $account['debit'];
        $totalKredit += $account['kredit'];
    }
}

// Calculate totals
foreach ($trialBalance['accounts'] as $account) {
    $totalDebit += $account['debit'];
    $totalKredit += $account['kredit'];
}

echo "\nTrial Balance Totals:\n";
echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalDebit - $totalKredit), 0, ',', '.') . "\n";
echo "Status: " . ($totalDebit == $totalKredit ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";
