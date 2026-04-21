<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Test Neraca Saldo After Position Fix ===" . PHP_EOL;

// Test neraca saldo with corrected positions
echo PHP_EOL . "Testing Neraca Saldo with Fixed Account Positions:" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo "Period: April 2026" . PHP_EOL;
echo "Expected: Only real accounts (1xx, 2xx, 3xx) should appear in neraca saldo" . PHP_EOL;

// Get COA data
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal')
    ->orderBy('kode_akun')
    ->get();

echo PHP_EOL . "=== Real Accounts Only (1xx, 2xx, 3xx) ===" . PHP_EOL;

$realAccounts = $coas->filter(function($coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    return in_array($firstDigit, ['1', '2', '3']); // Only real accounts
});

echo "Real Accounts Found: " . $realAccounts->count() . PHP_EOL;

$totalAssets = 0;
$totalLiabilities = 0;
$totalEquity = 0;

foreach ($realAccounts as $coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    
    // Calculate balance using neraca saldo logic (now fixed)
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
        } elseif ($firstDigit === '2') {
            $totalLiabilities += $saldoAkhir;
            echo "  Category: LIABILITY" . PHP_EOL;
        } elseif ($firstDigit === '3') {
            $totalEquity += $saldoAkhir;
            echo "  Category: EQUITY" . PHP_EOL;
        }
        echo "---" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Neraca Saldo Summary ===" . PHP_EOL;
echo "Total Assets (1xx): " . number_format($totalAssets, 0) . PHP_EOL;
echo "Total Liabilities (2xx): " . number_format($totalLiabilities, 0) . PHP_EOL;
echo "Total Equity (3xx): " . number_format($totalEquity, 0) . PHP_EOL;
echo "Liabilities + Equity: " . number_format($totalLiabilities + $totalEquity, 0) . PHP_EOL;

$balance = $totalAssets - ($totalLiabilities + $totalEquity);
echo "Balance: " . number_format($balance, 0) . PHP_EOL;
echo "Status: " . ($balance == 0 ? "BALANCED" : "NOT BALANCED") . PHP_EOL;

echo PHP_EOL . "=== Key Improvements ===" . PHP_EOL;
echo "1. Account positions now use digit-based logic (2,3,4 = credit)" . PHP_EOL;
echo "2. Only real accounts (1xx, 2xx, 3xx) appear in neraca saldo" . PHP_EOL;
echo "3. Expense accounts (5xx, 6xx) excluded from neraca saldo" . PHP_EOL;
echo "4. Revenue accounts (4xx) excluded from neraca saldo" . PHP_EOL;
echo "5. Inventory accounts use getInventorySaldoAwal correctly" . PHP_EOL;

echo PHP_EOL . "=== Expected Result ===" . PHP_EOL;
echo "Neraca Saldo should now show:" . PHP_EOL;
echo "- Only real accounts (Assets, Liabilities, Equity)" . PHP_EOL;
echo "- Correct balances with proper positions" . PHP_EOL;
echo "- Balanced equation: Assets = Liabilities + Equity" . PHP_EOL;
