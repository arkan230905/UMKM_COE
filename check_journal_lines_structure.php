<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING JOURNAL LINES TABLE STRUCTURE\n";
echo "====================================\n";

echo "\n=== JOURNAL LINES TABLE COLUMNS ===\n";
$journalLinesColumns = \Illuminate\Support\Facades\Schema::getColumnListing('journal_lines');
foreach ($journalLinesColumns as $column) {
    echo "- {$column}\n";
}

echo "\n=== SAMPLE JOURNAL LINES DATA ===\n";
$sampleData = \App\Models\JournalLine::take(5)->get();
foreach ($sampleData as $line) {
    echo "ID: {$line->id}, Debit: " . ($line->debit ?? 'NULL') . ", Credit: " . ($line->credit ?? 'NULL') . "\n";
}

echo "\n=== CORRECTED SYNC SCRIPT ===\n";

// Get all journal lines for April 2026 with correct column names
$journalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.user_id', 1)
    ->whereMonth('journal_entries.tanggal', 4)
    ->whereYear('journal_entries.tanggal', 2026)
    ->select(
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun',
        \Illuminate\Support\Facades\DB::raw('SUM(journal_lines.debit) as total_debit'),
        \Illuminate\Support\Facades\DB::raw('SUM(journal_lines.credit) as total_credit')
    )
    ->groupBy('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->orderBy('coas.kode_akun')
    ->get();

echo "Data buku besar April 2026:\n";
echo "Kode Akun\tNama Akun\t\t\tTotal Debit\tTotal Credit\tSaldo\n";
echo "================================================================\n";

$totalDebit = 0;
$totalCredit = 0;
$coaBalances = [];

foreach ($journalLines as $line) {
    $debit = $line->total_debit ?? 0;
    $credit = $line->total_credit ?? 0;
    
    // Calculate saldo based on account type
    if ($line->tipe_akun == 'Aset') {
        $saldo = $debit - $credit; // Assets: Debit - Credit
    } elseif ($line->tipe_akun == 'Kewajiban') {
        $saldo = $credit - $debit; // Liabilities: Credit - Debit
    } elseif ($line->tipe_akun == 'Equity' || $line->tipe_akun == 'Pendapatan') {
        $saldo = $credit - $debit; // Equity/Revenue: Credit - Debit
    } else { // Biaya/Expense
        $saldo = $debit - $credit; // Expenses: Debit - Credit
    }
    
    $coaBalances[$line->kode_akun] = [
        'nama' => $line->nama_akun,
        'tipe' => $line->tipe_akun,
        'debit' => $debit,
        'credit' => $credit,
        'saldo' => $saldo
    ];
    
    $totalDebit += $debit;
    $totalCredit += $credit;
    
    printf("%-8s\t%-30s\t%10s\t%10s\t%10s\n", 
        $line->kode_akun, 
        substr($line->nama_akun, 0, 30), 
        number_format($debit, 0, ',', '.'), 
        number_format($credit, 0, ',', '.'), 
        number_format($saldo, 0, ',', '.')
    );
}

echo "\n================================================================\n";
echo "TOTAL\t\t\t\t" . number_format($totalDebit, 0, ',', '.') . "\t" . number_format($totalCredit, 0, ',', '.') . "\n";
echo "Selisih: Rp " . number_format(abs($totalDebit - $totalCredit), 0, ',', '.') . "\n";
echo "Status: " . ($totalDebit == $totalCredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\nJournal lines structure check completed!\n";
