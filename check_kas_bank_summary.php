<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Kas Bank summary after fix...\n\n";

// Get all journals for April 2026
$startDate = '2026-04-01';
$endDate = '2026-04-30';

echo "Journals for April 2026:\n";

// Get Kas (112) journals
$kasJournals = App\Models\JurnalUmum::where('coa_id', 3) // COA ID 3 = Kas (112)
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->with('coa')
    ->get();

echo "\n=== KAS (112) ===\n";
$kasMasuk = 0;
$kasKeluar = 0;
foreach ($kasJournals as $journal) {
    echo "  {$journal->referensi} | D: {$journal->debit} | K: {$journal->kredit} | {$journal->tipe_referensi}\n";
    $kasMasuk += $journal->debit;
    $kasKeluar += $journal->kredit;
}
echo "Total Masuk: $kasMasuk\n";
echo "Total Keluar: $kasKeluar\n";
echo "Net: " . ($kasMasuk - $kasKeluar) . "\n";

// Get Kas Bank (111) journals
$kasBankJournals = App\Models\JurnalUmum::where('coa_id', 2) // COA ID 2 = Kas Bank (111)
    ->whereBetween('tanggal', [$startDate, $endDate])
    ->with('coa')
    ->get();

echo "\n=== KAS BANK (111) ===\n";
$kasBankMasuk = 0;
$kasBankKeluar = 0;
foreach ($kasBankJournals as $journal) {
    echo "  {$journal->referensi} | D: {$journal->debit} | K: {$journal->kredit} | {$journal->tipe_referensi}\n";
    $kasBankMasuk += $journal->debit;
    $kasBankKeluar += $journal->kredit;
}
echo "Total Masuk: $kasBankMasuk\n";
echo "Total Keluar: $kasBankKeluar\n";
echo "Net: " . ($kasBankMasuk - $kasBankKeluar) . "\n";

echo "\n=== SUMMARY ===\n";
echo "Total Transaksi Masuk (Kas + Kas Bank): " . ($kasMasuk + $kasBankMasuk) . "\n";
echo "Total Transaksi Keluar (Kas + Kas Bank): " . ($kasKeluar + $kasBankKeluar) . "\n";

echo "\nBy Transaction Type:\n";
$summary = App\Models\JurnalUmum::whereBetween('tanggal', [$startDate, $endDate])
    ->whereIn('coa_id', [2, 3]) // Kas Bank + Kas
    ->groupBy('tipe_referensi')
    ->selectRaw('tipe_referensi, SUM(debit) as total_debit, SUM(kredit) as total_kredit')
    ->get();

foreach ($summary as $item) {
    $net = $item->total_debit - $item->total_kredit;
    echo "  {$item->tipe_referensi}: Masuk={$item->total_debit}, Keluar={$item->total_kredit}, Net={$net}\n";
}
?>
