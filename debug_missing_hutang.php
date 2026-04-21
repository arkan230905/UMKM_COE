<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug Missing Hutang (Liabilities) ===" . PHP_EOL;

// Check why hutang usaha doesn't appear in balance sheet
echo PHP_EOL . "=== Hutang Usaha Analysis ===" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

// Check all hutang accounts
$hutangAccounts = ['210', '2101', '211']; // Hutang Usaha, Hutang Gaji

foreach ($hutangAccounts as $kodeAkun) {
    echo PHP_EOL . "Analyzing COA " . $kodeAkun . ":" . PHP_EOL;
    
    // Get COA info
    $coa = DB::table('coas')->where('kode_akun', $kodeAkun)->first();
    if (!$coa) {
        echo "COA not found" . PHP_EOL;
        continue;
    }
    
    echo "Account: " . $coa->nama_akun . PHP_EOL;
    echo "Type: " . $coa->tipe_akun . PHP_EOL;
    echo "Category: " . $coa->kategori_akun . PHP_EOL;
    echo "Saldo Awal: " . $coa->saldo_awal . PHP_EOL;
    
    // Get journal totals
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $kodeAkun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $kodeAkun)
        ->sum('journal_lines.credit');
    
    echo "Total Debit: " . number_format($totalDebit, 0) . PHP_EOL;
    echo "Total Credit: " . number_format($totalKredit, 0) . PHP_EOL;
    
    // Calculate final balance (liability accounts are credit normal)
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit;
    
    echo "Final Balance: " . number_format($saldoAkhir, 0) . PHP_EOL;
    
    // Check if this should be included in balance sheet
    echo "Balance Sheet Filter Check:" . PHP_EOL;
    
    // Check liability type filter
    $isLiability = in_array($coa->tipe_akun, ['Liability', 'liability', 'Pasiva', 'Kewajiban']);
    echo "  Is Liability: " . ($isLiability ? "YES" : "NO") . PHP_EOL;
    
    // Check if it's a parent account
    $isParent = false; // Simplified check
    echo "  Is Parent: " . ($isParent ? "YES" : "NO") . PHP_EOL;
    
    // Check if balance is positive (for liabilities)
    $hasPositiveBalance = $saldoAkhir > 0;
    echo "  Has Positive Balance: " . ($hasPositiveBalance ? "YES" : "NO") . PHP_EOL;
    
    // Check if it's short-term liability
    $isShortTerm = (stripos($coa->kategori_akun, 'Hutang') !== false &&
                    stripos($coa->kategori_akun, 'Jangka Panjang') === false) ||
                   (stripos($coa->nama_akun, 'Hutang Usaha') !== false) ||
                   (stripos($coa->nama_akun, 'Hutang Pajak') !== false);
    echo "  Is Short Term: " . ($isShortTerm ? "YES" : "NO") . PHP_EOL;
    
    // Final check: should this be included?
    $shouldBeIncluded = $isLiability && !$isParent && $hasPositiveBalance && $isShortTerm;
    echo "  Should Be Included: " . ($shouldBeIncluded ? "YES" : "NO") . PHP_EOL;
    
    echo "---" . PHP_EOL;
}

// Check if there are any purchase transactions that should create hutang
echo PHP_EOL . "=== Purchase Transactions Check ===" . PHP_EOL;

$purchaseTransactions = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->where('coas.kode_akun', '2101') // Hutang Usaha
    ->get();

echo "Journal entries for Hutang Usaha (2101): " . $purchaseTransactions->count() . PHP_EOL;

foreach ($purchaseTransactions as $entry) {
    echo date('Y-m-d', strtotime($entry->tanggal)) . " - Debit: " . $entry->debit . ", Credit: " . $entry->credit . PHP_EOL;
}

// Check if there are any journal entries that reference purchases
echo PHP_EOL . "=== Purchase Journal Entries ===" . PHP_EOL;

$purchaseJournals = DB::table('journal_entries')
    ->where('tanggal', '>=', $from)
    ->where('tanggal', '<=', $to)
    ->where('ref_type', 'purchase')
    ->get();

echo "Purchase journal entries: " . $purchaseJournals->count() . PHP_EOL;
foreach ($purchaseJournals as $journal) {
    echo date('Y-m-d', strtotime($journal->tanggal)) . " - " . $journal->ref_type . PHP_EOL;
}

echo PHP_EOL . "=== Expected Fix ===" . PHP_EOL;
echo "The issue might be:" . PHP_EOL;
echo "1. Hutang accounts have 0 balance (debit = credit)" . PHP_EOL;
echo "2. Purchase transactions are not creating proper hutang" . PHP_EOL;
echo "3. Balance sheet filtering excludes zero-balance liabilities" . PHP_EOL;
