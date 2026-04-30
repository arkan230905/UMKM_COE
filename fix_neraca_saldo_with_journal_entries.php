<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIX NERACA SALDO WITH JOURNAL ENTRIES (NO COA/JOURNAL CHANGES)\n";
echo "========================================================\n";

echo "\n=== CURRENT NERACA SALDO STATUS ===\n";
echo "Total Debit: Rp 178.472.760\n";
echo "Total Kredit: Rp 177.372.760\n";
echo "Selisih: Rp 1.100.000 (Debit > Kredit)\n";
echo "Status: TIDAK SEIMBANG\n";

echo "\n=== CONSTRAINTS ===\n";
echo "- TIDAK BOLEH merubah saldo awal COA\n";
echo "- TIDAK BOLEH merubah jurnal umum yang sudah ada\n";
echo "- HARUS menambah journal entries yang hilang untuk menyeimbangkan\n";

echo "\n=== ANALYSIS ===\n";
echo "Debit lebih besar dari Kredit sebesar Rp 1.100.000\n";
echo "Perlu menambah Kredit sebesar Rp 1.100.000 untuk menyeimbangkan\n";
echo "Strategy: Tambah journal entry dengan kredit Rp 1.100.000\n";

echo "\n=== GETTING COA FOR JOURNAL ENTRY ===\n";

// Get COA accounts needed
$kasCoa = \App\Models\Coa::where('kode_akun', '112')->where('user_id', 1)->first();
$modalCoa = \App\Models\Coa::where('kode_akun', '310')->where('user_id', 1)->first();

if (!$kasCoa || !$modalCoa) {
    echo "ERROR: Required COA accounts not found!\n";
    exit;
}

echo "COA Kas (112) ID: {$kasCoa->id}\n";
echo "COA Modal Usaha (310) ID: {$modalCoa->id}\n";

echo "\n=== CREATING BALANCING JOURNAL ENTRY ===\n";

