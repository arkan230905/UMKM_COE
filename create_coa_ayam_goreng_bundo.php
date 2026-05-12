<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "CREATING COA FOR AYAM GORENG BUNDO\n";
echo "===================================\n\n";

try {
    // Check if COA already exists
    $existingCoa = \App\Models\Coa::where('nama_akun', 'like', '%Ayam Goreng Bundo%')
        ->orWhere('kode_akun', '1161')
        ->first();
    
    if ($existingCoa) {
        echo "COA already exists: {$existingCoa->kode_akun} - {$existingCoa->nama_akun}\n";
        $coaId = $existingCoa->id;
    } else {
        // Create new COA for Ayam Goreng Bundo
        $newCoa = \App\Models\Coa::create([
            'kode_akun' => '1161',
            'nama_akun' => 'Pers. Barang Jadi Ayam Goreng Bundo',
            'tipe_akun' => 'Asset',
            'saldo_normal' => 'Debit',
            'kategori_akun' => 'Persediaan Barang Jadi',
            'status' => 'aktif',
            'created_by' => 1,
        ]);
        
        echo "Created new COA: 1161 - Pers. Barang Jadi Ayam Goreng Bundo (ID: {$newCoa->id})\n";
        $coaId = $newCoa->id;
    }
    
    // Update produk Ayam Goreng Bundo with the new COA
    $produk = \App\Models\Produk::where('nama_produk', 'Ayam Goreng Bundo')->first();
    
    if ($produk) {
        $produk->coa_persediaan_id = $coaId;
        $produk->save();
        
        echo "Updated produk 'Ayam Goreng Bundo' with COA persediaan ID: {$coaId}\n";
    } else {
        echo "Produk 'Ayam Goreng Bundo' not found!\n";
    }
    
    echo "\nNow checking if there are any wrong journals to fix:\n";
    
    // Look for journals that should use the new COA
    $wrongJournals = \App\Models\JurnalUmum::where('coa_id', 116) // Currently using Pers. Barang Jadi Ayam Crispi Macdi
        ->where('tipe_referensi', 'produksi')
        ->whereDate('tanggal', '2026-04-12')
        ->get();
    
    if ($wrongJournals->count() > 0) {
        echo "Found {$wrongJournals->count()} wrong journals to fix:\n";
        
        foreach ($wrongJournals as $journal) {
            echo "  Journal ID {$journal->id}: {$journal->referensi} | D: {$journal->debit} | K: {$journal->kredit}\n";
            
            // Update to use the correct COA
            $journal->coa_id = $coaId;
            $journal->save();
            
            echo "    Updated to use COA {$coaId}\n";
        }
    } else {
        echo "No wrong journals found for produksi on 2026-04-12\n";
    }
    
    echo "\nDone! COA and produk have been updated.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
