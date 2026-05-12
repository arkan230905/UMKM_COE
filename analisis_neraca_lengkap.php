<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "ANALISIS LENGKAP NERACA SALDO SETELAH PERBAIKAN" . PHP_EOL;
echo "==============================================" . PHP_EOL;

// 1. Cek saldo semua akun
echo PHP_EOL . "1. SALDO SEMUA AKUN:" . PHP_EOL;
$allAccounts = \DB::table('journal_lines')
    ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'coas.id', '=', 'journal_lines.coa_id')
    ->select(
        'coas.kode_akun',
        'coas.nama_akun',
        'coas.tipe_akun',
        \DB::raw('SUM(CASE WHEN journal_lines.debit > 0 THEN journal_lines.debit ELSE 0 END) as total_debit'),
        \DB::raw('SUM(CASE WHEN journal_lines.credit > 0 THEN journal_lines.credit ELSE 0 END) as total_credit')
    )
    ->groupBy('coas.kode_akun', 'coas.nama_akun', 'coas.tipe_akun')
    ->orderBy('coas.kode_akun')
    ->get();

echo "Kode Akun | Nama Akun | Tipe | Debit | Kredit | Saldo" . PHP_EOL;
echo str_repeat("-", 80) . PHP_EOL;

$totalDebit = 0;
$totalKredit = 0;

foreach ($allAccounts as $account) {
    $saldo = $account->total_debit - $account->total_credit;
    $totalDebit += $account->total_debit;
    $totalKredit += $account->total_credit;
    
    echo sprintf("%-8s | %-25s | %-8s | %12s | %12s | %12s" . PHP_EOL,
        $account->kode_akun,
        substr($account->nama_akun, 0, 25),
        $account->tipe_akun,
        number_format($account->total_debit, 0, ',', '.'),
        number_format($account->total_credit, 0, ',', '.'),
        number_format($saldo, 0, ',', '.')
    );
}

echo str_repeat("-", 80) . PHP_EOL;
echo sprintf("%-8s | %-25s | %-8s | %12s | %12s | %12s" . PHP_EOL,
    "TOTAL",
    "SEMUA AKUN",
    "",
    number_format($totalDebit, 0, ',', '.'),
    number_format($totalKredit, 0, ',', '.'),
    number_format($totalDebit - $totalKredit, 0, ',', '.')
);

// 2. Fokus pada akun HPP
echo PHP_EOL . PHP_EOL . "2. DETAIL AKUN HPP:" . PHP_EOL;
$hppAccounts = $allAccounts->filter(function($account) {
    return in_array($account->kode_akun, ['1600', '1601', '1602', '1603']);
});

foreach ($hppAccounts as $hpp) {
    echo sprintf("%-8s | %-25s | %12s | %12s | %12s" . PHP_EOL,
        $hpp->kode_akun,
        $hpp->nama_akun,
        number_format($hpp->total_debit, 0, ',', '.'),
        number_format($hpp->total_credit, 0, ',', '.'),
        number_format($hpp->total_debit - $hpp->total_credit, 0, ',', '.')
    );
}

// 3. Fokus pada persediaan
echo PHP_EOL . PHP_EOL . "3. DETAIL PERSEDIAAN:" . PHP_EOL;
$persediaanAccounts = $allAccounts->filter(function($account) {
    return substr($account->kode_akun, 0, 1) == '1' && $account->tipe_akun == 'Asset';
});

foreach ($persediaanAccounts as $persediaan) {
    echo sprintf("%-8s | %-25s | %12s | %12s | %12s" . PHP_EOL,
        $persediaan->kode_akun,
        $persediaan->nama_akun,
        number_format($persediaan->total_debit, 0, ',', '.'),
        number_format($persediaan->total_credit, 0, ',', '.'),
        number_format($persediaan->total_debit - $persediaan->total_credit, 0, ',', '.')
    );
}

// 4. Analisis masalah
echo PHP_EOL . "4. ANALISIS MASALAH:" . PHP_EOL;

// Hitung total HPP yang benar
$totalHppBenar = 0;
foreach ($hppAccounts as $hpp) {
    $totalHppBenar += $hpp->total_debit - $hpp->total_credit;
}

echo "- Total HPP (semua akun): Rp " . number_format($totalHppBenar, 0, ',', '.') . PHP_EOL;
echo "- Debit: Rp " . number_format($totalDebit, 0, ',', '.') . PHP_EOL;
echo "- Kredit: Rp " . number_format($totalKredit, 0, ',', '.') . PHP_EOL;
echo "- Selisih: Rp " . number_format($totalDebit - $totalKredit, 0, ',', '.') . PHP_EOL;

if (abs($totalDebit - $totalKredit) > 0.01) {
    echo PHP_EOL . "❌ ERROR: TIDAK BALANCE!" . PHP_EOL;
} else {
    echo PHP_EOL . "✅ Balance: OK" . PHP_EOL;
}
