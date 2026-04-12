<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FINAL FIX FOR JOURNAL DISPLAY ISSUE\n";
echo "==================================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first();
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first();

echo "COA References:\n";
echo "- 116 (ID {$coa116->id}): {$coa116->nama_akun}\n";
echo "- 1161 (ID {$coa1161->id}): {$coa1161->nama_akun}\n\n";

// Find the specific journal that's wrong
echo "Finding journal with Rp 3.368.960 that should be Ayam Goreng Bundo:\n";
echo "==========================================================\n";

$wrongJournal = \App\Models\JurnalUmum::where('debit', 3368960)
    ->where('coa_id', $coa116->id)
    ->where('tipe_referensi', 'produksi')
    ->first();

if ($wrongJournal) {
    echo "FOUND WRONG JOURNAL:\n";
    echo "ID: {$wrongJournal->id}\n";
    echo "COA: {$wrongJournal->coa->kode_akun} - {$wrongJournal->coa->nama_akun}\n";
    echo "Debit: " . number_format($wrongJournal->debit, 0, ',', '.') . "\n";
    echo "Keterangan: {$wrongJournal->keterangan}\n";
    echo "Referensi: {$wrongJournal->referensi}\n\n";
    
    echo "This journal should be for Ayam Goreng Bundo and use COA 1161!\n";
    
    try {
        // Update the journal
        $wrongJournal->coa_id = $coa1161->id;
        $wrongJournal->keterangan = 'Transfer WIP ke Barang Jadi - Ayam Goreng Bundo';
        $wrongJournal->save();
        
        echo "SUCCESS: Updated journal ID {$wrongJournal->id}\n";
        echo "New COA: 1161 - Pers. Barang Jadi Ayam Goreng Bundo\n";
        echo "New Keterangan: Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
        
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "No wrong journal found with COA 116 and debit 3.368.960\n\n";
    
    // Check if there's a journal with COA 1161
    $correctJournal = \App\Models\JurnalUmum::where('debit', 3368960)
        ->where('coa_id', $coa1161->id)
        ->where('tipe_referensi', 'produksi')
        ->first();
    
    if ($correctJournal) {
        echo "Found correct journal with COA 1161:\n";
        echo "ID: {$correctJournal->id}\n";
        echo "COA: {$correctJournal->coa->kode_akun} - {$correctJournal->coa->nama_akun}\n";
        echo "Keterangan: {$correctJournal->keterangan}\n";
        echo "This journal is correct!\n";
    } else {
        echo "No journal found with debit 3.368.960 at all!\n";
    }
}

echo "\nCurrent state of all produksi journals:\n";
echo "=====================================\n";

$allJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

foreach ($allJournals as $journal) {
    echo "ID: {$journal->id}\n";
    echo "COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    echo "Keterangan: {$journal->keterangan}\n";
    echo "---\n";
}

echo "\nExpected final display:\n";
echo "====================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Crispi Macdi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n";

echo "\nDone! Please refresh your browser with Ctrl+F5\n";

?>
