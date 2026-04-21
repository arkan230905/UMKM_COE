<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug BOP Negative Balance Issue ===" . PHP_EOL;

// Analyze BOP account balance issue
echo PHP_EOL . "BOP Account Analysis:" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo "Period: April 2026" . PHP_EOL;

// Get BOP account balances
$bopAccounts = DB::table('coas')
    ->where('kode_akun', '53')
    ->orWhere('kode_akun', '550')
    ->get();

foreach ($bopAccounts as $coa) {
    echo PHP_EOL . "COA " . $coa->kode_akun . " - " . $coa->nama_akun . ":" . PHP_EOL;
    
    // Get journal entries for this account
    $entries = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('coas.kode_akun', $coa->kode_akun)
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->select('journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo')
        ->orderBy('journal_entries.tanggal')
        ->get();
    
    echo "Journal Entries:" . PHP_EOL;
    $totalDebit = 0;
    $totalCredit = 0;
    
    foreach ($entries as $entry) {
        $totalDebit += $entry->debit;
        $totalCredit += $entry->credit;
        
        echo sprintf(
            "%s | %s | %s | %s",
            $entry->tanggal,
            number_format($entry->debit, 0),
            number_format($entry->credit, 0),
            $entry->memo
        ) . PHP_EOL;
    }
    
    echo "Total Debit: " . number_format($totalDebit, 0) . PHP_EOL;
    echo "Total Credit: " . number_format($totalCredit, 0) . PHP_EOL;
    echo "Net Balance: " . number_format($totalDebit - $totalCredit, 0) . PHP_EOL;
}

echo PHP_EOL . "=== Production BOP Allocation Analysis ===" . PHP_EOL;

// Check production BOP allocation
$productionEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->where('journal_lines.memo', 'like', '%Alokasi BOP%')
    ->select('journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo')
    ->orderBy('journal_entries.tanggal')
    ->get();

echo "Production BOP Allocations:" . PHP_EOL;
foreach ($productionEntries as $entry) {
    echo sprintf(
        "%s | %s | %s | %s",
        $entry->tanggal,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0),
        $entry->memo
    ) . PHP_EOL;
}

echo PHP_EOL . "=== BOP Payment Analysis ===" . PHP_EOL;

// Check BOP payments
$bopPayments = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->where(function($query) {
        $query->where('journal_lines.memo', 'like', '%Pembayaran Beban%')
              ->orWhere('journal_lines.memo', 'like', '%Listrik%');
    })
    ->where('coas.kode_akun', '550')
    ->select('journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo')
    ->orderBy('journal_entries.tanggal')
    ->get();

echo "BOP Payments:" . PHP_EOL;
foreach ($bopPayments as $entry) {
    echo sprintf(
        "%s | %s | %s | %s",
        $entry->tanggal,
        number_format($entry->debit, 0),
        number_format($entry->credit, 0),
        $entry->memo
    ) . PHP_EOL;
}

echo PHP_EOL . "=== Issue Analysis ===" . PHP_EOL;
echo "Problem: BOP (53) shows negative balance of Rp -1.090.237" . PHP_EOL;
echo "Root Cause Analysis:" . PHP_EOL;

// Check if BOP is being allocated to production without proper credit
$productionBOPCredit = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->where('journal_lines.memo', 'like', '%Alokasi BOP%')
    ->where('coas.kode_akun', '53')
    ->sum('journal_lines.credit');

$productionBOPDebit = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->where('journal_lines.memo', 'like', '%Alokasi BOP%')
    ->where('coas.kode_akun', '53')
    ->sum('journal_lines.debit');

echo "Production BOP Debit: " . number_format($productionBOPDebit, 0) . PHP_EOL;
echo "Production BOP Credit: " . number_format($productionBOPCredit, 0) . PHP_EOL;
echo "Production BOP Net: " . number_format($productionBOPDebit - $productionBOPCredit, 0) . PHP_EOL;

echo PHP_EOL . "=== Expected BOP Flow ===" . PHP_EOL;
echo "1. BOP Payment (550): Debit Rp 3.530.000" . PHP_EOL;
echo "   - Debit 550 BOP Listrik Rp 3.530.000" . PHP_EOL;
echo "   - Credit 111 Kas Bank Rp 3.530.000" . PHP_EOL;
echo PHP_EOL . "2. BOP Allocation to Production:" . PHP_EOL;
echo "   - Debit 117 Barang Dalam Proses Rp 545.118" . PHP_EOL;
echo "   - Credit 53 BOP Rp 545.118 (should be CREDIT!)" . PHP_EOL;
echo PHP_EOL . "3. HPP in Sales:" . PHP_EOL;
echo "   - Debit 53 BOP Rp 545.118" . PHP_EOL;
echo "   - Credit 116 Persediaan Barang Jadi Rp 545.118" . PHP_EOL;
echo PHP_EOL . "Current Issue: BOP allocation is DEBIT instead of CREDIT!" . PHP_EOL;
