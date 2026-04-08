<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Testing PembelianObserver with correct relationship loading...\n\n";

// Clean up
$journalService = new \App\Services\JournalService();
$journalService->deleteByRef('purchase', 10);

// Load pembelian with all relationships properly
$pembelian = \App\Models\Pembelian::with([
    'details.bahanBaku.coaPembelian',
    'details.bahanPendukung.coaPembelian'
])->find(10);

if (!$pembelian) {
    echo "Purchase ID 10 not found!\n";
    exit;
}

echo "Purchase loaded successfully\n";
echo "Details count: " . $pembelian->details->count() . "\n\n";

// Test the observer
echo "Testing observer created() method...\n";
$observer = new \App\Observers\PembelianObserver();
$observer->created($pembelian);

echo "\nChecking if journal was created...\n";
$journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
    ->where('ref_id', 10)
    ->with('lines.coa')
    ->get();

echo "Journal entries found: " . $journalEntries->count() . "\n\n";

if ($journalEntries->count() > 0) {
    foreach ($journalEntries as $journal) {
        echo "Journal Entry ID: {$journal->id}\n";
        echo "Tanggal: {$journal->tanggal}\n";
        echo "Memo: {$journal->memo}\n";
        echo "Lines:\n";
        
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($journal->lines as $line) {
            $coaName = $line->coa ? $line->coa->nama_akun : 'UNKNOWN';
            echo "  {$coaName}: Debit={$line->debit}, Credit={$line->credit}\n";
            $totalDebit += $line->debit;
            $totalCredit += $line->credit;
        }
        
        echo "  Total Debit: {$totalDebit}\n";
        echo "  Total Credit: {$totalCredit}\n";
        echo "  Balance: " . ($totalDebit == $totalCredit ? 'BALANCED ✓' : 'NOT BALANCED ✗') . "\n\n";
    }
    
    echo "✓ Journal creation SUCCESS!\n";
} else {
    echo "✗ Journal creation FAILED\n";
    
    // Check logs
    echo "\nChecking recent logs...\n";
    $logFile = 'storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        
        // Get last 10 lines
        $recentLines = array_slice($lines, -10);
        
        foreach ($recentLines as $line) {
            if (stripos($line, 'pembelian') !== false || 
                stripos($line, 'journal') !== false ||
                stripos($line, 'error') !== false) {
                echo $line . "\n";
            }
        }
    }
}

echo "\nDone.\n";
