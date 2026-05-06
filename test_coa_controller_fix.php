<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing COA Controller Header Totals Fix...\n\n";

// Simulate the controller logic
$coas = \App\Models\Coa::where('user_id', 1)
    ->whereNotNull('nama_akun')
    ->where('nama_akun', '!=', '')
    ->orderBy('kode_akun')
    ->get();

// Simulate saldo calculation
$saldoPeriode = [];
$posisiAkun = [];

foreach ($coas as $coa) {
    $saldoAwal = $coa->saldo_awal ?? 0;
    $saldoPeriode[$coa->id] = $saldoAwal;
    
    $firstDigit = substr($coa->kode_akun, 0, 1);
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    $posisiAkun[$coa->id] = $isDebitNormal ? 'Debit' : 'Kredit';
}

// Calculate header totals (controller logic)
$headerTotals = [];
$allCoaData = [];

foreach ($coas as $coa) {
    $allCoaData[$coa->kode_akun] = [
        'nama' => $coa->nama_akun,
        'saldo' => $saldoPeriode[$coa->id] ?? 0,
        'is_header' => (strlen($coa->kode_akun) <= 2) || (substr($coa->kode_akun, -1) == '0')
    ];
}

foreach ($allCoaData as $kode => $data) {
    if ($data['is_header']) {
        $total = $data['saldo'];
        
        foreach ($allCoaData as $childKode => $childData) {
            if ($childKode != $kode && strpos($childKode, $kode) === 0) {
                $total += $childData['saldo'];
            }
        }
        
        $headerTotals[$kode] = $total;
    }
}

echo "Controller Results:\n";
echo "==================\n";

// Show key header accounts
$keyHeaders = ['11', '31', '310'];
foreach ($keyHeaders as $kode) {
    if (isset($headerTotals[$kode])) {
        echo "Header {$kode}: " . number_format($headerTotals[$kode], 0, ',', '.') . "\n";
    } else {
        echo "Header {$kode}: NOT FOUND\n";
    }
}

echo "\n";

// Test view logic simulation
echo "View Logic Simulation:\n";
echo "======================\n";

foreach ($coas->take(10) as $coa) {
    $isHeader = (strlen($coa->kode_akun) <= 2) || (substr($coa->kode_akun, -1) == '0');
    $saldo = $saldoPeriode[$coa->id] ?? 0;
    
    echo "COA: {$coa->kode_akun} - {$coa->nama_akun}\n";
    
    if ($isHeader && isset($headerTotals[$coa->kode_akun])) {
        $total = $headerTotals[$coa->kode_akun];
        echo "  Display: <strong>" . number_format($total, 0, ',', '.') . "</strong> (HEADER TOTAL)\n";
    } else {
        if ($saldo == floor($saldo)) {
            echo "  Display: " . number_format($saldo, 0, ',', '.') . " (INDIVIDUAL)\n";
        } else {
            echo "  Display: " . number_format($saldo, 2, ',', '.') . " (INDIVIDUAL)\n";
        }
    }
    echo "\n";
}

echo "Expected Results:\n";
echo "================\n";
echo "Aset (11): Should show 175.600.000 (not 0)\n";
echo "Modal (31): Should show 175.600.000 (not 0)\n";
echo "Modal Usaha (310): Should show 175.600.000 (correct)\n";

echo "\nCOA controller fix test completed!\n";
