<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING SPECIFIC JOURNAL ISSUE\n";
echo "==============================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first(); // Ayam Crispi Macdi
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first(); // Ayam Goreng Bundo

echo "COA References:\n";
echo "- 116: {$coa116->nama_akun}\n";
echo "- 1161: {$coa1161->nama_akun}\n\n";

// Find the specific journal with Rp 3.368.960 that's using COA 116
$wrongJournal = \App\Models\JurnalUmum::where('coa_id', $coa116->id)
    ->where('debit', 3368960)
    ->where('tipe_referensi', 'produksi')
    ->first();

if ($wrongJournal) {
    echo "Found wrong journal:\n";
    echo "ID: {$wrongJournal->id}\n";
    echo "Tanggal: {$wrongJournal->tanggal}\n";
    echo "COA: {$wrongJournal->coa->kode_akun} - {$wrongJournal->coa->nama_akun}\n";
    echo "Debit: " . number_format($wrongJournal->debit, 0, ',', '.') . "\n";
    echo "Keterangan: {$wrongJournal->keterangan}\n";
    echo "Referensi: {$wrongJournal->referensi}\n\n";
    
    echo "This journal should use COA 1161 (Ayam Goreng Bundo) instead of COA 116\n\n";
    
    try {
        // Update the journal to use COA 1161
        $wrongJournal->coa_id = $coa1161->id;
        $wrongJournal->save();
        
        echo "SUCCESS: Updated journal ID {$wrongJournal->id} to use COA 1161\n";
        
        // Verify the update
        $updatedJournal = \App\Models\JurnalUmum::find($wrongJournal->id);
        echo "New COA: {$updatedJournal->coa->kode_akun} - {$updatedJournal->coa->nama_akun}\n";
        
    } catch (\Exception $e) {
        echo "ERROR: Failed to update journal - " . $e->getMessage() . "\n";
    }
} else {
    echo "No journal found with COA 116 and debit 3.368.960\n\n";
    
    // Check if there's a journal with that amount using COA 1161
    $correctJournal = \App\Models\JurnalUmum::where('coa_id', $coa1161->id)
        ->where('debit', 3368960)
        ->where('tipe_referensi', 'produksi')
        ->first();
    
    if ($correctJournal) {
        echo "Found correct journal using COA 1161:\n";
        echo "ID: {$correctJournal->id}\n";
        echo "COA: {$correctJournal->coa->kode_akun} - {$correctJournal->coa->nama_akun}\n";
        echo "Debit: " . number_format($correctJournal->debit, 0, ',', '.') . "\n";
        echo "Keterangan: {$correctJournal->keterangan}\n";
        echo "This journal is correct!\n";
    } else {
        echo "No journal found with debit 3.368.960 at all\n";
    }
}

echo "\nChecking all produksi journals:\n";
echo "============================\n";

$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->get();

foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nExpected final result:\n";
echo "====================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Crispi Macdi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Kredit | Rp 3.864.960\n\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Kredit | Rp 3.368.960\n";

echo "\nDone!\n";

?>
