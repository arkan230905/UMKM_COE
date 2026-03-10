<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG JOURNAL PEMBELIAN ===" . PHP_EOL;

// Cek journal entry terakhir
$lastEntry = \App\Models\JournalEntry::with('linesWithAccount.account')
    ->orderBy('id', 'desc')
    ->first();

if ($lastEntry) {
    echo "Journal Entry ID: {$lastEntry->id}" . PHP_EOL;
    echo "Tanggal: {$lastEntry->tanggal}" . PHP_EOL;
    echo "Ref: {$lastEntry->ref_type} - {$lastEntry->ref_id}" . PHP_EOL;
    echo "Memo: {$lastEntry->memo}" . PHP_EOL . PHP_EOL;
    
    echo "Journal Lines:" . PHP_EOL;
    foreach ($lastEntry->linesWithAccount as $line) {
        $accountName = $line->account ? $line->account->name : 'Unknown';
        echo "- {$accountName}: Debit Rp " . number_format($line->debit, 2, ',', '.') . 
             ", Credit Rp " . number_format($line->credit, 2, ',', '.') . PHP_EOL;
    }
    
    echo PHP_EOL;
    $totalDebit = $lastEntry->linesWithAccount->sum('debit');
    $totalCredit = $lastEntry->linesWithAccount->sum('credit');
    echo "Total Debit: Rp " . number_format($totalDebit, 2, ',', '.') . PHP_EOL;
    echo "Total Credit: Rp " . number_format($totalCredit, 2, ',', '.') . PHP_EOL;
    echo "Balance: " . ($totalDebit == $totalCredit ? 'OK' : 'NOT BALANCED') . PHP_EOL;
} else {
    echo "Tidak ada journal entry" . PHP_EOL;
}
