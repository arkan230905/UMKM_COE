<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Check Account Positions (2,3,4 = Credit) ===" . PHP_EOL;

// Check current logic in getLaporanPosisiKeuanganData
echo PHP_EOL . "=== Current Account Position Logic ===" . PHP_EOL;

$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

echo "Account Position Analysis:" . PHP_EOL;
foreach ($coas as $coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    
    // Current logic in the system
    $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
    
    // User's requirement: accounts 2,3,4 should be credit normal
    $shouldBeCreditNormal = in_array($firstDigit, ['2', '3', '4']);
    
    echo $coa->kode_akun . " - " . $coa->nama_akun . PHP_EOL;
    echo "  Type: " . $coa->tipe_akun . PHP_EOL;
    echo "  Saldo Normal: " . $coa->saldo_normal . PHP_EOL;
    echo "  First Digit: " . $firstDigit . PHP_EOL;
    echo "  Current Logic (Debit Normal): " . ($isDebitNormal ? "YES" : "NO") . PHP_EOL;
    echo "  Should Be Credit Normal: " . ($shouldBeCreditNormal ? "YES" : "NO") . PHP_EOL;
    
    if ($shouldBeCreditNormal && $isDebitNormal) {
        echo "  *** MISMATCH! Should be credit but treated as debit ***" . PHP_EOL;
    }
    echo "---" . PHP_EOL;
}

// Check specific accounts that should be credit normal
echo PHP_EOL . "=== Accounts That Should Be Credit Normal ===" . PHP_EOL;

$creditNormalAccounts = $coas->filter(function($coa) {
    $firstDigit = substr($coa->kode_akun, 0, 1);
    return in_array($firstDigit, ['2', '3', '4']);
});

echo "Found " . $creditNormalAccounts->count() . " accounts that should be credit normal:" . PHP_EOL;
foreach ($creditNormalAccounts as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (Type: " . $coa->tipe_akun . ", Normal: " . $coa->saldo_normal . ")" . PHP_EOL;
}

// Check the current calculation logic
echo PHP_EOL . "=== Current Calculation Logic Impact ===" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

foreach ($creditNormalAccounts as $coa) {
    // Get journal totals
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.debit');
        
    $totalCredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', $coa->kode_akun)
        ->sum('journal_lines.credit');
    
    if ($totalDebit > 0 || $totalCredit > 0) {
        $saldoAwal = (float)($coa->saldo_awal ?? 0);
        
        // Current logic (based on tipe_akun)
        $isDebitNormal = in_array(strtolower($coa->tipe_akun), ['asset', 'aset', 'expense', 'beban', 'biaya']);
        if ($isDebitNormal) {
            $currentBalance = $saldoAwal + $totalDebit - $totalCredit;
        } else {
            $currentBalance = $saldoAwal + $totalCredit - $totalDebit;
        }
        
        // Correct logic (based on account number)
        $firstDigit = substr($coa->kode_akun, 0, 1);
        $shouldBeDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
        if ($shouldBeDebitNormal) {
            $correctBalance = $saldoAwal + $totalDebit - $totalCredit;
        } else {
            $correctBalance = $saldoAwal + $totalCredit - $totalDebit;
        }
        
        echo $coa->kode_akun . " - " . $coa->nama_akun . PHP_EOL;
        echo "  Journal Activity: Debit " . number_format($totalDebit, 0) . ", Credit " . number_format($totalCredit, 0) . PHP_EOL;
        echo "  Current Balance: " . number_format($currentBalance, 0) . PHP_EOL;
        echo "  Correct Balance: " . number_format($correctBalance, 0) . PHP_EOL;
        
        if ($currentBalance != $correctBalance) {
            echo "  *** DIFFERENCE: " . number_format($currentBalance - $correctBalance, 0) . " ***" . PHP_EOL;
        }
        echo "---" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Required Fix ===" . PHP_EOL;
echo "Need to update the logic in getLaporanPosisiKeuanganData:" . PHP_EOL;
echo "1. Check first digit of account code" . PHP_EOL;
echo "2. Accounts 2xx, 3xx, 4xx = credit normal" . PHP_EOL;
echo "3. Accounts 1xx, 5xx, 6xx = debit normal" . PHP_EOL;
echo "4. This should fix the balance sheet equation" . PHP_EOL;
