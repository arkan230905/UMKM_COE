<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== NERACA SALDO VERIFICATION ===\n";
echo "Period: Mei 2026\n";
echo "User ID: 1\n\n";

// Get all COAs for user
$coas = DB::table('coas')
    ->where('user_id', 1)
    ->orderBy('kode_akun')
    ->get();

$totalSaldoDebit = 0;
$totalSaldoKredit = 0;

echo str_pad("Kode", 8) . str_pad("Nama Akun", 35) . str_pad("Tipe", 15) . str_pad("Saldo Akhir", 20) . "\n";
echo str_repeat("-", 78) . "\n";

foreach ($coas as $coa) {
    // Get transactions for this COA
    $debit = DB::table('jurnal_umum')
        ->where('coa_id', $coa->id)
        ->where('user_id', 1)
        ->sum('debit');
        
    $kredit = DB::table('jurnal_umum')
        ->where('coa_id', $coa->id)
        ->where('user_id', 1)
        ->sum('kredit');
    
    $saldo = $debit - $kredit;
    
    // Skip accounts with zero balance
    if (abs($saldo) < 0.01) {
        continue;
    }
    
    // Normalize tipe_akun
    $tipeAkun = strtoupper($coa->tipe_akun);
    
    // Determine position in trial balance
    $isDebitPosition = in_array($tipeAkun, ['ASET', 'ASSET', 'AKTIVA']) || 
                       in_array($tipeAkun, ['BEBAN', 'BIAYA', 'EXPENSE']);
    
    if ($isDebitPosition) {
        if ($saldo >= 0) {
            $totalSaldoDebit += $saldo;
            $displaySaldo = "Rp " . number_format($saldo, 0);
        } else {
            $totalSaldoKredit += abs($saldo);
            $displaySaldo = "Rp (" . number_format(abs($saldo), 0) . ")";
        }
    } else {
        if ($saldo <= 0) {
            $totalSaldoKredit += abs($saldo);
            $displaySaldo = "Rp " . number_format(abs($saldo), 0);
        } else {
            $totalSaldoDebit += $saldo;
            $displaySaldo = "Rp " . number_format($saldo, 0);
        }
    }
    
    echo str_pad($coa->kode_akun, 8) . 
         str_pad(substr($coa->nama_akun, 0, 34), 35) . 
         str_pad($coa->tipe_akun, 15) . 
         str_pad($displaySaldo, 20) . "\n";
}

echo str_repeat("-", 78) . "\n";
echo "\n=== BALANCE CHECK ===\n";
echo "Total Saldo Debit:  Rp " . number_format($totalSaldoDebit, 0) . "\n";
echo "Total Saldo Kredit: Rp " . number_format($totalSaldoKredit, 0) . "\n";
$difference = $totalSaldoDebit - $totalSaldoKredit;
echo "Difference:         Rp " . number_format($difference, 0) . "\n\n";

if (abs($difference) < 0.01) {
    echo "✅ STATUS: BALANCED!\n";
} else {
    echo "❌ STATUS: NOT BALANCED (Selisih: Rp " . number_format($difference, 0) . ")\n";
}

echo "\n=== KEY ACCOUNTS TO VERIFY ===\n";
$keyAccounts = ['116', '1161'];
foreach ($keyAccounts as $kodeAkun) {
    $coa = DB::table('coas')->where('kode_akun', $kodeAkun)->where('user_id', 1)->first();
    if ($coa) {
        $debit = DB::table('jurnal_umum')
            ->where('coa_id', $coa->id)
            ->where('user_id', 1)
            ->sum('debit');
            
        $kredit = DB::table('jurnal_umum')
            ->where('coa_id', $coa->id)
            ->where('user_id', 1)
            ->sum('kredit');
        
        $saldo = $debit - $kredit;
        
        echo "\n{$coa->kode_akun} - {$coa->nama_akun}:\n";
        echo "  Debit:  Rp " . number_format($debit, 0) . "\n";
        echo "  Kredit: Rp " . number_format($kredit, 0) . "\n";
        echo "  Saldo:  Rp " . number_format($saldo, 0) . "\n";
        
        if ($kodeAkun == '116' && abs($saldo) < 0.01) {
            echo "  ✅ Parent account has zero balance (correct!)\n";
        } elseif ($kodeAkun == '1161' && $saldo > 0) {
            echo "  ✅ Jasuke account has positive balance (correct!)\n";
        }
    }
}
