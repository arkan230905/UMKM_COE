<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING HUTANG USAHA ===\n\n";

// Check if Hutang Usaha exists in COA
echo "1. Checking COA for Hutang Usaha:\n";
$hutangCoas = DB::table('coas')
    ->where('user_id', 1)
    ->where(function($query) {
        $query->where('nama_akun', 'like', '%Hutang%')
              ->orWhere('nama_akun', 'like', '%Payable%');
    })
    ->get(['kode_akun', 'nama_akun', 'tipe_akun', 'saldo_awal']);

if ($hutangCoas->isEmpty()) {
    echo "   ❌ No Hutang accounts found in COA!\n";
} else {
    foreach ($hutangCoas as $coa) {
        echo "   - {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun}) - Saldo Awal: Rp " . number_format($coa->saldo_awal, 0) . "\n";
    }
}

// Check if there are journal entries with Hutang Usaha
echo "\n2. Checking jurnal_umum for Hutang entries:\n";
$hutangEntries = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('ju.user_id', 1)
    ->where(function($query) {
        $query->where('coas.nama_akun', 'like', '%Hutang%')
              ->orWhere('coas.nama_akun', 'like', '%Payable%');
    })
    ->select(
        'coas.kode_akun',
        'coas.nama_akun',
        DB::raw('SUM(ju.debit) as total_debit'),
        DB::raw('SUM(ju.kredit) as total_kredit')
    )
    ->groupBy('coas.kode_akun', 'coas.nama_akun')
    ->get();

if ($hutangEntries->isEmpty()) {
    echo "   ❌ No Hutang entries found in jurnal_umum!\n";
} else {
    foreach ($hutangEntries as $entry) {
        $saldo = $entry->total_kredit - $entry->total_debit; // Hutang is credit normal
        echo "   - {$entry->kode_akun} - {$entry->nama_akun}\n";
        echo "     Debit: Rp " . number_format($entry->total_debit, 0) . "\n";
        echo "     Kredit: Rp " . number_format($entry->total_kredit, 0) . "\n";
        echo "     Saldo: Rp " . number_format($saldo, 0) . "\n\n";
    }
}

// Check all journal entries to see which COAs are being used
echo "\n3. All COAs used in jurnal_umum:\n";
$allUsedCoas = DB::table('jurnal_umum as ju')
    ->join('coas', 'ju.coa_id', '=', 'coas.id')
    ->where('ju.user_id', 1)
    ->select('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->distinct()
    ->orderBy('coas.kode_akun')
    ->get();

foreach ($allUsedCoas as $coa) {
    echo "   {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
}

// Calculate the imbalance
echo "\n4. Calculating total imbalance:\n";
$totalDebit = DB::table('jurnal_umum')->where('user_id', 1)->sum('debit');
$totalKredit = DB::table('jurnal_umum')->where('user_id', 1)->sum('kredit');
$journalDiff = $totalDebit - $totalKredit;

echo "   Total Debit in Journal: Rp " . number_format($totalDebit, 0) . "\n";
echo "   Total Kredit in Journal: Rp " . number_format($totalKredit, 0) . "\n";
echo "   Journal Difference: Rp " . number_format($journalDiff, 0) . "\n";

if (abs($journalDiff) < 0.01) {
    echo "   ✅ Journal entries are balanced\n";
} else {
    echo "   ❌ Journal entries are NOT balanced!\n";
}
