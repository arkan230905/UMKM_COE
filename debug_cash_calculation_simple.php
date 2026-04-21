<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug Cash Calculation Simple ===" . PHP_EOL;

// Check cash account calculations
echo PHP_EOL . "=== Cash Account Analysis ===" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

$cashAccounts = ['111', '112']; // Kas Bank, Kas

foreach ($cashAccounts as $kodeAkun) {
    echo PHP_EOL . "Analyzing COA " . $kodeAkun . ":" . PHP_EOL;
    
    // Get COA info
    $coa = DB::table('coas')->where('kode_akun', $kodeAkun)->first();
    echo "Account: " . $coa->nama_akun . PHP_EOL;
    echo "Saldo Awal: " . number_format($coa->saldo_awal, 0) . PHP_EOL;
    
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
    
    // Calculate final balance
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
    
    echo "Final Balance: " . number_format($saldoAkhir, 0) . PHP_EOL;
    
    // Compare with report
    if ($kodeAkun == '111') {
        echo "Report shows: Rp 93.867.050" . PHP_EOL;
        echo "Expected: 100.000.000 + 1.393.050 - 7.526.000 = 93.867.050" . PHP_EOL;
        echo "Match: " . ($saldoAkhir == 93867050 ? "YES" : "NO") . PHP_EOL;
    } elseif ($kodeAkun == '112') {
        echo "Report shows: Rp 72.398.100" . PHP_EOL;
        echo "Expected: 75.000.000 + 2.786.100 - 5.388.000 = 72.398.100" . PHP_EOL;
        echo "Match: " . ($saldoAkhir == 72398100 ? "YES" : "NO") . PHP_EOL;
    }
    
    echo "---" . PHP_EOL;
}

// Check the total calculation
echo PHP_EOL . "=== Balance Sheet Balance Check ===" . PHP_EOL;

// Calculate total assets (should be positive)
$totalAssets = 93867050 + 72398100 + 396000 + 1920000 + 600000 + 2500000 + 19040000 + 19360000 + 5040000 + 15936000 + 26700800 + 3527518 + 3031518;
echo "Total Assets: " . number_format($totalAssets, 0) . PHP_EOL;

// Calculate total liabilities (should be 0 based on our check)
$totalLiabilities = 0;
echo "Total Liabilities: " . number_format($totalLiabilities, 0) . PHP_EOL;

// Calculate total equity
$totalEquity = 175000000 - 11741313; // Modal - Laba/Rugi
echo "Total Equity: " . number_format($totalEquity, 0) . PHP_EOL;

echo PHP_EOL . "Balance Check:" . PHP_EOL;
echo "Assets: " . number_format($totalAssets, 0) . PHP_EOL;
echo "Liabilities + Equity: " . number_format($totalLiabilities + $totalEquity, 0) . PHP_EOL;
echo "Difference: " . number_format($totalAssets - ($totalLiabilities + $totalEquity), 0) . PHP_EOL;

echo PHP_EOL . "The issue is clear: Assets are much higher than Liabilities + Equity" . PHP_EOL;
echo "This suggests there might be missing liability accounts or incorrect equity calculation" . PHP_EOL;
