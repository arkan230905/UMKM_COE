<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Test Final Balance After User Input Fix...\n";

// Authenticate as user 6
$user = \App\Models\User::find(6);
if ($user) {
    auth()->login($user);
    echo "Authenticated as user 6: {$user->name}\n";
}

// Check trial balance
echo "\n=== TRIAL BALANCE STATUS ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$totalDebit = array_sum(array_column($trialBalance['accounts'], 'debit'));
$totalKredit = array_sum(array_column($trialBalance['accounts'], 'kredit'));
$selisih = $totalDebit - $totalKredit;

echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($selisih, 0, ',', '.') . "\n";
echo "Status: " . ($selisih == 0 ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";

// Check cash accounts
echo "\nCash Accounts:\n";
foreach ($trialBalance['accounts'] as $account) {
    if ($account['kode_akun'] == '111' || $account['kode_akun'] == '112') {
        echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($account['debit'], 0, ',', '.') . "\n";
    }
}

// Check balance sheet
echo "\n=== BALANCE SHEET STATUS ===\n";
$neracaService = app(\App\Services\NeracaService::class);
$neraca = $neracaService->generateLaporanPosisiKeuangan(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "Total Aset: Rp " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n";
echo "Total Kewajiban: Rp " . number_format($neraca['kewajiban']['total'], 0, ',', '.') . "\n";
echo "Total Ekuitas: Rp " . number_format($neraca['ekuitas']['total'], 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: Rp " . number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($neraca['selisih'], 0, ',', '.') . "\n";
echo "Status: " . ($neraca['neraca_seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";

// Show ekuitas detail
echo "\nEkuitas Detail:\n";
foreach ($neraca['ekuitas']['detail'] as $ekuitas) {
    echo "- {$ekuitas['kode_akun']}: {$ekuitas['nama_akun']} = Rp " . number_format($ekuitas['saldo'], 0, ',', '.') . "\n";
}

// Summary
echo "\n=== SUMMARY ===\n";
echo "User Input Saldo Awal:\n";
echo "- Kas Bank: Rp 100.000.000\n";
echo "- Kas: Rp 75.000.000\n";
echo "- Total: Rp 175.000.000\n";

echo "\nActual Cash in Trial Balance:\n";
echo "- Kas Bank: Rp " . number_format($trialBalance['accounts'][0]['debit'] ?? 0, 0, ',', '.') . "\n";
echo "- Kas: Rp " . number_format($trialBalance['accounts'][1]['debit'] ?? 0, 0, ',', '.') . "\n";

echo "\nBalance Status:\n";
echo "- Neraca Saldo: " . ($selisih == 0 ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";
echo "- Laporan Posisi Keuangan: " . ($neraca['neraca_seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";

if ($selisih == 0 && $neraca['neraca_seimbang']) {
    echo "\nSUCCESS: Both reports are now BALANCED!\n";
} else {
    echo "\nStill need adjustments...\n";
}
