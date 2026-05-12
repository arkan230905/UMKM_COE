<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FORCE FIXING DATABASE JOURNALS\n";
echo "==============================\n\n";

// Get COA references
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first();
$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first();

echo "COA References:\n";
echo "- 116 (ID {$coa116->id}): {$coa116->nama_akun}\n";
echo "- 1161 (ID {$coa1161->id}): {$coa1161->nama_akun}\n\n";

// Find ALL journals that might be wrong
echo "Checking ALL journals with amounts 3.864.960 and 3.368.960:\n";
echo "====================================================\n";

$amounts = [3864960, 3368960];

foreach ($amounts as $amount) {
    echo "\nAmount: " . number_format($amount, 0, ',', '.') . "\n";
    
    $journals = \App\Models\JurnalUmum::where(function($query) use ($amount) {
            $query->where('debit', $amount)->orWhere('kredit', $amount);
        })
        ->where('tipe_referensi', 'produksi')
        ->with('coa')
        ->get();
    
    echo "Found {$journals->count()} journals:\n";
    
    foreach ($journals as $journal) {
        echo "  ID: {$journal->id}\n";
        echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
        echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "  Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
        echo "  Keterangan: {$journal->keterangan}\n";
        
        // Check if this should be Ayam Goreng Bundo
        if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false) {
            if ($journal->coa_id == $coa116->id) {
                echo "  -> WRONG: Should use COA 1161, currently using COA 116!\n";
                
                try {
                    // Force update
                    $journal->coa_id = $coa1161->id;
                    $journal->save();
                    
                    echo "  -> FIXED: Updated to COA 1161\n";
                } catch (\Exception $e) {
                    echo "  -> ERROR: " . $e->getMessage() . "\n";
                }
            } else {
                echo "  -> CORRECT: Using COA 1161\n";
            }
        } elseif (strpos($journal->keterangan, 'Ayam Crispi Macdi') !== false) {
            if ($journal->coa_id == $coa116->id) {
                echo "  -> CORRECT: Using COA 116 for Ayam Crispi Macdi\n";
            } else {
                echo "  -> WRONG: Should use COA 116, currently using COA {$journal->coa->kode_akun}!\n";
                
                try {
                    $journal->coa_id = $coa116->id;
                    $journal->save();
                    echo "  -> FIXED: Updated to COA 116\n";
                } catch (\Exception $e) {
                    echo "  -> ERROR: " . $e->getMessage() . "\n";
                }
            }
        }
        echo "---\n";
    }
}

echo "\nDirect SQL Check:\n";
echo "=================\n";

// Check using raw SQL to be absolutely sure
$sql = "SELECT j.id, j.coa_id, c.kode_akun, c.nama_akun, j.debit, j.kredit, j.keterangan 
        FROM jurnal_umum j 
        JOIN coas c ON j.coa_id = c.id 
        WHERE j.tipe_referensi = 'produksi' 
        AND (j.debit = 3368960 OR j.kredit = 3368960 OR j.debit = 3864960 OR j.kredit = 3864960)
        ORDER BY j.id";

$results = \DB::select($sql);

foreach ($results as $row) {
    echo "ID: {$row->id} | COA: {$row->kode_akun} | D: {$row->debit} | K: {$row->kredit} | {$row->keterangan}\n";
    
    // Force update if needed
    if (strpos($row->keterangan, 'Ayam Goreng Bundo') !== false && $row->kode_akun == '116') {
        echo "  -> FORCE UPDATING to COA 1161...\n";
        \DB::update("UPDATE jurnal_umum SET coa_id = ? WHERE id = ?", [$coa1161->id, $row->id]);
        echo "  -> UPDATED!\n";
    }
}

echo "\nFinal verification:\n";
echo "==================\n";

$finalResults = \DB::select($sql);

foreach ($finalResults as $row) {
    echo "ID: {$row->id} | COA: {$row->kode_akun} | D: {$row->debit} | K: {$row->kredit} | {$row->keterangan}\n";
}

echo "\nExpected final state:\n";
echo "====================\n";
echo "Ayam Crispi Macdi journals should use COA 116\n";
echo "Ayam Goreng Bundo journals should use COA 1161\n";

echo "\nDone!\n";

?>
