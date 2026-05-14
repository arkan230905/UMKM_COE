<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING FOR UNBALANCED TRANSACTIONS ===\n\n";

// Group by referensi and tipe_referensi to check if each transaction is balanced
$transactions = DB::table('jurnal_umum')
    ->where('user_id', 1)
    ->select('tipe_referensi', 'referensi', 'tanggal')
    ->selectRaw('SUM(debit) as total_debit')
    ->selectRaw('SUM(kredit) as total_kredit')
    ->groupBy('tipe_referensi', 'referensi', 'tanggal')
    ->get();

echo "Checking " . count($transactions) . " transactions...\n\n";

$unbalanced = [];
foreach ($transactions as $trans) {
    $diff = abs($trans->total_debit - $trans->total_kredit);
    if ($diff > 0.01) {
        $unbalanced[] = $trans;
        echo "❌ UNBALANCED: {$trans->tipe_referensi} #{$trans->referensi} ({$trans->tanggal})\n";
        echo "   Debit: Rp " . number_format($trans->total_debit, 2) . "\n";
        echo "   Kredit: Rp " . number_format($trans->total_kredit, 2) . "\n";
        echo "   Difference: Rp " . number_format($diff, 2) . "\n\n";
        
        // Show details
        $details = DB::table('jurnal_umum as ju')
            ->join('coas', 'ju.coa_id', '=', 'coas.id')
            ->where('ju.tipe_referensi', $trans->tipe_referensi)
            ->where('ju.referensi', $trans->referensi)
            ->where('ju.user_id', 1)
            ->select('coas.kode_akun', 'coas.nama_akun', 'ju.keterangan', 'ju.debit', 'ju.kredit')
            ->get();
        
        foreach ($details as $detail) {
            echo "   " . str_pad($detail->kode_akun, 6) . " " . str_pad($detail->nama_akun, 30) . " ";
            if ($detail->debit > 0) {
                echo "Dr. Rp " . number_format($detail->debit, 0);
            } else {
                echo "Cr. Rp " . number_format($detail->kredit, 0);
            }
            echo "\n";
        }
        echo "\n";
    }
}

if (empty($unbalanced)) {
    echo "✅ All transactions are balanced!\n";
} else {
    echo "Found " . count($unbalanced) . " unbalanced transaction(s)!\n";
}

// Also check for entries without referensi
echo "\n=== CHECKING FOR ENTRIES WITHOUT REFERENSI ===\n";
$noRef = DB::table('jurnal_umum')
    ->where('user_id', 1)
    ->whereNull('referensi')
    ->orWhere('referensi', '')
    ->count();

if ($noRef > 0) {
    echo "❌ Found {$noRef} entries without referensi\n";
} else {
    echo "✅ All entries have referensi\n";
}
