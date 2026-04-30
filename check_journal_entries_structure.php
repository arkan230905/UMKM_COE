<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CHECKING JOURNAL ENTRIES TABLE STRUCTURE\n";
echo "======================================\n";

echo "\n=== JOURNAL ENTRIES TABLE COLUMNS ===\n";
$journalEntriesColumns = \Illuminate\Support\Facades\Schema::getColumnListing('journal_entries');
foreach ($journalEntriesColumns as $column) {
    echo "- {$column}\n";
}

echo "\n=== SAMPLE JOURNAL ENTRIES DATA ===\n";
$sampleData = \App\Models\JournalEntry::take(3)->get();
foreach ($sampleData as $entry) {
    echo "ID: {$entry->id}, Tanggal: " . ($entry->tanggal ?? 'NULL') . ", Ref: " . ($entry->ref_type ?? 'NULL') . "\n";
}

echo "\n=== CORRECTED SYNC SCRIPT WITH PROPER FILTERING ===\n";

// Get all journal lines for April 2026 - filter by COA user_id instead
$journalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('coas.user_id', 1) // Filter by COA user_id instead
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

echo "\n=== UPDATING COA SALDO AWAL SESUAI BUKU BESAR ===\n";

// Get all COA accounts
$allCoas = \App\Models\Coa::where('user_id', 1)->get();

foreach ($allCoas as $coa) {
    $kodeAkun = $coa->kode_akun;
    
    if (isset($coaBalances[$kodeAkun])) {
        $newSaldo = $coaBalances[$kodeAkun]['saldo'];
        $currentSaldo = $coa->saldo_awal ?? 0;
        
        if ($currentSaldo != $newSaldo) {
            echo "Updating {$kodeAkun} - {$coa->nama_akun}:\n";
            echo "  Current: Rp " . number_format($currentSaldo, 0, ',', '.') . "\n";
            echo "  New: Rp " . number_format($newSaldo, 0, ',', '.') . "\n";
            
            $coa->update([
                'saldo_awal' => $newSaldo,
                'updated_at' => now(),
            ]);
            
            echo "  Status: UPDATED\n";
        } else {
            echo "No change needed for {$kodeAkun} - {$coa->nama_akun}\n";
        }
    } else {
        // COA not in journal lines, set to 0
        if (($coa->saldo_awal ?? 0) != 0) {
            echo "Setting {$kodeAkun} - {$coa->nama_akun} to 0 (no journal activity):\n";
            echo "  Current: Rp " . number_format($coa->saldo_awal ?? 0, 0, ',', '.') . "\n";
            echo "  New: Rp 0\n";
            
            $coa->update([
                'saldo_awal' => 0,
                'updated_at' => now(),
            ]);
            
            echo "  Status: UPDATED\n";
        }
    }
}

echo "\nJournal entries structure check completed!\n";
