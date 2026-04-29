<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Check Authentication Context...\n";

echo "Current Auth ID: " . (auth()->check() ? auth()->id() : 'NOT AUTHENTICATED') . "\n";
echo "Current Auth User: " . (auth()->check() ? auth()->user()->name : 'NOT AUTHENTICATED') . "\n";

// Check if we can manually set auth context
if (!auth()->check()) {
    echo "Not authenticated, trying to authenticate as user 6...\n";
    
    $user = \App\Models\User::find(6);
    if ($user) {
        auth()->login($user);
        echo "Logged in as user 6: {$user->name}\n";
        echo "New Auth ID: " . auth()->id() . "\n";
    } else {
        echo "User 6 not found\n";
    }
}

// Now test TrialBalanceService again
echo "\n=== TEST TRIAL BALANCE WITH AUTH CONTEXT ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "Cash Accounts:\n";
foreach ($trialBalance['accounts'] as $account) {
    if ($account['kode_akun'] == '111' || $account['kode_akun'] == '112') {
        echo "- {$account['kode_akun']}: {$account['nama_akun']} - D: {$account['debit']}, K: {$account['kredit']}\n";
    }
}

// Calculate totals
$totalDebit = array_sum(array_column($trialBalance['accounts'], 'debit'));
$totalKredit = array_sum(array_column($trialBalance['accounts'], 'kredit'));

echo "\nTotals:\n";
echo "Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalDebit - $totalKredit), 0, ',', '.') . "\n";
echo "Status: " . ($totalDebit == $totalKredit ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";
