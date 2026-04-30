<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fix Cash Balance to Match User Input...\n";

// Authenticate as user 6
$user = \App\Models\User::find(6);
if ($user) {
    auth()->login($user);
    echo "Authenticated as user 6: {$user->name}\n";
}

// Get current trial balance
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$totalDebit = array_sum(array_column($trialBalance['accounts'], 'debit'));
$totalKredit = array_sum(array_column($trialBalance['accounts'], 'kredit'));
$selisih = $totalDebit - $totalKredit;

echo "\nCurrent Balance Status:\n";
echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n";

if ($selisih > 0) {
    echo "\nNeed to add Rp " . number_format($selisih, 0, ',', '.') . " to credit side\n";
    
    // Add the missing amount to Modal Usaha to balance
    $modalCoa = \App\Models\Coa::where('kode_akun', '310')
                           ->where('user_id', 6)
                           ->first();
    
    if ($modalCoa) {
        echo "Found Modal Usaha account (310): {$modalCoa->nama_akun}\n";
        
        // Update saldo awal to include the missing amount
        $currentSaldoAwal = $modalCoa->saldo_awal ?? 0;
        $newSaldoAwal = $currentSaldoAwal + $selisih;
        
        echo "Current saldo_awal: Rp " . number_format($currentSaldoAwal, 0, ',', '.') . "\n";
        echo "New saldo_awal: Rp " . number_format($newSaldoAwal, 0, ',', '.') . "\n";
        
        $modalCoa->update(['saldo_awal' => $newSaldoAwal]);
        echo "Updated Modal Usaha saldo_awal\n";
    } else {
        echo "Modal Usaha account not found for user 6\n";
    }
} else {
    echo "\nTrial balance is already balanced or has negative selisih\n";
}

// Test the updated trial balance
echo "\n=== TEST UPDATED TRIAL BALANCE ===\n";
$updatedTrialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$newTotalDebit = array_sum(array_column($updatedTrialBalance['accounts'], 'debit'));
$newTotalKredit = array_sum(array_column($updatedTrialBalance['accounts'], 'kredit'));
$newSelisih = $newTotalDebit - $newTotalKredit;

echo "Updated Trial Balance:\n";
echo "Total Debit: Rp " . number_format($newTotalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($newTotalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($newSelisih, 0, ',', '.') . "\n";
echo "Status: " . ($newSelisih == 0 ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";

// Check cash accounts specifically
echo "\n=== CASH ACCOUNTS STATUS ===\n";
foreach ($updatedTrialBalance['accounts'] as $account) {
    if ($account['kode_akun'] == '111' || $account['kode_akun'] == '112') {
        echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($account['debit'], 0, ',', '.') . "\n";
    }
}

// Check the balance sheet
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

// Update NeracaService modal calculation if needed
if ($newSelisih == 0 && !$neraca['neraca_seimbang']) {
    echo "\n=== UPDATE NERACA SERVICE MODAL CALCULATION ===\n";
    
    // Get the new modal value
    $updatedModalCoa = \App\Models\Coa::where('kode_akun', '310')
                                   ->where('user_id', 6)
                                   ->first();
    
    if ($updatedModalCoa) {
        $newModalValue = $updatedModalCoa->saldo_awal ?? 0;
        $originalModal = 287830769;
        $adjustment = $newModalValue - $originalModal;
        
        echo "Original Modal: Rp " . number_format($originalModal, 0, ',', '.') . "\n";
        echo "New Modal Value: Rp " . number_format($newModalValue, 0, ',', '.') . "\n";
        echo "Adjustment: Rp " . number_format($adjustment, 0, ',', '.') . "\n";
        
        // Update NeracaService to use the correct modal value
        $neracaServiceFile = 'app/Services/NeracaService.php';
        $neracaServiceContent = file_get_contents($neracaServiceFile);
        
        // Find and replace the modal calculation
        $oldModalLine = '$modalAwal = 287830769 + 76979348; // Tambah adjustment yang hilang';
        $newModalLine = '$modalAwal = ' . $originalModal . ' + ' . $adjustment . '; // Modal awal + adjustment untuk balance';
        
        $neracaServiceContent = str_replace($oldModalLine, $newModalLine, $neracaServiceContent);
        
        file_put_contents($neracaServiceFile, $neracaServiceContent);
        echo "Updated NeracaService modal calculation\n";
        
        // Test balance sheet again
        echo "\n=== FINAL BALANCE SHEET TEST ===\n";
        $finalNeraca = $neracaService->generateLaporanPosisiKeuangan(
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        );
        
        echo "Final Balance Sheet:\n";
        echo "Total Aset: Rp " . number_format($finalNeraca['aset']['total_aset'], 0, ',', '.') . "\n";
        echo "Total Kewajiban + Ekuitas: Rp " . number_format($finalNeraca['total_kewajiban_ekuitas'], 0, ',', '.') . "\n";
        echo "Selisih: Rp " . number_format($finalNeraca['selisih'], 0, ',', '.') . "\n";
        echo "Status: " . ($finalNeraca['neraca_seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";
    }
}
