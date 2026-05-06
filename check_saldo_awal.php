<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING SALDO AWAL (Opening Balances) ===\n\n";

$coas = DB::table('coas')
    ->where('user_id', 1)
    ->where('saldo_awal', '!=', 0)
    ->orderBy('kode_akun')
    ->get(['kode_akun', 'nama_akun', 'tipe_akun', 'saldo_awal', 'saldo_normal']);

echo str_pad("Kode", 8) . str_pad("Nama Akun", 35) . str_pad("Tipe", 15) . str_pad("Saldo Awal", 20) . str_pad("Normal", 10) . "\n";
echo str_repeat("-", 88) . "\n";

$totalSaldoAwalDebit = 0;
$totalSaldoAwalKredit = 0;

foreach ($coas as $coa) {
    $saldoAwal = $coa->saldo_awal;
    
    echo str_pad($coa->kode_akun, 8) . 
         str_pad(substr($coa->nama_akun, 0, 34), 35) . 
         str_pad($coa->tipe_akun, 15) . 
         str_pad("Rp " . number_format($saldoAwal, 0), 20) . 
         str_pad($coa->saldo_normal, 10) . "\n";
    
    // Add to totals based on saldo_normal
    if ($coa->saldo_normal == 'debit') {
        $totalSaldoAwalDebit += $saldoAwal;
    } else {
        $totalSaldoAwalKredit += $saldoAwal;
    }
}

echo str_repeat("-", 88) . "\n";
echo "Total Saldo Awal Debit:  Rp " . number_format($totalSaldoAwalDebit, 0) . "\n";
echo "Total Saldo Awal Kredit: Rp " . number_format($totalSaldoAwalKredit, 0) . "\n";
$diff = $totalSaldoAwalDebit - $totalSaldoAwalKredit;
echo "Difference:              Rp " . number_format($diff, 0) . "\n";

if (abs($diff) < 0.01) {
    echo "\n✅ Opening balances are balanced!\n";
} else {
    echo "\n❌ Opening balances are NOT balanced!\n";
}
