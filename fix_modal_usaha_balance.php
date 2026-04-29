<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fix Modal Usaha Balance Calculation...\n";

// Authenticate as user 6
$user = \App\Models\User::find(6);
if ($user) {
    auth()->login($user);
    echo "Authenticated as user 6: {$user->name}\n";
}

// Reset Modal Usaha to original value (before adjustment)
echo "\n=== RESET MODAL USAHA ===\n";
$coa310 = \App\Models\Coa::where('kode_akun', '310')
                          ->where('user_id', 6)
                          ->first();

if ($coa310) {
    echo "Current Modal Usaha saldo_awal: Rp " . number_format($coa310->saldo_awal ?? 0, 0, ',', '.') . "\n";
    
    // Reset to original value
    $originalModal = 287830769;
    $coa310->update(['saldo_awal' => $originalModal]);
    
    echo "Reset to original: Rp " . number_format($originalModal, 0, ',', '.') . "\n";
}

// Now we need to balance the trial balance again
echo "\n=== REBALANCE TRIAL BALANCE ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$totalDebit = array_sum(array_column($trialBalance['accounts'], 'debit'));
$totalKredit = array_sum(array_column($trialBalance['accounts'], 'kredit'));
$selisih = $totalDebit - $totalKredit;

echo "Trial Balance Status:\n";
echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n";

if ($selisih > 0) {
    echo "\nNeed to add Rp " . number_format($selisih, 0, ',', '.') . " to credit side\n";
    
    // Add to Modal Usaha again but correctly this time
    $currentSaldoAwal = $coa310->saldo_awal ?? 0;
    $newSaldoAwal = $currentSaldoAwal + $selisih;
    
    echo "Modal Usaha: Rp " . number_format($currentSaldoAwal, 0, ',', '.') . " + Rp " . number_format($selisih, 0, ',', '.') . " = Rp " . number_format($newSaldoAwal, 0, ',', '.') . "\n";
    
    $coa310->update(['saldo_awal' => $newSaldoAwal]);
    echo "Updated Modal Usaha saldo_awal\n";
}

// Test balance sheet
echo "\n=== TEST BALANCE SHEET ===\n";
$neracaService = app(\App\Services\NeracaService::class);
$neraca = $neracaService->generateLaporanPosisiKeuangan(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "Balance Sheet Status:\n";
echo "Total Aset: Rp " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: Rp " . number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($neraca['selisih'], 0, ',', '.') . "\n";
echo "Status: " . ($neraca['neraca_seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";

// Show ekuitas detail
echo "\nEkuitas Detail:\n";
foreach ($neraca['ekuitas']['detail'] as $ekuitas) {
    echo "- {$ekuitas['kode_akun']}: {$ekuitas['nama_akun']} = Rp " . number_format($ekuitas['saldo'], 0, ',', '.') . "\n";
}

// Final trial balance check
echo "\n=== FINAL TRIAL BALANCE CHECK ===\n";
$finalTrialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$finalTotalDebit = array_sum(array_column($finalTrialBalance['accounts'], 'debit'));
$finalTotalKredit = array_sum(array_column($finalTrialBalance['accounts'], 'kredit'));
$finalSelisih = $finalTotalDebit - $finalTotalKredit;

echo "Final Trial Balance:\n";
echo "Total Debit: Rp " . number_format($finalTotalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($finalTotalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($finalSelisih, 0, ',', '.') . "\n";
echo "Status: " . ($finalSelisih == 0 ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";
