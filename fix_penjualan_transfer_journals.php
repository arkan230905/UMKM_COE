<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing penjualan transfer journals...\n\n";

// Find all transfer penjualan
$transferPenjualans = App\Models\Penjualan::where('payment_method', 'transfer')->get();

echo "Found " . $transferPenjualans->count() . " transfer penjualan\n";

foreach ($transferPenjualans as $penjualan) {
    echo "\nProcessing: {$penjualan->nomor_penjualan}\n";
    
    // Find the existing journal for this penjualan
    $existingJournals = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
        ->where('referensi', $penjualan->nomor_penjualan)
        ->where('debit', '>', 0) // Find the debit journal (kas entry)
        ->where('kredit', 0)
        ->get();
    
    foreach ($existingJournals as $journal) {
        echo "  - Found debit journal: COA {$journal->coa_id} ({$journal->debit})\n";
        
        // Check if it's using Kas (112) instead of Kas Bank (111)
        if ($journal->coa_id == 3) { // COA ID 3 is Kas (112)
            echo "  - Updating to Kas Bank (111)...\n";
            
            // Update to use Kas Bank (COA ID 2)
            $journal->coa_id = 2; // COA ID 2 is Kas Bank (111)
            $journal->save();
            
            echo "  - Updated successfully\n";
        } else {
            echo "  - Already using correct COA\n";
        }
    }
}

echo "\nVerifying updated journals:\n";

$updatedJournals = App\Models\JurnalUmum::where('tipe_referensi', 'penjualan')
    ->where('referensi', 'like', 'SJ-20260412-%')
    ->where('debit', '>', 0)
    ->with('coa')
    ->get();

foreach ($updatedJournals as $journal) {
    echo "Journal: {$journal->referensi} | COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun} | Debit: {$journal->debit}\n";
}

echo "\nDone!\n";
?>
