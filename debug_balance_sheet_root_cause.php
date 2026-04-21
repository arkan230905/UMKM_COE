<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug Balance Sheet Root Cause ===" . PHP_EOL;

// Find the root cause of the 101M difference
echo PHP_EOL . "=== Root Cause Analysis ===" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

// Check all journal entries for April 2026
echo "All Journal Entries Summary:" . PHP_EOL;

$allEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->select('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun', 
             DB::raw('SUM(journal_lines.debit) as total_debit'),
             DB::raw('SUM(journal_lines.credit) as total_credit'))
    ->groupBy('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->orderBy('coas.kode_akun')
    ->get();

$totalDebitAll = 0;
$totalCreditAll = 0;

echo PHP_EOL . "Journal Entries by Account:" . PHP_EOL;
foreach ($allEntries as $entry) {
    if ($entry->total_debit > 0 || $entry->total_credit > 0) {
        echo $entry->kode_akun . " " . $entry->nama_akun . " (" . $entry->tipe_akun . ")" . PHP_EOL;
        echo "  Debit: " . number_format($entry->total_debit, 0) . PHP_EOL;
        echo "  Credit: " . number_format($entry->total_credit, 0) . PHP_EOL;
        
        $totalDebitAll += $entry->total_debit;
        $totalCreditAll += $entry->total_credit;
        echo "---" . PHP_EOL;
    }
}

echo PHP_EOL . "Journal Entries Summary:" . PHP_EOL;
echo "Total Debit All: " . number_format($totalDebitAll, 0) . PHP_EOL;
echo "Total Credit All: " . number_format($totalCreditAll, 0) . PHP_EOL;
echo "Difference: " . number_format($totalDebitAll - $totalCreditAll, 0) . PHP_EOL;

// Check if the journal entries are balanced
if ($totalDebitAll == $totalCreditAll) {
    echo "Journal Entries are BALANCED" . PHP_EOL;
} else {
    echo "Journal Entries are NOT BALANCED!" . PHP_EOL;
}

// Check the balance sheet equation
echo PHP_EOL . "=== Balance Sheet Equation Check ===" . PHP_EOL;

// Calculate assets
$assetAccounts = $allEntries->filter(function($entry) {
    return in_array($entry->tipe_akun, ['Aset']);
});

$totalAssets = 0;
foreach ($assetAccounts as $account) {
    $coa = DB::table('coas')->where('kode_akun', $account->kode_akun)->first();
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    // Asset accounts: Saldo Awal + Debit - Credit
    $finalBalance = $saldoAwal + $account->total_debit - $account->total_credit;
    $totalAssets += $finalBalance;
}

echo "Total Assets: " . number_format($totalAssets, 0) . PHP_EOL;

// Calculate liabilities
$liabilityAccounts = $allEntries->filter(function($entry) {
    return in_array($entry->tipe_akun, ['Kewajiban']);
});

$totalLiabilities = 0;
foreach ($liabilityAccounts as $account) {
    $coa = DB::table('coas')->where('kode_akun', $account->kode_akun)->first();
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    // Liability accounts: Saldo Awal + Credit - Debit
    $finalBalance = $saldoAwal + $account->total_credit - $account->total_debit;
    $totalLiabilities += $finalBalance;
}

echo "Total Liabilities: " . number_format($totalLiabilities, 0) . PHP_EOL;

// Calculate equity (including retained earnings)
$equityAccounts = $allEntries->filter(function($entry) {
    return in_array($entry->tipe_akun, ['Modal']);
});

$totalEquity = 0;
foreach ($equityAccounts as $account) {
    $coa = DB::table('coas')->where('kode_akun', $account->kode_akun)->first();
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    // Equity accounts: Saldo Awal + Credit - Debit
    $finalBalance = $saldoAwal + $account->total_credit - $account->total_debit;
    $totalEquity += $finalBalance;
}

echo "Total Equity: " . number_format($totalEquity, 0) . PHP_EOL;

// Add retained earnings (revenue - expenses)
$revenueAccounts = $allEntries->filter(function($entry) {
    return in_array($entry->tipe_akun, ['Pendapatan']);
});

$expenseAccounts = $allEntries->filter(function($entry) {
    return in_array($entry->tipe_akun, ['Expense']);
});

$totalRevenue = 0;
foreach ($revenueAccounts as $account) {
    $coa = DB::table('coas')->where('kode_akun', $account->kode_akun)->first();
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    // Revenue accounts: Credit - Debit (reduces equity)
    $finalBalance = $saldoAwal + $account->total_credit - $account->total_debit;
    $totalRevenue += $finalBalance;
}

$totalExpenses = 0;
foreach ($expenseAccounts as $account) {
    $coa = DB::table('coas')->where('kode_akun', $account->kode_akun)->first();
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    // Expense accounts: Debit - Credit (reduces equity)
    $finalBalance = $saldoAwal + $account->total_debit - $account->total_credit;
    $totalExpenses += $finalBalance;
}

$retainedEarnings = $totalRevenue - $totalExpenses;
echo "Retained Earnings (Revenue - Expenses): " . number_format($retainedEarnings, 0) . PHP_EOL;

$totalEquityWithRetained = $totalEquity + $retainedEarnings;
echo "Total Equity with Retained Earnings: " . number_format($totalEquityWithRetained, 0) . PHP_EOL;

echo PHP_EOL . "=== Final Balance Check ===" . PHP_EOL;
echo "Assets: " . number_format($totalAssets, 0) . PHP_EOL;
echo "Liabilities: " . number_format($totalLiabilities, 0) . PHP_EOL;
echo "Equity + Retained: " . number_format($totalEquityWithRetained, 0) . PHP_EOL;
echo "Liabilities + Equity: " . number_format($totalLiabilities + $totalEquityWithRetained, 0) . PHP_EOL;
echo "Difference: " . number_format($totalAssets - ($totalLiabilities + $totalEquityWithRetained), 0) . PHP_EOL;
