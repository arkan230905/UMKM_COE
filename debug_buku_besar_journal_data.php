<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DEBUG BUKU BESAR - JOURNAL DATA CONSISTENCY\n";
echo "=============================================\n";

echo "\n=== ANALISA DATA JOURNAL UNTUK KAS (112) ===\n";

// Get all journal entries for April 2026
$journalEntries = \App\Models\JournalEntry::whereMonth('tanggal', 4)
    ->whereYear('tanggal', 2026)
    ->orderBy('tanggal')
    ->orderBy('id')
    ->get();

echo "Total Journal Entries April 2026: " . $journalEntries->count() . "\n";

echo "\n=== DETAIL JOURNAL ENTRIES ===\n";
echo "ID\tTanggal\t\tRef Type\t\tRef ID\t\tMemo\n";
echo "================================================================\n";

foreach ($journalEntries as $entry) {
    echo "{$entry->id}\t{$entry->tanggal}\t{$entry->ref_type}\t\t{$entry->ref_id}\t\t" . substr($entry->memo, 0, 30) . "\n";
}

echo "\n=== DETAIL JOURNAL LINES UNTUK KAS (112) ===\n";

// Get COA Kas (112)
$kasCoa = \App\Models\Coa::where('kode_akun', '112')->where('user_id', 1)->first();
if (!$kasCoa) {
    echo "ERROR: COA Kas (112) tidak ditemukan!\n";
    exit;
}

echo "COA Kas (112) ID: {$kasCoa->id}\n";

// Get all journal lines for Kas COA
$kasJournalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->where('journal_lines.coa_id', $kasCoa->id)
    ->whereMonth('journal_entries.tanggal', 4)
    ->whereYear('journal_entries.tanggal', 2026)
    ->select(
        'journal_entries.id',
        'journal_entries.tanggal',
        'journal_entries.ref_type',
        'journal_entries.ref_id',
        'journal_entries.memo',
        'journal_lines.debit',
        'journal_lines.credit'
    )
    ->orderBy('journal_entries.tanggal')
    ->orderBy('journal_entries.id')
    ->get();

echo "\nJournal Lines untuk Kas (112):\n";
echo "ID\tTanggal\t\tRef Type\t\tDebit\t\tCredit\t\tMemo\n";
echo "========================================================================\n";

$totalDebit = 0;
$totalCredit = 0;

foreach ($kasJournalLines as $line) {
    $totalDebit += $line->debit;
    $totalCredit += $line->credit;
    
    echo "{$line->id}\t{$line->tanggal}\t{$line->ref_type}\t\t" . 
         number_format($line->debit, 0, ',', '.') . "\t" . 
         number_format($line->credit, 0, ',', '.') . "\t" . 
         substr($line->memo, 0, 30) . "\n";
}

echo "\n========================================================================\n";
echo "Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Credit: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
echo "Net: Rp " . number_format($totalDebit - $totalCredit, 0, ',', '.') . "\n";

echo "\n=== COMPARISON WITH USER REPORT ===\n";
echo "User Report:\n";
echo "- Saldo Awal: Rp 75.644.200\n";
echo "- Total Debit: Rp 1.110.000\n";
echo "- Total Credit: Rp 2.367.700\n";
echo "- Saldo Akhir: Rp 74.386.500\n";

echo "\nActual Journal Data:\n";
echo "- Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "- Total Credit: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
echo "- Net: Rp " . number_format($totalDebit - $totalCredit, 0, ',', '.') . "\n";

echo "\n=== ANALYSIS ===\n";
if ($totalDebit == 1110000 && $totalCredit == 2367700) {
    echo "MATCH: Journal data matches user report\n";
} else {
    echo "MISMATCH: Journal data does not match user report\n";
    echo "Expected Debit: Rp 1.110.000, Actual: Rp " . number_format($totalDebit, 0, ',', '.') . "\n";
    echo "Expected Credit: Rp 2.367.700, Actual: Rp " . number_format($totalCredit, 0, ',', '.') . "\n";
}

echo "\n=== CHECKING FOR DUPLICATE OR MISSING ENTRIES ===\n";

// Check for specific transactions mentioned by user
$expectedTransactions = [
    'Pembelian #PB-20260430-0001 - Tel-Mart' => ['credit' => 555000],
    'Pembelian #PB-20260430-0002 - Sukbir Mart' => ['credit' => 521700],
    'Penerimaan tunai penjualan' => ['debit' => 555000, 'count' => 2],
    'Penggajian Budi Susanto' => ['credit' => 765000],
    'Penggajian Dedi Gunawan' => ['credit' => 526000],
];

foreach ($expectedTransactions as $memo => $expected) {
    $found = false;
    foreach ($kasJournalLines as $line) {
        if (strpos($line->memo, $memo) !== false) {
            $found = true;
            echo "Found: {$memo} - Debit: " . number_format($line->debit, 0, ',', '.') . 
                 ", Credit: " . number_format($line->credit, 0, ',', '.') . "\n";
            break;
        }
    }
    
    if (!$found) {
        echo "MISSING: {$memo}\n";
    }
}

echo "\n=== CHECKING JOURNAL ENTRY CREATION ===\n";

// Check if there are any journal entries without proper lines
$entriesWithoutLines = \App\Models\JournalEntry::whereMonth('tanggal', 4)
    ->whereYear('tanggal', 2026)
    ->whereDoesntHave('journalLines')
    ->count();

if ($entriesWithoutLines > 0) {
    echo "WARNING: {$entriesWithoutLines} journal entries without lines\n";
} else {
    echo "OK: All journal entries have lines\n";
}

echo "\nBuku besar debug completed!\n";
