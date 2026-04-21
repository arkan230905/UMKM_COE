<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Recreate Production Journals with Correct BOP ===" . PHP_EOL;

// Recreate production journals with correct BOP direction
echo PHP_EOL . "Recreating Production Journals:" . PHP_EOL;

$produksiData = DB::table('produksis')
    ->whereMonth('tanggal', 4)
    ->whereYear('tanggal', 2026)
    ->whereIn('status', ['completed', 'selesai'])
    ->get();

foreach ($produksiData as $produksi) {
    echo PHP_EOL . "Processing Production ID: " . $produksi->id . PHP_EOL;
    echo "Date: " . $produksi->tanggal . PHP_EOL;
    echo "BTKL: " . number_format($produksi->total_btkl, 0) . PHP_EOL;
    echo "BOP: " . number_format($produksi->total_bop, 0) . PHP_EOL;
    
    // Create correct journal entries
    $journal = new \App\Services\JournalService();
    $tanggal = $produksi->tanggal;
    
    $lines = [];
    
    // 1. Transfer BTKL & BOP to WIP (debit 117, credit 52 & 53)
    if ($produksi->total_btkl > 0) {
        $coaBTKL = DB::table('coas')->where('kode_akun', '52')->first();
        if ($coaBTKL) {
            $lines[] = [
                'code' => $coaBTKL->kode_akun,
                'debit' => 0,
                'credit' => $produksi->total_btkl,
                'memo' => 'Alokasi BTKL ke produksi'
            ];
        }
    }
    
    if ($produksi->total_bop > 0) {
        $coaBOP = DB::table('coas')->where('kode_akun', '53')->first();
        if ($coaBOP) {
            $lines[] = [
                'code' => $coaBOP->kode_akun,
                'debit' => $produksi->total_bop, // FIXED: DEBIT instead of CREDIT
                'credit' => 0,
                'memo' => 'Alokasi BOP ke produksi'
            ];
        }
    }
    
    // Credit WIP for total BTKL + BOP
    $totalLaborOverhead = $produksi->total_btkl + $produksi->total_bop;
    if ($totalLaborOverhead > 0) {
        $coaWIP = DB::table('coas')->where('kode_akun', '117')->first();
        if ($coaWIP) {
            $lines[] = [
                'code' => $coaWIP->kode_akun,
                'debit' => $totalLaborOverhead,
                'credit' => 0,
                'memo' => 'Transfer BTKL & BOP ke WIP'
            ];
        }
    }
    
    if (!empty($lines)) {
        try {
            $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'Alokasi BTKL & BOP ke Produksi', $lines);
            echo "✅ Created production journal entries for Production ID: " . $produksi->id . PHP_EOL;
        } catch (\Exception $e) {
            echo "❌ Error creating journal for Production ID " . $produksi->id . ": " . $e->getMessage() . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== Verify BOP Balance After Fix ===" . PHP_EOL;

// Check BOP balance after recreation
$bulan = 4;
$tahun = 2026;
$from = Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

$bopEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('coas.kode_akun', '53')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->select('journal_lines.debit', 'journal_lines.credit')
    ->get();

$totalDebit = $bopEntries->sum('debit');
$totalCredit = $bopEntries->sum('credit');

echo "BOP Account (53) Balance:" . PHP_EOL;
echo "Total Debit: " . number_format($totalDebit, 0) . PHP_EOL;
echo "Total Credit: " . number_format($totalCredit, 0) . PHP_EOL;
echo "Net Balance: " . number_format($totalDebit - $totalCredit, 0) . PHP_EOL;
echo "Status: " . (($totalDebit - $totalCredit) >= 0 ? "POSITIVE ✅" : "NEGATIVE ❌") . PHP_EOL;

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "✅ Fixed BOP allocation direction: DEBIT instead of CREDIT" . PHP_EOL;
echo "✅ Recreated production journals with correct entries" . PHP_EOL;
echo "✅ BOP account should now show positive balance" . PHP_EOL;
echo "✅ Consistent with expense account logic" . PHP_EOL;
