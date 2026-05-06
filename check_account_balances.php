<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking account balances after fix:\n\n";

// Check account 116 (Parent - should be 0 now)
echo "Account 116 (Pers. Barang Jadi - Parent):\n";
$debit116 = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', '116')
    ->where('jurnal_umum.user_id', 1)
    ->sum('jurnal_umum.debit');
    
$kredit116 = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', '116')
    ->where('jurnal_umum.user_id', 1)
    ->sum('jurnal_umum.kredit');
    
$saldo116 = $debit116 - $kredit116;
echo "  Debit: Rp " . number_format($debit116, 2) . "\n";
echo "  Kredit: Rp " . number_format($kredit116, 2) . "\n";
echo "  Saldo: Rp " . number_format($saldo116, 2) . "\n\n";

// Check account 1161 (Jasuke - should have the transactions)
echo "Account 1161 (Pers. Barang Jadi Jasuke - Child):\n";
$debit1161 = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', '1161')
    ->where('jurnal_umum.user_id', 1)
    ->sum('jurnal_umum.debit');
    
$kredit1161 = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', '1161')
    ->where('jurnal_umum.user_id', 1)
    ->sum('jurnal_umum.kredit');
    
$saldo1161 = $debit1161 - $kredit1161;
echo "  Debit: Rp " . number_format($debit1161, 2) . "\n";
echo "  Kredit: Rp " . number_format($kredit1161, 2) . "\n";
echo "  Saldo: Rp " . number_format($saldo1161, 2) . "\n\n";

// Calculate total trial balance
echo "=== TRIAL BALANCE CHECK ===\n";
$allAccounts = DB::table('jurnal_umum')
    ->join('coas', 'jurnal_umum.coa_id', '=', 'coas.id')
    ->where('jurnal_umum.user_id', 1)
    ->select('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->selectRaw('SUM(jurnal_umum.debit) as total_debit')
    ->selectRaw('SUM(jurnal_umum.kredit) as total_kredit')
    ->groupBy('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->get();

$totalSaldoDebit = 0;
$totalSaldoKredit = 0;

foreach ($allAccounts as $account) {
    $saldo = $account->total_debit - $account->total_kredit;
    
    // Normalize tipe_akun to uppercase for comparison
    $tipeAkun = strtoupper($account->tipe_akun);
    
    // Determine position in trial balance
    if (in_array($tipeAkun, ['ASET', 'ASSET', 'AKTIVA']) || 
        in_array($tipeAkun, ['BEBAN', 'BIAYA', 'EXPENSE'])) {
        // Debit position accounts
        if ($saldo >= 0) {
            $totalSaldoDebit += $saldo;
        } else {
            $totalSaldoKredit += abs($saldo);
        }
    } else {
        // Credit position accounts (Kewajiban, Modal, Pendapatan)
        if ($saldo <= 0) {
            $totalSaldoKredit += abs($saldo);
        } else {
            $totalSaldoDebit += $saldo;
        }
    }
}

echo "Total Saldo Debit: Rp " . number_format($totalSaldoDebit, 2) . "\n";
echo "Total Saldo Kredit: Rp " . number_format($totalSaldoKredit, 2) . "\n";
$difference = $totalSaldoDebit - $totalSaldoKredit;
echo "Difference: Rp " . number_format($difference, 2) . "\n";

if (abs($difference) < 0.01) {
    echo "\n✅ NERACA SALDO IS BALANCED!\n";
} else {
    echo "\n❌ NERACA SALDO IS NOT BALANCED (Difference: Rp " . number_format($difference, 2) . ")\n";
}
