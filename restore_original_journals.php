<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "RESTORING ORIGINAL JOURNAL STRUCTURE\n";
echo "===================================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first();
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first();
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first();

echo "COA References:\n";
echo "- 116 (ID {$coa116->id}): {$coa116->nama_akun}\n";
echo "- 1161 (ID {$coa1161->id}): {$coa1161->nama_akun}\n";
echo "- 117 (ID {$coa117->id}): {$coa117->nama_akun}\n\n";

echo "Current produksi journals:\n";
echo "========================\n";

$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | Ref: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
}

echo "\nRestoring to original structure:\n";
echo "==============================\n";

try {
    \DB::beginTransaction();
    
    // Update all produksi journals to use the same referensi and proper COA
    foreach ($produksiJournals as $journal) {
        echo "Updating journal ID {$journal->id}:\n";
        
        // Restore original referensi
        $journal->referensi = 'PROD-20260412-001';
        
        // Update COA based on product
        if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false) {
            echo "  - Ayam Goreng Bundo: Using COA 1161\n";
            $journal->coa_id = $coa1161->id;
        } elseif (strpos($journal->keterangan, 'Ayam Crispi Macdi') !== false) {
            if ($journal->debit > 0) {
                echo "  - Ayam Crispi Macdi (debit): Using COA 116\n";
                $journal->coa_id = $coa116->id;
            } else {
                echo "  - Ayam Crispi Macdi (kredit): Using COA 117\n";
                $journal->coa_id = $coa117->id;
            }
        }
        
        $journal->save();
        echo "  - Updated to: Ref {$journal->referensi} | COA {$journal->coa->kode_akun}\n";
    }
    
    \DB::commit();
    echo "\nSUCCESS: All journals have been restored to original structure!\n";
    
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

$totalDebit = 0;
$totalKredit = 0;

foreach ($finalJournals as $journal) {
    echo "ID: {$journal->id} | Ref: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
    $totalDebit += $journal->debit;
    $totalKredit += $journal->kredit;
}

echo "\nTotal Debit: " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Balance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";

echo "\nExpected UI display (original structure):\n";
echo "=====================================\n";
echo "12/04/2026\n";
echo "Transfer WIP ke Barang Jadi\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.864.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.864.960\n";
echo "116 - Pers. Barang Jadi Ayam Crispi Macdi | Asset | Debit | Rp 3.368.960\n";
echo "117 - Pers. Barang dalam Proses | Asset | Kredit | Rp 3.368.960\n";

echo "\nNote: All produksi journals now use the same referensi (PROD-20260412-001)\n";
echo "This will group them as one transaction in the UI display\n";
echo "But the COA for Ayam Goreng Bundo is now 1161 (correct)\n";

echo "\nDone! Please refresh your browser with Ctrl+F5\n";

?>
