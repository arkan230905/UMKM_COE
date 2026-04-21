<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug Neraca Saldo Balance Issue ===" . PHP_EOL;

// Check why assets don't equal liabilities + equity
echo PHP_EOL . "Balance Sheet Analysis:" . PHP_EOL;
echo "Assets: Rp 264.316.987" . PHP_EOL;
echo "Liabilities + Equity: Rp 179.179.150" . PHP_EOL;
echo "Difference: Rp 85.137.837" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo PHP_EOL . "=== Detailed Account Analysis ===" . PHP_EOL;

// Get all accounts and calculate balances
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
    ->orderBy('kode_akun')
    ->get();

echo "All Accounts with Non-Zero Balances:" . PHP_EOL;

$totalAssets = 0;
$totalLiabilities = 0;
$totalEquity = 0;

foreach ($coas as $coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    
    // Calculate balance
    $saldoAwal = 0;
    
    // For inventory accounts, use getInventorySaldoAwal logic
    if (in_array($coa->kode_akun, ['1141', '1142', '1143', '1152', '1153', '1154', '1155', '1156'])) {
        if (in_array($coa->kode_akun, ['1141', '1142', '1143'])) {
            $saldoAwal = DB::table('bahan_bakus')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        } elseif (in_array($coa->kode_akun, ['1152', '1153', '1154', '1155', '1156'])) {
            $saldoAwal = DB::table('bahan_pendukungs')
                ->where('coa_persediaan_id', $coa->kode_akun)
                ->where('saldo_awal', '>', 0)
                ->sum(DB::raw('saldo_awal * harga_satuan'));
        }
    } else {
        $saldoAwal = (float)($coa->saldo_awal ?? 0);
    }
    
    // Get journal totals
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.credit');
    
    // Calculate final balance using corrected position logic
    $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
    } else {
        $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit;
    }
    
    // Only show accounts with non-zero balance
    if ($saldoAkhir != 0) {
        echo $coa->kode_akun . " - " . $coa->nama_akun . PHP_EOL;
        echo "  Type: " . $coa->tipe_akun . PHP_EOL;
        echo "  Category: " . $firstDigit . "xx" . PHP_EOL;
        echo "  Position: " . ($isDebitNormal ? "Debit" : "Kredit") . PHP_EOL;
        echo "  Balance: " . number_format($saldoAkhir, 0) . PHP_EOL;
        
        // Categorize
        if ($firstDigit === '1') {
            $totalAssets += $saldoAkhir;
            echo "  Category: ASSET" . PHP_EOL;
        } elseif (in_array($firstDigit, ['2', '3', '4'])) {
            if ($firstDigit === '2') {
                $totalLiabilities += $saldoAkhir;
                echo "  Category: LIABILITY" . PHP_EOL;
            } else {
                $totalEquity += $saldoAkhir;
                echo "  Category: EQUITY" . PHP_EOL;
            }
        }
        echo "---" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Total Assets: " . number_format($totalAssets, 0) . PHP_EOL;
echo "Total Liabilities: " . number_format($totalLiabilities, 0) . PHP_EOL;
echo "Total Equity: " . number_format($totalEquity, 0) . PHP_EOL;
echo "Liabilities + Equity: " . number_format($totalLiabilities + $totalEquity, 0) . PHP_EOL;
echo "Balance: " . number_format($totalAssets - ($totalLiabilities + $totalEquity), 0) . PHP_EOL;

// Check if the issue is with retained earnings
echo PHP_EOL . "=== Retained Earnings Check ===" . PHP_EOL;

// Calculate retained earnings (Revenue - Expenses)
$revenueAccounts = $coas->filter(function($coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    return $firstDigit === '4'; // 4xx = revenue
});

$expenseAccounts = $coas->filter(function($coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    return in_array($firstDigit, ['5', '6']); // 5xx, 6xx = expenses
});

$totalRevenue = 0;
foreach ($revenueAccounts as $coa) {
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.credit');
    
    // Revenue accounts are credit normal
    $saldoAkhir = $totalKredit - $totalDebit;
    $totalRevenue += $saldoAkhir;
}

$totalExpenses = 0;
foreach ($expenseAccounts as $coa) {
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.debit');
        
    $totalKredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.credit');
    
    // Expense accounts are debit normal
    $saldoAkhir = $totalDebit - $totalKredit;
    $totalExpenses += $saldoAkhir;
}

$retainedEarnings = $totalRevenue - $totalExpenses;

echo "Total Revenue: " . number_format($totalRevenue, 0) . PHP_EOL;
echo "Total Expenses: " . number_format($totalExpenses, 0) . PHP_EOL;
echo "Retained Earnings: " . number_format($retainedEarnings, 0) . PHP_EOL;

echo PHP_EOL . "=== Final Balance Sheet ===" . PHP_EOL;
echo "Assets: " . number_format($totalAssets, 0) . PHP_EOL;
echo "Liabilities: " . number_format($totalLiabilities, 0) . PHP_EOL;
echo "Equity (including retained): " . number_format($totalEquity + $retainedEarnings, 0) . PHP_EOL;
echo "Total Liabilities + Equity: " . number_format($totalLiabilities + $totalEquity + $retainedEarnings, 0) . PHP_EOL;

$finalBalance = $totalAssets - ($totalLiabilities + $totalEquity + $retainedEarnings);
echo "Final Balance: " . number_format($finalBalance, 0) . PHP_EOL;
echo "Status: " . ($finalBalance == 0 ? "BALANCED" : "NOT BALANCED") . PHP_EOL;
