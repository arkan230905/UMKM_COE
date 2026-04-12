<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FIXING BALANCE ISSUE\n";
echo "===================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first();
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first();
$coa117 = \App\Models\Coa::where('kode_akun', '117')->first();

echo "COA References:\n";
echo "- 116 (ID {$coa116->id}): {$coa116->nama_akun}\n";
echo "- 1161 (ID {$coa1161->id}): {$coa1161->nama_akun}\n";
echo "- 117 (ID {$coa117->id}): {$coa117->nama_akun}\n\n";

// Check current produksi journals
echo "Current produksi journals:\n";
echo "========================\n";

$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

$totalDebit = 0;
$totalKredit = 0;

foreach ($produksiJournals as $journal) {
    echo "ID: {$journal->id} | Ref: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
    $totalDebit += $journal->debit;
    $totalKredit += $journal->kredit;
}

echo "\nTotal Debit: " . number_format($totalDebit, 0, ',', '.') . "\n";
echo "Total Kredit: " . number_format($totalKredit, 0, ',', '.') . "\n";
echo "Balance: " . ($totalDebit == $totalKredit ? "BALANCED" : "NOT BALANCED") . "\n";

if ($totalDebit != $totalKredit) {
    echo "\nPROBLEM: Journals are not balanced!\n";
    echo "SOLUTION: Fix the missing credit entries\n\n";
    
    try {
        \DB::beginTransaction();
        
        // Check Ayam Crispi Macdi journals
        $crispiDebit = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('coa_id', $coa116->id)
            ->where('debit', '>', 0)
            ->first();
        
        $crispiKredit = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('coa_id', $coa117->id)
            ->where('kredit', '>', 0)
            ->where('keterangan', 'like', '%Ayam Crispi Macdi%')
            ->first();
        
        if ($crispiDebit && !$crispiKredit) {
            echo "Creating missing credit entry for Ayam Crispi Macdi:\n";
            
            $newKredit = [
                'coa_id' => $coa117->id,
                'tanggal' => $crispiDebit->tanggal,
                'keterangan' => 'Transfer WIP ke Barang Jadi - Ayam Crispi Macdi',
                'debit' => 0,
                'kredit' => $crispiDebit->debit,
                'referensi' => 'PROD-CRISPI-20260412-001',
                'tipe_referensi' => 'produksi',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            \App\Models\JurnalUmum::insert($newKredit);
            echo "SUCCESS: Created credit entry for Ayam Crispi Macdi\n";
        }
        
        // Check Ayam Goreng Bundo journals
        $bundoDebit = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('coa_id', $coa1161->id)
            ->where('debit', '>', 0)
            ->first();
        
        $bundoKredit = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
            ->where('coa_id', $coa117->id)
            ->where('kredit', '>', 0)
            ->where('keterangan', 'like', '%Ayam Goreng Bundo%')
            ->first();
        
        if ($bundoDebit && !$bundoKredit) {
            echo "Creating missing credit entry for Ayam Goreng Bundo:\n";
            
            $newKredit = [
                'coa_id' => $coa117->id,
                'tanggal' => $bundoDebit->tanggal,
                'keterangan' => 'Transfer WIP ke Barang Jadi - Ayam Goreng Bundo',
                'debit' => 0,
                'kredit' => $bundoDebit->debit,
                'referensi' => 'PROD-AYAMGORENG-20260412-001',
                'tipe_referensi' => 'produksi',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            \App\Models\JurnalUmum::insert($newKredit);
            echo "SUCCESS: Created credit entry for Ayam Goreng Bundo\n";
        }
        
        \DB::commit();
        echo "\nSUCCESS: All missing entries have been created!\n";
        
    } catch (\Exception $e) {
        \DB::rollBack();
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nFinal verification:\n";
echo "==================\n";

$finalJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->orderBy('id')
    ->get();

$finalDebit = 0;
$finalKredit = 0;

foreach ($finalJournals as $journal) {
    echo "ID: {$journal->id} | Ref: {$journal->referensi} | COA: {$journal->coa->kode_akun} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
    $finalDebit += $journal->debit;
    $finalKredit += $journal->kredit;
}

echo "\nFinal Total Debit: " . number_format($finalDebit, 0, ',', '.') . "\n";
echo "Final Total Kredit: " . number_format($finalKredit, 0, ',', '.') . "\n";
echo "Final Balance: " . ($finalDebit == $finalKredit ? "BALANCED" : "NOT BALANCED") . "\n";

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
