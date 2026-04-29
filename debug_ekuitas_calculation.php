<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debug Ekuitas Calculation...\n";

// Authenticate as user 6
$user = \App\Models\User::find(6);
if ($user) {
    auth()->login($user);
    echo "Authenticated as user 6: {$user->name}\n";
}

// Check COA 310 directly
echo "\n=== CHECK COA 310 DIRECTLY ===\n";
$coa310 = \App\Models\Coa::where('kode_akun', '310')
                          ->where('user_id', 6)
                          ->first();

if ($coa310) {
    echo "COA 310: {$coa310->nama_akun}\n";
    echo "Saldo Awal: Rp " . number_format($coa310->saldo_awal ?? 0, 0, ',', '.') . "\n";
    echo "Tipe Akun: {$coa310->tipe_akun}\n";
    echo "Saldo Normal: {$coa310->saldo_normal}\n";
} else {
    echo "COA 310 not found\n";
}

// Check what NeracaService is actually using
echo "\n=== DEBUG NERACA SERVICE EKUITAS ===\n";
$neracaService = app(\App\Services\NeracaService::class);

// Use reflection to debug the calculateEkuitas method
$reflection = new \ReflectionClass($neracaService);
$method = $reflection->getMethod('calculateEkuitas');
$method->setAccessible(true);

// Get neraca saldo data first
$getNeracaSaldoMethod = $reflection->getMethod('getNeracaSaldo');
$getNeracaSaldoMethod->setAccessible(true);

$neracaSaldo = $getNeracaSaldoMethod->invoke(
    $neracaService,
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "NeracaSaldo data for equity accounts:\n";
foreach ($neracaSaldo as $item) {
    $firstDigit = substr($item['kode_akun'], 0, 1);
    if ($firstDigit == '3') {
        echo "- {$item['kode_akun']}: {$item['nama_akun']} - Saldo: {$item['saldo']}, Kredit: {$item['kredit']}\n";
    }
}

// Now call calculateEkuitas
$ekuitasResult = $method->invoke($neracaService, $neracaSaldo);

echo "\nEkuitas calculation result:\n";
$totalEkuitas = 0;
foreach ($ekuitasResult as $ekuitas) {
    echo "- {$ekuitas['kode_akun']}: {$ekuitas['nama_akun']} = Rp " . number_format($ekuitas['saldo'], 0, ',', '.') . "\n";
    $totalEkuitas += $ekuitas['saldo'];
}
echo "Total Ekuitas: Rp " . number_format($totalEkuitas, 0, ',', '.') . "\n";

// Check calculateLabaRugi result
$calculateLabaRugiMethod = $reflection->getMethod('calculateLabaRugi');
$calculateLabaRugiMethod->setAccessible(true);

$labaRugi = $calculateLabaRugiMethod->invoke($neracaService, $neracaSaldo);
echo "\nLaba/Rugi calculation result: Rp " . number_format($labaRugi, 0, ',', '.') . "\n";

// Test the full balance sheet
echo "\n=== FULL BALANCE SHEET TEST ===\n";
$neraca = $neracaService->generateLaporanPosisiKeuangan(
    now()->startOfMonth()->format('Y-m-d'),
    now()->endOfMonth()->format('Y-m-d')
);

echo "Balance Sheet Result:\n";
echo "Total Aset: Rp " . number_format($neraca['aset']['total_aset'], 0, ',', '.') . "\n";
echo "Total Kewajiban + Ekuitas: Rp " . number_format($neraca['total_kewajiban_ekuitas'], 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format($neraca['selisih'], 0, ',', '.') . "\n";
echo "Status: " . ($neraca['neraca_seimbang'] ? 'SEIMBANG' : 'TIDAK SEIMBANG') . "\n";

// Show ekuitas detail from balance sheet
echo "\nEkuitas Detail from Balance Sheet:\n";
foreach ($neraca['ekuitas']['detail'] as $ekuitas) {
    echo "- {$ekuitas['kode_akun']}: {$ekuitas['nama_akun']} = Rp " . number_format($ekuitas['saldo'], 0, ',', '.') . "\n";
}