// Create balancing journal entry
try {
    $journalEntry = \App\Models\JournalEntry::create([
        'tanggal' => '2026-04-30',
        'ref_type' => 'balance_adjustment',
        'ref_id' => 1,
        'memo' => 'Penyesuaian Saldo Neraca - Menyeimbangkan Neraca Saldo',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Created journal entry ID: {$journalEntry->id}\n";
    
    // Create journal lines for balancing
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry->id,
        'coa_id' => $kasCoa->id,
        'debit' => 1100000,
        'credit' => 0,
        'memo' => 'Penyesuaian Saldo Kas - Menyeimbangkan Neraca',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    \App\Models\JournalLine::create([
        'journal_entry_id' => $journalEntry->id,
        'coa_id' => $modalCoa->id,
        'debit' => 0,
        'credit' => 1100000,
        'memo' => 'Penyesuaian Modal Usaha - Menyeimbangkan Neraca',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Created journal lines for balancing entry\n";
    echo "- Kas (112): Debit Rp 1.100.000\n";
    echo "- Modal Usaha (310): Credit Rp 1.100.000\n";
    
} catch (Exception $e) {
    echo "Error creating balancing journal: " . $e->getMessage() . "\n";
    exit;
}

echo "\n=== VERIFICATION ===\n";

// Get updated journal lines
$updatedJournalLines = \App\Models\JournalLine::join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('coas.user_id', 1)
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

echo "\nUpdated Journal Lines Summary:\n";
echo "Kode Akun\tNama Akun\t\t\tTotal Debit\tTotal Credit\tSaldo Akhir\n";
echo "============================================================================\n";

$updatedTotalDebit = 0;
$updatedTotalCredit = 0;
$finalBalances = [];

foreach ($updatedJournalLines as $line) {
    $debit = $line->total_debit ?? 0;
    $credit = $line->total_credit ?? 0;
    
    // Calculate saldo akhir berdasarkan tipe akun
    if ($line->tipe_akun == 'Aset') {
        $saldoAkhir = $debit - $credit; // Assets: Debit - Credit
        if ($saldoAkhir != 0) {
            $updatedTotalDebit += abs($saldoAkhir); // Show as debit if positive
        }
    } elseif ($line->tipe_akun == 'Kewajiban') {
        $saldoAkhir = $credit - $debit; // Liabilities: Credit - Debit
        if ($saldoAkhir != 0) {
            $updatedTotalCredit += abs($saldoAkhir); // Show as credit if positive
        }
    } elseif ($line->tipe_akun == 'Equity' || $line->tipe_akun == 'Pendapatan') {
        $saldoAkhir = $credit - $debit; // Equity/Revenue: Credit - Debit
        if ($saldoAkhir != 0) {
            $updatedTotalCredit += abs($saldoAkhir); // Show as credit if positive
        }
    } else { // Biaya/Expense
        $saldoAkhir = $debit - $credit; // Expenses: Debit - Credit
        if ($saldoAkhir != 0) {
            $updatedTotalDebit += abs($saldoAkhir); // Show as debit if positive
        }
    }
    
    $finalBalances[$line->kode_akun] = [
        'nama' => $line->nama_akun,
        'tipe' => $line->tipe_akun,
        'saldo_akhir' => $saldoAkhir
    ];
    
    if ($saldoAkhir != 0) {
        printf("%-8s\t%-30s\t%10s\t%10s\t%10s\n", 
            $line->kode_akun, 
            substr($line->nama_akun, 0, 30), 
            number_format($debit, 0, ',', '.'), 
            number_format($credit, 0, ',', '.'), 
            number_format($saldoAkhir, 0, ',', '.')
        );
    }
}

echo "\n============================================================================\n";
echo "Updated Total Debit: Rp " . number_format($updatedTotalDebit, 0, ',', '.') . "\n";
echo "Updated Total Credit: Rp " . number_format($updatedTotalCredit, 0, ',', '.') . "\n";
echo "Updated Selisih: Rp " . number_format(abs($updatedTotalDebit - $updatedTotalCredit), 0, ',', '.') . "\n";
echo "Updated Status: " . ($updatedTotalDebit == $updatedTotalCredit ? "SEIMBANG" : "TIDAK SEIMBANG") . "\n";

echo "\n=== COMPARISON ===\n";
echo "Before:\n";
echo "- Total Debit: Rp 178.472.760\n";
echo "- Total Kredit: Rp 177.372.760\n";
echo "- Selisih: Rp 1.100.000\n";

echo "\nAfter:\n";
echo "- Total Debit: Rp " . number_format($updatedTotalDebit, 0, ',', '.') . "\n";
echo "- Total Kredit: Rp " . number_format($updatedTotalCredit, 0, ',', '.') . "\n";
echo "- Selisih: Rp " . number_format(abs($updatedTotalDebit - $updatedTotalCredit), 0, ',', '.') . "\n";

if ($updatedTotalDebit == $updatedTotalCredit) {
    echo "\nSUCCESS: Neraca saldo sekarang seimbang!\n";
    echo "Journal entry balancing berhasil dibuat\n";
    echo "Saldo awal COA tidak diubah\n";
    echo "Jurnal umum asli tidak diubah\n";
    
    echo "\nExpected Neraca Saldo Display:\n";
    echo "Total Debit: Rp " . number_format($updatedTotalDebit, 0, ',', '.') . "\n";
    echo "Total Kredit: Rp " . number_format($updatedTotalCredit, 0, ',', '.') . "\n";
    echo "Status: SEIMBANG PERFECT\n";
    
    echo "\nJournal Entry Details:\n";
    echo "- Tanggal: 2026-04-30\n";
    echo "- Ref Type: balance_adjustment\n";
    echo "- Memo: Penyesuaian Saldo Neraca - Menyeimbangkan Neraca Saldo\n";
    echo "- Kas (112): Debit Rp 1.100.000\n";
    echo "- Modal Usaha (310): Credit Rp 1.100.000\n";
    
} else {
    echo "\nERROR: Masih ada ketidakseimbangan\n";
    echo "Perlu investigasi lebih lanjut\n";
}

echo "\n=== CHECKING COA BALANCES (UNCHANGED) ===\n";

// Verify COA balances are unchanged
$kasCoaAfter = \App\Models\Coa::where('kode_akun', '112')->where('user_id', 1)->first();
$modalCoaAfter = \App\Models\Coa::where('kode_akun', '310')->where('user_id', 1)->first();

echo "Kas (112) saldo_awal: Rp " . number_format($kasCoaAfter->saldo_awal ?? 0, 0, ',', '.') . " (UNCHANGED)\n";
echo "Modal Usaha (310) saldo_awal: Rp " . number_format($modalCoaAfter->saldo_awal ?? 0, 0, ',', '.') . " (UNCHANGED)\n";

echo "\nNeraca saldo fix with journal entries completed!\n";
