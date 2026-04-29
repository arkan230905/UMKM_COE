<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Journal Data for April 2026...\n\n";

// Check jurnal_umum data for April 2026
$tanggalAwal = '2026-04-01';
$tanggalAkhir = '2026-04-30';

echo "Checking Jurnal Umum Data:\n";
echo "========================\n";

$journals = \App\Models\JurnalUmum::whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir])
    ->orderBy('tanggal')
    ->get();

echo "Found {$journals->count()} journal entries for April 2026\n\n";

if ($journals->count() > 0) {
    foreach ($journals as $journal) {
        echo "ID: {$journal->id}\n";
        echo "Tanggal: {$journal->tanggal}\n";
        echo "COA: {$journal->coa_id}\n";
        echo "Keterangan: {$journal->keterangan}\n";
        echo "Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
        echo "Referensi: {$journal->referensi}\n";
        echo "Tipe Referensi: {$journal->tipe_referensi}\n";
        echo "\n";
    }
} else {
    echo "No journal entries found for April 2026\n\n";
    
    // Check all journal data
    echo "Checking All Journal Data:\n";
    echo "==========================\n";
    
    $allJournals = \App\Models\JurnalUmum::orderBy('tanggal', 'desc')
        ->limit(10)
        ->get();
    
    echo "Found {$allJournals->count()} recent journal entries\n\n";
    
    foreach ($allJournals as $journal) {
        echo "ID: {$journal->id}\n";
        echo "Tanggal: {$journal->tanggal}\n";
        echo "COA: {$journal->coa_id}\n";
        echo "Keterangan: {$journal->keterangan}\n";
        echo "Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
        echo "\n";
    }
}

// Check COA saldo awal
echo "\nChecking COA Saldo Awal:\n";
echo "=======================\n";

$coas = \App\Models\Coa::where('user_id', 1)
    ->whereNotNull('saldo_awal')
    ->where('saldo_awal', '>', 0)
    ->orderBy('kode_akun')
    ->get();

echo "Found {$coas->count()} COA with saldo awal > 0\n\n";

foreach ($coas as $coa) {
    echo "COA: {$coa->kode_akun} - {$coa->nama_akun}\n";
    echo "Saldo Awal: " . number_format($coa->saldo_awal, 0, ',', '.') . "\n";
    echo "Tipe: {$coa->tipe_akun}\n";
    echo "\n";
}

echo "Journal data test completed!\n";
