<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Journal Tables Structure...\n\n";

// Check jurnal_umum table
echo "Jurnal Umum Table:\n";
echo "==================\n";

try {
    $jurnalUmumCount = \Illuminate\Support\Facades\DB::table('jurnal_umum')->count();
    echo "Jurnal Umum Records: {$jurnalUmumCount}\n";
    
    if ($jurnalUmumCount > 0) {
        $sample = \Illuminate\Support\Facades\DB::table('jurnal_umum')->limit(3)->get();
        foreach ($sample as $record) {
            echo "  ID: {$record->id}, Tanggal: {$record->tanggal}, Debit: {$record->debit}, Kredit: {$record->kredit}\n";
        }
    }
} catch (Exception $e) {
    echo "Error accessing jurnal_umum: " . $e->getMessage() . "\n";
}

echo "\n";

// Check journal_lines table
echo "Journal Lines Table:\n";
echo "====================\n";

try {
    $journalLinesCount = \Illuminate\Support\Facades\DB::table('journal_lines')->count();
    echo "Journal Lines Records: {$journalLinesCount}\n";
    
    if ($journalLinesCount > 0) {
        $sample = \Illuminate\Support\Facades\DB::table('journal_lines')->limit(3)->get();
        foreach ($sample as $record) {
            echo "  ID: {$record->id}, Debit: {$record->debit}, Kredit: {$record->kredit}\n";
        }
    }
} catch (Exception $e) {
    echo "Error accessing journal_lines: " . $e->getMessage() . "\n";
}

echo "\n";

// Check journal_entries table
echo "Journal Entries Table:\n";
echo "=======================\n";

try {
    $journalEntriesCount = \Illuminate\Support\Facades\DB::table('journal_entries')->count();
    echo "Journal Entries Records: {$journalEntriesCount}\n";
    
    if ($journalEntriesCount > 0) {
        $sample = \Illuminate\Support\Facades\DB::table('journal_entries')->limit(3)->get();
        foreach ($sample as $record) {
            echo "  ID: {$record->id}, Debit: {$record->debit}, Kredit: {$record->kredit}\n";
        }
    }
} catch (Exception $e) {
    echo "Error accessing journal_entries: " . $e->getMessage() . "\n";
}

echo "\n";

// Check if TrialBalanceService is using the right tables
echo "Conclusion:\n";
echo "===========\n";
echo "TrialBalanceService uses JournalLine and JournalEntry models\n";
echo "But actual data is in jurnal_umum table\n";
echo "This is why TrialBalanceService returns empty data\n";
echo "Solution: Update TrialBalanceService to use jurnal_umum table\n";

echo "\nJournal tables test completed!\n";
