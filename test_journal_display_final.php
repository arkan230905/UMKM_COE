<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST JOURNAL DISPLAY FINAL ===" . PHP_EOL;

// 1. Cek data mentah di database
echo "1. DATA MENTAH DI DATABASE:" . PHP_EOL;

$journalEntry = \App\Models\JournalEntry::where('ref_type', 'purchase')
    ->with('lines.account')
    ->orderBy('id', 'desc')
    ->first();

if ($journalEntry) {
    echo "Journal Entry ID: {$journalEntry->id}" . PHP_EOL;
    echo "Tanggal: {$journalEntry->tanggal}" . PHP_EOL;
    echo "Memo: {$journalEntry->memo}" . PHP_EOL;
    echo PHP_EOL;
    
    echo "Lines ({$journalEntry->lines->count()}):" . PHP_EOL;
    foreach ($journalEntry->lines as $index => $line) {
        echo "  " . ($index + 1) . ". Account: " . ($line->account ? $line->account->name : 'Unknown') . PHP_EOL;
        echo "     Code: " . ($line->account ? $line->account->code : 'Unknown') . PHP_EOL;
        echo "     Debit: Rp " . number_format($line->debit, 2, ',', '.') . PHP_EOL;
        echo "     Credit: Rp " . number_format($line->credit, 2, ',', '.') . PHP_EOL;
        echo PHP_EOL;
    }
}

// 2. Simulasi query yang sama seperti AkuntansiController
echo PHP_EOL . "2. SIMULASI QUERY AKUNTANSI CONTROLLER:" . PHP_EOL;

$query = \App\Models\JournalEntry::with(['lines.account'])
    ->where('ref_type', 'purchase')
    ->orderBy('tanggal','asc')
    ->orderBy('id','asc');

$entries = $query->get();

echo "Total entries: " . $entries->count() . PHP_EOL;

foreach ($entries as $entry) {
    echo "Entry ID: {$entry->id}" . PHP_EOL;
    echo "Tanggal: {$entry->tanggal}" . PHP_EOL;
    echo "Ref: {$entry->ref_type} - {$entry->ref_id}" . PHP_EOL;
    
    foreach ($entry->lines as $line) {
        echo "  - {$line->account->name}: Debit Rp " . 
             number_format($line->debit, 2, ',', '.') . 
             ", Credit Rp " . number_format($line->credit, 2, ',', '.') . PHP_EOL;
    }
    echo PHP_EOL;
}

// 3. Cek apakah ada transformasi data
echo PHP_EOL . "3. CEK TRANSFORMASI DATA:" . PHP_EOL;

foreach ($entries as $entry) {
    foreach ($entry->lines as $line) {
        $debitValue = $line->debit;
        $creditValue = $line->credit;
        
        echo "Account: {$line->account->name}" . PHP_EOL;
        echo "  Raw debit: {$debitValue}" . PHP_EOL;
        echo "  Raw credit: {$creditValue}" . PHP_EOL;
        echo "  Debit > 0: " . ($debitValue > 0 ? 'YES' : 'NO') . PHP_EOL;
        echo "  Credit > 0: " . ($creditValue > 0 ? 'YES' : 'NO') . PHP_EOL;
        echo PHP_EOL;
    }
}

echo "✅ Test selesai!" . PHP_EOL;
echo "Silakan refresh browser dengan Ctrl+F5" . PHP_EOL;
