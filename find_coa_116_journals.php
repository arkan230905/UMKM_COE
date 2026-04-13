<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FINDING ALL JOURNALS USING COA 116\n";
echo "===================================\n\n";

// Get COA 116
$coa116 = \App\Models\Coa::where('kode_akun', '116')->first();

if (!$coa116) {
    echo "COA 116 not found!\n";
    exit;
}

echo "COA 116: {$coa116->kode_akun} - {$coa116->nama_akun}\n\n";

// Find all journals using COA 116
$journals = \App\Models\JurnalUmum::where('coa_id', $coa116->id)
    ->with('coa')
    ->orderBy('tanggal', 'desc')
    ->orderBy('id', 'desc')
    ->get();

echo "Found {$journals->count()} journals using COA 116:\n\n";

foreach ($journals as $journal) {
    echo "Journal ID: {$journal->id}\n";
    echo "  Tanggal: {$journal->tanggal}\n";
    echo "  Tipe: {$journal->tipe_referensi}\n";
    echo "  Referensi: {$journal->referensi}\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    
    // Check if this is related to Ayam Goreng Bundo produksi
    $isAyamGorengBundo = false;
    if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false || 
        strpos($journal->referensi, 'Ayam Goreng Bundo') !== false ||
        $journal->tipe_referensi === 'produksi') {
        $isAyamGorengBundo = true;
    }
    
    if ($isAyamGorengBundo) {
        echo "  -> This should use COA 1161 (Pers. Barang Jadi Ayam Goreng Bundo)!\n";
        
        // Get the new COA
        $newCoa = \App\Models\Coa::where('kode_akun', '1161')->first();
        if ($newCoa) {
            echo "  -> Updating to COA {$newCoa->kode_akun}...\n";
            
            $journal->coa_id = $newCoa->id;
            $journal->save();
            
            echo "  -> Updated successfully!\n";
        } else {
            echo "  -> ERROR: COA 1161 not found!\n";
        }
    }
    
    echo "---\n";
}

echo "\nChecking for produksi journals specifically:\n";
echo "======================================\n";

$produksiJournals = \App\Models\JurnalUmum::where('tipe_referensi', 'produksi')
    ->with('coa')
    ->get();

echo "Found {$produksiJournals->count()} produksi journals:\n\n";

foreach ($produksiJournals as $journal) {
    echo "Produksi Journal ID: {$journal->id}\n";
    echo "  Tanggal: {$journal->tanggal}\n";
    echo "  COA: {$journal->coa->kode_akun} - {$journal->coa->nama_akun}\n";
    echo "  Keterangan: {$journal->keterangan}\n";
    echo "  Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
    echo "  Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
    
    // Check if this is Ayam Goreng Bundo
    if (strpos($journal->keterangan, 'Ayam Goreng Bundo') !== false) {
        echo "  -> This is Ayam Goreng Bundo produksi!\n";
        
        $newCoa = \App\Models\Coa::where('kode_akun', '1161')->first();
        if ($newCoa && $journal->coa_id == 116) {
            echo "  -> Updating to COA {$newCoa->kode_akun}...\n";
            
            $journal->coa_id = $newCoa->id;
            $journal->save();
            
            echo "  -> Updated successfully!\n";
        }
    }
    
    echo "---\n";
}

echo "\nVerification - Check journals using COA 1161:\n";
echo "==========================================\n";

$coa1161 = \App\Models\Coa::where('kode_akun', '1161')->first();
if ($coa1161) {
    $journals1161 = \App\Models\JurnalUmum::where('coa_id', $coa1161->id)
        ->with('coa')
        ->get();
    
    echo "Journals using COA 1161:\n";
    foreach ($journals1161 as $journal) {
        echo "  {$journal->referensi} | D: " . number_format($journal->debit, 0, ',', '.') . " | K: " . number_format($journal->kredit, 0, ',', '.') . " | {$journal->keterangan}\n";
    }
}

echo "\nDone!\n";

?>
