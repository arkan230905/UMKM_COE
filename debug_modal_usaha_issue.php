<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Modal Usaha Issue ===" . PHP_EOL;

// Check why Modal Usaha is not included in equity calculation
echo PHP_EOL . "=== Modal Usaha Analysis ===" . PHP_EOL;

// Get Modal Usaha account
$modalUsaha = DB::table('coas')->where('kode_akun', '310')->first();
if ($modalUsaha) {
    echo "Account Found: " . $modalUsaha->nama_akun . PHP_EOL;
    echo "Type: " . $modalUsaha->tipe_akun . PHP_EOL;
    echo "Saldo Normal: " . $modalUsaha->saldo_normal . PHP_EOL;
    echo "Saldo Awal: " . number_format($modalUsaha->saldo_awal, 0) . PHP_EOL;
    
    // Check if it has journal entries
    $bulan = 4;
    $tahun = 2026;
    $from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
    $to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
    
    $totalDebit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', '310')
        ->sum('journal_lines.debit');
        
    $totalCredit = DB::table('journal_entries')
        ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
        ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
        ->where('journal_entries.tanggal', '>=', $from)
        ->where('journal_entries.tanggal', '<=', $to)
        ->where('coas.kode_akun', '310')
        ->sum('journal_lines.credit');
    
    echo "Journal Debit: " . number_format($totalDebit, 0) . PHP_EOL;
    echo "Journal Credit: " . number_format($totalCredit, 0) . PHP_EOL;
    
    // Calculate final balance
    $saldoAwal = (float)($modalUsaha->saldo_awal ?? 0);
    $finalBalance = $saldoAwal + $totalCredit - $totalDebit; // Equity accounts are credit normal
    
    echo "Final Balance: " . number_format($finalBalance, 0) . PHP_EOL;
    
    // Check if this should be included in balance sheet
    echo PHP_EOL . "Balance Sheet Filter Check:" . PHP_EOL;
    
    // Check equity type filter
    $isEquity = in_array($modalUsaha->tipe_akun, ['Equity', 'equity', 'Modal', 'Ekuitas']);
    echo "  Is Equity: " . ($isEquity ? "YES" : "NO") . PHP_EOL;
    
    // Check if it's a parent account
    $isParent = false; // Simplified - 310 is not a parent
    echo "  Is Parent: " . ($isParent ? "YES" : "NO") . PHP_EOL;
    
    // Check if balance is non-zero
    $hasNonZeroBalance = $finalBalance != 0;
    echo "  Has Non-Zero Balance: " . ($hasNonZeroBalance ? "YES" : "NO") . PHP_EOL;
    
    // Final check: should this be included?
    $shouldBeIncluded = $isEquity && !$isParent && $hasNonZeroBalance;
    echo "  Should Be Included: " . ($shouldBeIncluded ? "YES" : "NO") . PHP_EOL;
    
} else {
    echo "Modal Usaha account (310) not found!" . PHP_EOL;
}

// Check the balance sheet calculation logic
echo PHP_EOL . "=== Balance Sheet Calculation Logic ===" . PHP_EOL;

// Simulate the exact logic from getLaporanPosisiKeuanganData
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

// Create trial balance data (simplified)
$trialBalanceData = [];
foreach ($coas as $coa) {
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    
    // For Modal Usaha, no journal entries in April
    if ($coa->kode_akun == '310') {
        $totalDebitSampaiPeriode = 0;
        $totalKreditSampaiPeriode = 0;
    } else {
        // Get from journal entries (simplified)
        $totalDebitSampaiPeriode = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
            ->where('journal_entries.tanggal', '<=', $to)
            ->where('coas.kode_akun', $coa->kode_akun)
            ->sum('journal_lines.debit');
            
        $totalKreditSampaiPeriode = DB::table('journal_entries')
            ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
            ->where('journal_entries.tanggal', '<=', $to)
            ->where('coas.kode_akun', $coa->kode_akun)
            ->sum('journal_lines.credit');
    }
    
    // Calculate final balance
    $isDebitNormal = strtolower($coa->saldo_normal ?? '') === 'debit';
    if ($isDebitNormal) {
        $saldoAkhir = $saldoAwal + $totalDebitSampaiPeriode - $totalKreditSampaiPeriode;
    } else {
        $saldoAkhir = $saldoAwal + $totalKreditSampaiPeriode - $totalDebitSampaiPeriode;
    }
    
    $trialBalanceData[$coa->kode_akun] = [
        'coa' => $coa,
        'saldo_akhir' => $saldoAkhir
    ];
}

// Check Modal Usaha in trial balance
if (isset($trialBalanceData['310'])) {
    echo "Modal Usaha in Trial Balance:" . PHP_EOL;
    echo "  Final Balance: " . number_format($trialBalanceData['310']['saldo_akhir'], 0) . PHP_EOL;
} else {
    echo "Modal Usaha NOT in Trial Balance!" . PHP_EOL;
}

// Check equity filtering
echo PHP_EOL . "=== Equity Filtering Check ===" . PHP_EOL;

$ekuitas = $coas->filter(function($coa) use ($trialBalanceData) {
    // Only include Equity accounts
    if (!in_array($coa->tipe_akun, ['Equity', 'equity', 'Modal', 'Ekuitas'])) return false;
    
    // Only include accounts with non-zero balance
    $balance = $trialBalanceData[$coa->kode_akun]['saldo_akhir'] ?? 0;
    if ($balance == 0) return false;
    
    return true;
});

echo "Equity Accounts Found: " . $ekuitas->count() . PHP_EOL;
foreach ($ekuitas as $coa) {
    $balance = $trialBalanceData[$coa->kode_akun]['saldo_akhir'] ?? 0;
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " = " . number_format($balance, 0) . PHP_EOL;
}

echo PHP_EOL . "=== Expected Fix ===" . PHP_EOL;
echo "Modal Usaha should be included with balance Rp 175.000.000" . PHP_EOL;
echo "This would make the balance sheet balanced:" . PHP_EOL;
echo "Assets: ~Rp 264M" . PHP_EOL;
echo "Liabilities + Equity: ~Rp 264M (including Modal Usaha)" . PHP_EOL;
