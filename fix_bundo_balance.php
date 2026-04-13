<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING AYAM GORENG BUNDO BALANCE\n";
echo "================================\n\n";

// Get COA references
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first();
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first();

echo "COA References:\n";
echo "- 1161 (ID {$coa1161->id}): {$coa1161->nama_akun}\n";
echo "- 117 (ID {$coa117->id}): {$coa117->nama_akun}\n\n";

// Find the wrong journal for Ayam Goreng Bundo
echo "Finding wrong Ayam Goreng Bundo journal:\n";
echo "=====================================\n";

$wrongJournal = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->where('coa_id', $coa1161->id)
    ->where('kredit', '>', 0)
    ->where('keterangan', 'like', '%Ayam Goreng Bundo%')
    ->first();

if ($wrongJournal) {
    echo "FOUND WRONG JOURNAL:\n";
    echo "ID: {$wrongJournal->id}\n";
    echo "COA: {$wrongJournal->coa->kode_akun} - {$wrongJournal->coa->nama_akun}\n";
    echo "Debit: " . number_format($wrongJournal->debit, 0, ',', '.') . "\n";
    echo "Kredit: " . number_format($wrongJournal->kredit, 0, ',', '.') . "\n";
    echo "Keterangan: {$wrongJournal->keterangan}\n";
    echo "Referensi: {$wrongJournal->referensi}\n";
    
    echo "\nPROBLEM: Credit should go to COA 117 (Pers. Barang dalam Proses), not COA 1161!\n";
    
    try {
        // Update the journal to use COA 117
        $wrongJournal->coa_id = $coa117->id;
        $wrongJournal->save();
        
        echo "SUCCESS: Updated journal ID {$wrongJournal->id} to use COA 117\n";
        
        // Verify the update
        $updatedJournal = \App\Models\JurnalUmum::find($wrongJournal->id);
        echo "New COA: {$updatedJournal->coa->kode_akun} - {$updatedJournal->coa->nama_akun}\n";
        
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "No wrong journal found for Ayam Goreng Bundo\n";
}

echo "\nFinal verification:\n";
echo "==================\n";

$allJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

$totalDebit = 0;
$totalKredit = 0;

foreach ($allJournals as $journal) {
    echo "ID: {$journal->id} | Ref: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
    $totalDebit += $journal->debit;
    $totalKredit += $journal->kredit;
}

echo "\nFinal Total Debit: " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Final Total Kredit: " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Final Balance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";

echo "\nExpected final structure:\n";
echo "========================\n";
echo "Ayam Goreng Bundo:\n";
echo "  Debit: 1161 - Pers. Barang Jadi Ayam Goreng Bundo: Rp 3.368.960\n";
echo "  Kredit: 117 - Pers. Barang dalam Proses: Rp 3.368.960\n\n";
echo "Ayam Crispi Macdi:\n";
echo "  Debit: 116 - Pers. Barang Jadi Ayam Crispi Macdi: Rp 3.864.960\n";
echo "  Kredit: 117 - Pers. Barang dalam Proses: Rp 3.864.960\n";

echo "\nDone! Please refresh your browser with Ctrl+F5\n";

?>
