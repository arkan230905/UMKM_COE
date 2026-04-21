<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug Negative Cash Balances ===" . PHP_EOL;

// Check why cash accounts show negative values
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
    echo "Type: " . $coa->tipe_akun . PHP_EOL;
    echo "Saldo Normal: " . $coa->saldo_normal . PHP_EOL;
    echo "Saldo Awal: " . $coa->saldo_awal . PHP_EOL;
    
    // Get journal entries
    $journalEntries = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $kodeAkun)
        ->select('journal_entries.tanggal', 'journal_entries.deskripsi', 'journal_lines.debit', 'journal_lines.credit')
        ->orderBy('journal_entries.tanggal')
        ->get();
    
    echo PHP_EOL . "Journal Entries:" . PHP_EOL;
    $totalDebit = 0;
    $totalKredit = 0;
    
    foreach ($journalEntries as $entry) {
        echo date('Y-m-d', strtotime($entry->tanggal)) . " - " . $entry->deskripsi . PHP_EOL;
        echo "  Debit: " . $entry->debit . PHP_EOL;
        echo "  Credit: " . $entry->credit . PHP_EOL;
        
        $totalDebit += $entry->debit;
        $totalKredit += $entry->credit;
    }
    
    echo PHP_EOL . "Totals:" . PHP_EOL;
    echo "Total Debit: " . $totalDebit . PHP_EOL;
    echo "Total Credit: " . $totalKredit . PHP_EOL;
    
    // Calculate final balance using different methods
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    // Method 1: Standard asset calculation (Saldo Awal + Debit - Credit)
    $method1 = $saldoAwal + $totalDebit - $totalKredit;
    
    // Method 2: Using saldo_normal
    $isDebitNormal = strtolower($coa->saldo_normal ?? '') === 'debit';
    if ($isDebitNormal) {
        $method2 = $saldoAwal + $totalDebit - $totalKredit;
    } else {
        $method2 = $saldoAwal + $totalKredit - $totalDebit;
    }
    
    // Method 3: Using tipe_akun
    $isAsset = in_array(strtolower($coa->tipe_akun), ['asset', 'aset']);
    if ($isAsset) {
        $method3 = $saldoAwal + $totalDebit - $totalKredit;
    } else {
        $method3 = $saldoAwal + $totalKredit - $totalDebit;
    }
    
    echo PHP_EOL . "Balance Calculations:" . PHP_EOL;
    echo "Method 1 (Standard Asset): " . $method1 . PHP_EOL;
    echo "Method 2 (Using saldo_normal): " . $method2 . PHP_EOL;
    echo "Method 3 (Using tipe_akun): " . $method3 . PHP_EOL;
    
    echo PHP_EOL . "Expected Balance (from report):" . PHP_EOL;
    if ($kodeAkun == '111') {
        echo "Report shows: Rp 93.867.050" . PHP_EOL;
    } elseif ($kodeAkun == '112') {
        echo "Report shows: Rp 72.398.100" . PHP_EOL;
    }
    
    echo "---" . PHP_EOL;
}

// Check what the actual calculation should be
echo PHP_EOL . "=== Correct Calculation Check ===" . PHP_EOL;

echo "For Kas Bank (111):" . PHP_EOL;
echo "Saldo Awal: 100.000.000" . PHP_EOL;
echo "Total Debit: 1.393.050" . PHP_EOL;
echo "Total Credit: 7.526.000" . PHP_EOL;
echo "Expected: 100.000.000 + 1.393.050 - 7.526.000 = 93.867.050" . PHP_EOL;

echo PHP_EOL . "For Kas (112):" . PHP_EOL;
echo "Saldo Awal: 75.000.000" . PHP_EOL;
echo "Total Debit: 2.786.100" . PHP_EOL;
echo "Total Credit: 5.388.000" . PHP_EOL;
echo "Expected: 75.000.000 + 2.786.100 - 5.388.000 = 72.398.100" . PHP_EOL;

echo PHP_EOL . "The issue might be in the getFinalBalance function logic!" . PHP_EOL;
