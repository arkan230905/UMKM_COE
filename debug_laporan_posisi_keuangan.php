<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Debug Laporan Posisi Keuangan ===" . PHP_EOL;

// Check what the laporanPosisiKeuangan method is doing
echo PHP_EOL . "=== Check laporanPosisiKeuangan Method ===" . PHP_EOL;

// Get current neraca saldo data for April 2026
$bulan = 4;
$tahun = 2026;

echo "Checking Neraca Saldo for April 2026:" . PHP_EOL;

// Get neraca saldo data using the same logic as neracaSaldo method
$from = Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
$to = Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');

echo "From: " . $from . PHP_EOL;
echo "To: " . $to . PHP_EOL;

// Get COA data
$coas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->groupBy('kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal', 'saldo_awal', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

echo PHP_EOL . "COA Data Available:" . PHP_EOL;
foreach ($coas as $coa) {
    if (in_array($coa->tipe_akun, ['ASET', 'ASET LANCAR', 'ASET TIDAK LANCAR'])) {
        echo $coa->kode_akun . " - " . $coa->nama_akun . " (" . $coa->tipe_akun . ")" . PHP_EOL;
        echo "  Saldo Awal: " . $coa->saldo_awal . PHP_EOL;
        echo "  Kategori: " . $coa->kategori_akun . PHP_EOL;
        echo "---" . PHP_EOL;
    }
}

// Check if the issue is in the laporanPosisiKeuangan method itself
echo PHP_EOL . "=== Check laporanPosisiKeuangan Processing ===" . PHP_EOL;

// Get asset accounts specifically
$assets = DB::table('coas')
    ->where('tipe_akun', 'ASET')
    ->orWhere('tipe_akun', 'ASET LANCAR')
    ->orWhere('tipe_akun', 'ASET TIDAK LANCAR')
    ->orderBy('kode_akun')
    ->get();

echo "Asset Accounts Found: " . $assets->count() . PHP_EOL;
foreach ($assets as $asset) {
    echo "- " . $asset->kode_akun . ": " . $asset->nama_akun . PHP_EOL;
}

// Check if there are any journal entries for assets
echo PHP_EOL . "=== Check Journal Entries for Assets ===" . PHP_EOL;

$assetJournalEntries = DB::table('journal_entries')
    ->join('journal_lines', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
    ->join('coas', 'journal_lines.coa_id', '=', 'coas.id')
    ->where('journal_entries.tanggal', '>=', $from)
    ->where('journal_entries.tanggal', '<=', $to)
    ->whereIn('coas.tipe_akun', ['ASET', 'ASET LANCAR', 'ASET TIDAK LANCAR'])
    ->select('coas.kode_akun', 'coas.nama_akun', DB::raw('SUM(journal_lines.debit) as total_debit'), DB::raw('SUM(journal_lines.credit) as total_credit'))
    ->groupBy('coas.kode_akun', 'coas.nama_akun')
    ->get();

echo "Journal Entries for Assets: " . $assetJournalEntries->count() . PHP_EOL;
foreach ($assetJournalEntries as $entry) {
    echo "- " . $entry->kode_akun . ": Debit " . $entry->total_debit . ", Credit " . $entry->total_credit . PHP_EOL;
}

// Check if the issue is with the account classification
echo PHP_EOL . "=== Account Classification Issue ===" . PHP_EOL;

$allCoas = DB::table('coas')
    ->select('kode_akun', 'nama_akun', 'tipe_akun', 'kategori_akun')
    ->orderBy('kode_akun')
    ->get();

echo "All Account Types:" . PHP_EOL;
$types = [];
foreach ($allCoas as $coa) {
    $types[$coa->tipe_akun][] = $coa->kode_akun . " - " . $coa->nama_akun;
}

foreach ($types as $type => $accounts) {
    echo $type . " (" . count($accounts) . "):" . PHP_EOL;
    foreach ($accounts as $account) {
        echo "  - " . $account . PHP_EOL;
    }
    echo PHP_EOL;
}
