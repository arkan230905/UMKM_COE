<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Checking Purchase ID 10 and Journal Entries...\n\n";

// Check pembelian
$pembelian = \App\Models\Pembelian::find(10);
echo "Pembelian ID 10 exists: " . ($pembelian ? 'YES' : 'NO') . "\n";

if ($pembelian) {
    echo "Nomor Pembelian: " . $pembelian->nomor_pembelian . "\n";
    echo "Tanggal: " . $pembelian->tanggal . "\n";
    echo "Total Harga: " . number_format($pembelian->total_harga, 0, ',', '.') . "\n";
    echo "Payment Method: " . $pembelian->payment_method . "\n";
    echo "Status: " . $pembelian->status . "\n\n";
    
    // Check journal entries
    echo "Checking journal entries...\n";
    $journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
        ->where('ref_id', 10)
        ->with('lines.coa')
        ->orderBy('id', 'desc')
        ->get();
    
    echo "Found " . $journalEntries->count() . " journal entries\n\n";
    
    if ($journalEntries->count() > 0) {
        foreach ($journalEntries as $journal) {
            echo "Journal Entry ID: " . $journal->id . "\n";
            echo "Tanggal: " . $journal->tanggal . "\n";
            echo "Memo: " . $journal->memo . "\n";
            echo "Lines count: " . $journal->lines->count() . "\n\n";
            
            foreach ($journal->lines as $line) {
                echo "  Line ID: " . $line->id . "\n";
                echo "  COA: " . ($line->coa ? $line->coa->nama_akun : 'NULL') . "\n";
                echo "  Debet: " . number_format($line->debit, 0, ',', '.') . "\n";
                echo "  Kredit: " . number_format($line->credit, 0, ',', '.') . "\n";
                echo "  Memo: " . $line->memo . "\n\n";
            }
        }
    } else {
        echo "No journal entries found for purchase ID 10\n\n";
        
        // Check if there are any journal entries with different ref_type
        echo "Checking other journal entries that might reference this purchase...\n";
        $otherJournals = \App\Models\JournalEntry::where('ref_id', 10)->get();
        echo "Found " . $otherJournals->count() . " journal entries with ref_id=10 (any ref_type)\n\n";
        
        foreach ($otherJournals as $journal) {
            echo "Journal Entry ID: " . $journal->id . "\n";
            echo "Ref Type: " . $journal->ref_type . "\n";
            echo "Ref ID: " . $journal->ref_id . "\n";
            echo "Tanggal: " . $journal->tanggal . "\n";
            echo "Memo: " . $journal->memo . "\n\n";
        }
    }
} else {
    echo "Purchase ID 10 not found!\n";
    
    // Check all purchases
    echo "\nChecking all purchases...\n";
    $allPurchases = \App\Models\Pembelian::orderBy('id')->limit(10)->get(['id', 'nomor_pembelian', 'tanggal', 'total_harga']);
    foreach ($allPurchases as $p) {
        echo "ID: {$p->id}, Nomor: {$p->nomor_pembelian}, Tanggal: {$p->tanggal}, Total: " . number_format($p->total_harga, 0, ',', '.') . "\n";
    }
}

echo "\nDone.\n";
