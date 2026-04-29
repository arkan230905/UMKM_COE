<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debug Kewajiban Calculation...\n";

// Authenticate as user 6
$user = \App\Models\User::find(6);
if ($user) {
    auth()->login($user);
    echo "Authenticated as user 6: {$user->name}\n";
}

// Get neraca saldo data
$neracaService = app(\App\Services\NeracaService::class);
$reflection = new \ReflectionClass($neracaService);
$getNeracaSaldoMethod = $reflection->getMethod('getNeracaSaldo');
$getNeracaSaldoMethod->setAccessible(true);

$neracaSaldo = $getNeracaSaldoMethod->invoke(
    $neracaService,
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "\n=== NERACA SALDO DATA FOR KEWAJIBAN (2xx) ===\n";
$kewajibanFromNeracaSaldo = [];
foreach ($neracaSaldo as $item) {
    $firstDigit = substr($item['kode_akun'], 0, 1);
    if ($firstDigit == '2') {
        $kewajibanFromNeracaSaldo[] = $item;
        echo "- {$item['kode_akun']}: {$item['nama_akun']} - Saldo: {$item['saldo']}, Kredit: {$item['kredit']}\n";
    }
}

// Debug calculateKewajiban method
$calculateKewajibanMethod = $reflection->getMethod('calculateKewajiban');
$calculateKewajibanMethod->setAccessible(true);

$kewajibanResult = $calculateKewajibanMethod->invoke($neracaService, $neracaSaldo);

echo "\n=== CALCULATE KEWAJIBAN RESULT ===\n";
$totalKewajiban = 0;
foreach ($kewajibanResult as $kewajiban) {
    echo "- {$kewajiban['kode_akun']}: {$kewajiban['nama_akun']} = Rp " . number_format($kewajiban['saldo'], 0, ',', '.') . "\n";
    $totalKewajiban += $kewajiban['saldo'];
}
echo "Total Kewajiban: Rp " . number_format($totalKewajiban, 0, ',', '.') . "\n";

// Compare with trial balance
echo "\n=== COMPARE WITH TRIAL BALANCE ===\n";
$trialBalanceService = app(\App\Services\TrialBalanceService::class);
$trialBalance = $trialBalanceService->calculateTrialBalance(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

$kewajibanFromTrialBalance = 0;
echo "Kewajiban from Trial Balance:\n";
foreach ($trialBalance['accounts'] as $account) {
    $firstDigit = substr($account['kode_akun'], 0, 1);
    if ($firstDigit == '2') {
        $balance = $account['kredit'];
        echo "- {$account['kode_akun']}: {$account['nama_akun']} = Rp " . number_format($balance, 0, ',', '.') . "\n";
        $kewajibanFromTrialBalance += $balance;
    }
}
echo "Total from Trial Balance: Rp " . number_format($kewajibanFromTrialBalance, 0, ',', '.') . "\n";

// Check full balance sheet
echo "\n=== FULL BALANCE SHEET ===\n";
$neraca = $neracaService->generateLaporanPosisiKeuangan(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "Balance Sheet:\n";
echo "Total Aset: Rp " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n";
echo "Total Kewajiban: Rp " . number_format($neraca['kewajiban']['total'], 0, ',', '.') . "\n";
echo "Total Ekuitas: Rp " . number_format($neraca['ekuitas']['total'], 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: Rp " . number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($neraca['selisih'], 0, ',', '.') . "\n";
echo "Status: " . ($neraca['neraca_seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";
