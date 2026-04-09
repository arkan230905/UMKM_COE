<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Debugging PembelianObserver error...\n\n";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pembelian = \App\Models\Pembelian::find(10);
if (!$pembelian) {
    echo "Purchase ID 10 not found!\n";
    exit;
}

echo "Creating observer instance...\n";
try {
    $observer = new \App\Observers\PembelianObserver();
    echo "Observer created successfully.\n";
} catch (\Exception $e) {
    echo "Error creating observer: " . $e->getMessage() . "\n";
    exit;
}

echo "\nTesting createPembelianJournal method directly...\n";
try {
    // Use reflection to access private method
    $reflection = new ReflectionClass($observer);
    $method = $reflection->getMethod('createPembelianJournal');
    $method->setAccessible(true);
    
    echo "Calling createPembelianJournal...\n";
    $method->invoke($observer, $pembelian);
    echo "Method completed successfully.\n";
    
} catch (\Exception $e) {
    echo "Error in createPembelianJournal: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nChecking if journal was created...\n";
$journalEntries = \App\Models\JournalEntry::where('ref_type', 'purchase')
    ->where('ref_id', 10)
    ->with('lines.coa')
    ->get();

echo "Journal entries found: " . $journalEntries->count() . "\n";

if ($journalEntries->count() > 0) {
    foreach ($journalEntries as $journal) {
        echo "Journal Entry ID: {$journal->id}\n";
        echo "Lines:\n";
        foreach ($journal->lines as $line) {
            $coaName = $line->coa ? $line->coa->nama_akun : 'UNKNOWN';
            echo "  {$coaName}: Debit={$line->debit}, Credit={$line->credit}\n";
        }
    }
} else {
    echo "No journal entries found.\n";
}

echo "\nChecking Laravel logs for errors...\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    
    // Get last 50 lines
    $recentLines = array_slice($lines, -50);
    
    echo "Recent log entries:\n";
    foreach ($recentLines as $line) {
        if (stripos($line, 'pembelian') !== false || 
            stripos($line, 'journal') !== false ||
            stripos($line, 'error') !== false) {
            echo $line . "\n";
        }
    }
}

echo "\nDone.\n";
