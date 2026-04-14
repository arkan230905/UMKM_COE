<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING SEPARATED PRODUKSI JOURNALS\n";
echo "==================================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first();
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first();
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first();

echo "COA References:\n";
echo "- 116 (ID {$coa116->id}): {$coa116->nama_akun}\n";
echo "- 1161 (ID {$coa1161->id}): {$coa1161->nama_akun}\n";
echo "- 117 (ID {$coa117->id}): {$coa117->nama_akun}\n\n";

// Find all produksi journals
$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

echo "Current produksi journals:\n";
echo "========================\n";

foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | Referensi: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nPROBLEM: Both transactions have the same referensi!\n";
echo "SOLUTION: Create separate referensi for each product\n\n";

try {
    \DB::beginTransaction();
    
    // Update Ayam Goreng Bundo journals to have unique referensi
    $ayamGorengJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
        ->where('coa_id', $coa1161->id)
        ->orWhere(function($query) use ($coa1161) {
            $query->where('tipe_referensi', 'produksi')
                   ->where('keterangan', 'like', '%Ayam Goreng Bundo%');
        })
        ->get();
    
    echo "Updating Ayam Goreng Bundo journals:\n";
    foreach ($ayamGorengJournals as $journal) {
        echo "  ID {$journal->id}: {$journal->referensi} -> ";
        $journal->referensi = 'PROD-AYAMGORENG-20260412-001';
        $journal->save();
        echo "{$journal->referensi}\n";
    }
    
    // Update Ayam Crispi Macdi journals to have unique referensi
    $ayamCrispiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
        ->where('coa_id', $coa116->id)
        ->orWhere(function($query) use ($coa116) {
            $query->where('tipe_referensi', 'produksi')
                   ->where('keterangan', 'like', '%Ayam Crispi Macdi%');
        })
        ->get();
    
    echo "\nUpdating Ayam Crispi Macdi journals:\n";
    foreach ($ayamCrispiJournals as $journal) {
        echo "  ID {$journal->id}: {$journal->referensi} -> ";
        $journal->referensi = 'PROD-CRISPI-20260412-001';
        $journal->save();
        echo "{$journal->referensi}\n";
    }
    
    \DB::commit();
    echo "\nSUCCESS: Journals have been separated!\n";
    
} catch (\Exception $e) {
    \DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nFinal verification:\n";
echo "==================\n";

$finalJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

foreach ($finalJournals as $journal) {
    echo "ID: {$journal->id} | Referensi: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nExpected UI display after fix:\n";
echo "==============================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Goreng Bundo\n";
echo "1161 - Pers. Barang Jadi Ayam Goreng Bundo | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi - Ayam Crispi Macdi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n";

echo "\nDone! Please refresh your browser with Ctrl+F5\n";

?>
