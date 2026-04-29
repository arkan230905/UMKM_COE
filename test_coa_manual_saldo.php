<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing COA Manual Saldo Display...\n\n";

// Get all COA for user
$coas = \App\Models\Coa::where('user_id', 1)
    ->whereNotNull('nama_akun')
    ->where('nama_akun', '!=', '')
    ->orderBy('kode_akun')
    ->get();

echo "Found {$coas->count()} COA entries\n\n";

// Simulate controller logic (manual only)
$saldoPeriode = [];
$posisiAkun = [];

foreach ($coas as $coa) {
    $saldoAwal = $coa->saldo_awal ?? 0;
    $saldoPeriode[$coa->id] = $saldoAwal;
    
    $firstDigit = substr($coa->kode_akun, 0, 1);
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    $posisiAkun[$coa->id] = $isDebitNormal ? 'Debit' : 'Kredit';
}

echo "Manual Saldo Results:\n";
echo "====================\n";

// Show key accounts including Modal
$keyAccounts = ['11', '31', '310'];
foreach ($keyAccounts as $kode) {
    $coa = $coas->where('kode_akun', $kode)->first();
    if ($coa) {
        $saldo = $saldoPeriode[$coa->id] ?? 0;
        echo "COA {$kode} - {$coa->nama_akun}: " . number_format($saldo, 0, ',', '.') . " (MANUAL)\n";
    } else {
        echo "COA {$kode}: NOT FOUND\n";
    }
}

echo "\n";

// Test view logic simulation (manual only)
echo "View Logic Simulation (Manual Only):\n";
echo "=====================================\n";

foreach ($coas->take(10) as $coa) {
    $saldo = $saldoPeriode[$coa->id] ?? 0;
    
    echo "COA: {$coa->kode_akun} - {$coa->nama_akun}\n";
    
    // Manual display logic (no automatic totals)
    if ($saldo == floor($saldo)) {
        echo "  Display: " . number_format($saldo, 0, ',', '.') . " (MANUAL)\n";
    } else {
        echo "  Display: " . number_format($saldo, 2, ',', '.') . " (MANUAL)\n";
    }
    echo "\n";
}

echo "Expected Results:\n";
echo "================\n";
echo "Aset (11): Shows manual saldo (user input)\n";
echo "Modal (31): Shows manual saldo (user input)\n";
echo "Modal Usaha (310): Shows manual saldo (user input)\n";
echo "All accounts: Show manual saldo only (no automatic totals)\n";

echo "\nCOA manual saldo test completed!\n";
