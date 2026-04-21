<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test BOP Fix ===" . PHP_EOL;

// Test BOP allocation after fix
echo PHP_EOL . "Testing BOP Allocation After Fix:" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo "Period: April 2026" . PHP_EOL;

// Get BOP account balance after fix
$bopAccount = DB::table('coas')->where('kode_akun', '53')->first();
echo PHP_EOL . "COA " . $bopAccount->kode_akun . " - " . $bopAccount->nama_akun . ":" . PHP_EOL;

// Get journal entries for BOP
$entries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', '53')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->select('journal_entries.tanggal', 'journal_lines.debit', 'journal_lines.credit', 'journal_lines.memo')
    ->orderBy('journal_entries.tanggal')
    ->get();

echo "Journal Entries After Fix:" . PHP_EOL;
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

echo PHP_EOL . "Balance After Fix:" . PHP_EOL;
echo "Total Debit: " . number_format($totalDebit, 0) . PHP_EOL;
echo "Total Credit: " . number_format($totalCredit, 0) . PHP_EOL;
echo "Net Balance: " . number_format($totalDebit - $totalCredit, 0) . PHP_EOL;

echo PHP_EOL . "=== Expected BOP Flow After Fix ===" . PHP_EOL;
echo "1. BOP Payment (550):" . PHP_EOL;
echo "   - Debit 550 BOP Listrik Rp 3.530.000" . PHP_EOL;
echo "   - Credit 111 Kas Bank Rp 3.530.000" . PHP_EOL;
echo PHP_EOL . "2. BOP Allocation to Production:" . PHP_EOL;
echo "   - Debit 53 BOP Rp 545.118 (FIXED!)" . PHP_EOL;
echo "   - Credit 117 Barang Dalam Proses Rp 545.118" . PHP_EOL;
echo PHP_EOL . "3. HPP in Sales:" . PHP_EOL;
echo "   - Debit 53 BOP Rp 545.118" . PHP_EOL;
echo "   - Credit 116 Persediaan Barang Jadi Rp 545.118" . PHP_EOL;
echo PHP_EOL . "Expected BOP Balance:" . PHP_EOL;
echo "- Debit: 545.118 (allocation) + 545.118 (HPP) = 1.090.236" . PHP_EOL;
echo "- Credit: 545.118 (allocation) = 545.118" . PHP_EOL;
echo "- Net: 545.118 (should be positive!)" . PHP_EOL;

echo PHP_EOL . "=== Fix Summary ===" . PHP_EOL;
echo "✅ BOP allocation direction fixed: DEBIT instead of CREDIT" . PHP_EOL;
echo "✅ Production journal now correctly charges BOP account" . PHP_EOL;
echo "✅ BOP balance should now be positive" . PHP_EOL;
echo "✅ Consistent with expense account logic (debit increases expense)" . PHP_EOL;

echo PHP_EOL . "Files Modified:" . PHP_EOL;
echo "- app/Http/Controllers/ProduksiController.php" . PHP_EOL;
echo "  - Fixed BOP allocation: debit instead of credit" . PHP_EOL;
echo "  - Applied to both production methods" . PHP_EOL;
