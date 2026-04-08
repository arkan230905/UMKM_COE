<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking all pembelian transactions and their journal status...\n\n";

// Get all pembelian transactions
$pembelians = \App\Models\Pembelian::orderBy('id')->get(['id', 'nomor_pembelian', 'tanggal', 'total_harga', 'payment_method']);

echo "Total pembelian transactions: " . $pembelians->count() . "\n\n";

foreach ($pembelians as $pembelian) {
    echo "=== Pembelian ID: {$pembelian->id} ===\n";
    echo "Nomor: {$pembelian->nomor_pembelian}\n";
    echo "Tanggal: {$pembelian->tanggal}\n";
    echo "Total: " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
    echo "Payment Method: {$pembelian->payment_method}\n";
    
    // Check if journal exists
    $journalCount = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', $pembelian->id)
        ->count();
    
    echo "Journal Entries: " . $journalCount . "\n";
    
    if ($journalCount > 0) {
        echo "Status: ✓ HAS JOURNAL\n";
        
        // Show journal details
        $journals = \App\Models\JournalEntry::where('ref_type', 'purchase')
            ->where('ref_id', $pembelian->id)
            ->with('lines.coa')
            ->get();
            
        foreach ($journals as $journal) {
            echo "  Journal ID: {$journal->id}\n";
            foreach ($journal->lines as $line) {
                $coaName = $line->coa ? $line->coa->nama_akun : 'UNKNOWN';
                echo "    {$coaName}: D={$line->debit}, C={$line->credit}\n";
            }
        }
    } else {
        echo "Status: ✗ NO JOURNAL\n";
        
        // Check if pembelian has details
        $detailCount = \App\Models\PembelianDetail::where('pembelian_id', $pembelian->id)->count();
        echo "  Details count: {$detailCount}\n";
        
        if ($detailCount > 0) {
            echo "  Should have journal but missing!\n";
        } else {
            echo "  No details - probably empty purchase\n";
        }
    }
    
    echo "\n";
}

echo "Summary:\n";
$withJournal = 0;
$withoutJournal = 0;

foreach ($pembelians as $pembelian) {
    $journalCount = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', $pembelian->id)
        ->count();
    
    if ($journalCount > 0) {
        $withJournal++;
    } else {
        $withoutJournal++;
    }
}

echo "With journal: {$withJournal}\n";
echo "Without journal: {$withoutJournal}\n";

echo "\nDone.\n";
