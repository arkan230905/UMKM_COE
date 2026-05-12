<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Verify Liability Fix ===" . PHP_EOL;

// Test the corrected liability filtering
echo PHP_EOL . "Testing Corrected Liability Filtering:" . PHP_EOL;

$bulan = 4;
$tahun = 2026;
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

// Get COA data
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_normal', 'saldo_awal')
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun', 'saldo_normal', 'saldo_awal')
    ->orderBy('kode_akun')
    ->get();

// Test liability filtering
$liabilityAccounts = $coas->filter(function($coa) {
    // Only include Liability accounts (Pasiva -> Kewajiban)
    if (!in_array($coa->tipe_akun, ['Liability', 'liability', 'Pasiva', 'Kewajiban'])) return false;
    
    return true;
});

echo "Liability Accounts Found: " . $liabilityAccounts->count() . PHP_EOL;
foreach ($liabilityAccounts as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
}

// Calculate balances for liability accounts
echo PHP_EOL . "=== Liability Balances ===" . PHP_EOL;

foreach ($liabilityAccounts as $coa) {
    // Get journal entries
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
    
    $saldoAwal = (float)($coa->saldo_awal ?? 0);
    $saldoAkhir = $saldoAwal + $totalKredit - $totalDebit; // Liability accounts are credit normal
    
    echo $coa->kode_akun . ": " . $coa->nama_akun . " = Rp " . number_format($saldoAkhir, 0) . PHP_EOL;
    echo "  Saldo Awal: " . $saldoAwal . PHP_EOL;
    echo "  Total Debit: " . $totalDebit . PHP_EOL;
    echo "  Total Kredit: " . $totalKredit . PHP_EOL;
    echo "---" . PHP_EOL;
}

// Test equity filtering too
echo PHP_EOL . "=== Equity Accounts Check ===" . PHP_EOL;

$equityAccounts = $coas->filter(function($coa) {
    // Only include Equity accounts (Ekuitas -> Ekuitas)
    if (!in_array($coa->tipe_akun, ['Equity', 'equity', 'Modal', 'Ekuitas'])) return false;
    
    return true;
});

echo "Equity Accounts Found: " . $equityAccounts->count() . PHP_EOL;
foreach ($equityAccounts as $coa) {
    echo "- " . $coa->kode_akun . ": " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
}

echo PHP_EOL . "=== Expected Results ===" . PHP_EOL;
echo "Liability accounts should now be found and displayed in balance sheet" . PHP_EOL;
echo "Balance sheet should now be more balanced" . PHP_EOL;
