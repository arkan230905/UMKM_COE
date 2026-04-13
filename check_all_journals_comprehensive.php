<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "COMPREHENSIVE JOURNAL CHECK\n";
echo "=============================\n\n";

// Check ALL journals without any filters
echo "1. ALL JOURNALS IN DATABASE:\n";
echo "============================\n";

$allJournals = \App\Models\JurnalUmum::with('coa')
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->get();

echo "Total journals: " . $allJournals->count() . "\n\n";

foreach ($allJournals as $journal) {
    echo "ID: {$journal->id} | Tanggal: {$journal->tanggal} | Tipe: {$journal->tipe_referensi} | Ref: {$journal->referensi}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . " | Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "---\n";
}

echo "\n2. JOURNALS ON 12/04/2026 SPECIFICALLY:\n";
echo "===================================\n";

$april12Journals = \App\Models\JurnalUmum::whereDate('tanggal', '2026-04-12')
    ->with('coa')
    ->orderBy('id')
    ->get();

echo "Journals on 2026-04-12: " . $april12Journals->count() . "\n\n";

foreach ($april12Journals as $journal) {
    echo "ID: {$journal->id} | Tipe: {$journal->tipe_referensi} | Ref: {$journal->referensi}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . " | Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "---\n";
}

echo "\n3. SEARCH FOR 'Ayam Goreng Bundo' IN ALL JOURNALS:\n";
echo "============================================\n";

$ayamGorengJournals = \App\Models\JurnalUmum::where('keterangan', 'like', '%Ayam Goreng Bundo%')
    ->orWhere('referensi', 'like', '%Ayam Goreng Bundo%')
    ->with('coa')
    ->get();

echo "Journals mentioning 'Ayam Goreng Bundo': " . $ayamGorengJournals->count() . "\n\n";

foreach ($ayamGorengJournals as $journal) {
    echo "ID: {$journal->id} | Tanggal: {$journal->tanggal} | Tipe: {$journal->tipe_referensi}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "---\n";
}

echo "\n4. CHECK FOR PRODUCTION TRANSFER JOURNALS:\n";
echo "======================================\n";

// Look for journals with "Transfer WIP" or similar
$transferJournals = \App\Models\JurnalUmum::where('keterangan', 'like', '%Transfer%')
    ->orWhere('keterangan', 'like', '%WIP%')
    ->orWhere('keterangan', 'like', '%Barang Jadi%')
    ->with('coa')
    ->get();

echo "Journals with 'Transfer', 'WIP', or 'Barang Jadi': " . $transferJournals->count() . "\n\n";

foreach ($transferJournals as $journal) {
    echo "ID: {$journal->id} | Tanggal: {$journal->tanggal} | Tipe: {$journal->tipe_referensi}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . " | Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "---\n";
}

echo "\n5. CHECK IF THERE ARE ANY TABLES WITH DIFFERENT NAMES:\n";
echo "=================================================\n";

// Check if there are other journal-like tables
$tables = \DB::select("SHOW TABLES LIKE '%jurnal%'");
echo "Tables with 'jurnal' in name:\n";
foreach ($tables as $table) {
    foreach ($table as $tableName) {
        echo "  - $tableName\n";
        
        // Check if this table has data
        try {
            $count = \DB::table($tableName)->count();
            echo "    Records: $count\n";
            
            if ($count > 0 && $tableName != 'jurnal_umum') {
                echo "    -> This might be the table the user is seeing!\n";
                
                // Show some sample data
                $sample = \DB::table($tableName)->limit(3)->get();
                foreach ($sample as $row) {
                    echo "    Sample: " . json_encode($row) . "\n";
                }
            }
        } catch (\Exception $e) {
            echo "    Error: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

echo "\nDone!\n";

?>
