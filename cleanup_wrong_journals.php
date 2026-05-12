<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CLEANING UP WRONG JOURNALS\n";
echo "========================\n\n";

// These are the wrong transaction numbers user sees but don't exist in database
$wrongTransactions = ['SJ-260412-001', 'SJ-260412-002', 'SJ-260412-003'];

foreach ($wrongTransactions as $transNo) {
    echo "Checking: $transNo\n";
    
    $journals = App\Models\JurnalUmum::where('referensi', $transNo)->get();
    
    if ($journals->count() > 0) {
        echo "  Found {$journals->count()} journals - DELETING:\n";
        foreach ($journals as $journal) {
            echo "    - COA {$journal->coa_id}: D={$journal->debit} K={$journal->kredit}\n";
            $journal->delete();
        }
        echo "  Deleted all journals for $transNo\n";
    } else {
        echo "  No journals found - OK\n";
    }
    echo "---\n";
}

echo "\nVerifying remaining journals:\n";
$remainingJournals = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
    ->whereBetween('tanggal', ['2026-04-01', '2026-04-30'])
    ->with('coa')
    ->get();

echo "Remaining penjualan journals:\n";
foreach ($remainingJournals as $journal) {
    $type = $journal->debit > 0 ? 'MASUK' : 'KELUAR';
    $amount = $journal->debit > 0 ? $journal->debit : $journal->kredit;
    echo "  {$journal->referensi} | {$journal->coa->kode_akun} | {$type} | Rp " . number_format($amount, 0, ',', '.') . "\n";
}

echo "\nDone! Please refresh your browser with Ctrl+F5\n";

?>
