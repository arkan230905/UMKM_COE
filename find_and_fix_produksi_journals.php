<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FINDING AND FIXING PRODUKSI JOURNALS\n";
echo "===================================\n\n";

// Get the new COA for Ayam Goreng Bundo
$newCoa = \App\Models\Coa::where('kode_akun', '1161')->first();

if (!$newCoa) {
    echo "ERROR: COA 1161 not found!\n";
    exit;
}

echo "Using COA: {$newCoa->kode_akun} - {$newCoa->nama_akun}\n\n";

// Find all produksi journals
$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->get();

echo "Found {$produksiJournals->count()} produksi journals:\n\n";

foreach ($produksiJournals as $journal) {
    echo "Journal ID: {$journal->id}\n";
    echo "  Tanggal: {$journal->tanggal}\n";
    echo "  Referensi: {$journal->referensi}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Debit: {$journal->debit}\n";
    echo "  Kredit: {$journal->kredit}\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    
    // Check if this journal should use the new COA
    if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false || 
        strpos($journal->referensi, 'Ayam Goreng Bundo') !== false) {
        
        echo "  -> This journal should use the new COA!\n";
        
        // Update the journal to use the correct COA
        $journal->coa_id = $newCoa->id;
        $journal->save();
        
        echo "  -> Updated to use COA {$newCoa->kode_akun}\n";
    }
    
    echo "---\n";
}

echo "\nChecking journals that use COA 116 (Pers. Barang Jadi Ayam Crispi Macdi):\n";
echo "================================================================\n";

$wrongCoaJournals = \App\Models\JurnalUmum::where('coa_id', 116)
    ->with('coa')
    ->get();

foreach ($wrongCoaJournals as $journal) {
    echo "Journal ID: {$journal->id}\n";
    echo "  Tipe: {$journal->tipe_referensi}\n";
    echo "  Referensi: {$journal->referensi}\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "  Debit: {$journal->debit}\n";
    echo "  Kredit: {$journal->kredit}\n";
    
    // Check if this is related to Ayam Goreng Bundo
    if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false) {
        echo "  -> This should be updated to COA 1161!\n";
        
        $journal->coa_id = $newCoa->id;
        $journal->save();
        
        echo "  -> Updated!\n";
    }
    
    echo "---\n";
}

echo "\nFinal verification:\n";
echo "==================\n";

$finalJournals = \App\Models\JurnalUmum::where('coa_id', $newCoa->id)
    ->with('coa')
    ->get();

echo "Journals using COA {$newCoa->kode_akun}:\n";
foreach ($finalJournals as $journal) {
    echo "  {$journal->referensi} | D: {$journal->debit} | K: {$journal->kredit} | {$journal->keterangan}\n";
}

echo "\nDone!\n";

?>
