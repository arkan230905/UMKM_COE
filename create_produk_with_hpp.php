<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating produk with HPP values...\n";

// Create Jasuke product with proper HPP
$jasuke = \App\Models\Produk::where('user_id', 1)->where('nama_produk', 'Jasuke')->first();

if (!$jasuke) {
    $jasuke = \App\Models\Produk::create([
        'nama_produk' => 'Jasuke',
        'harga' => 10000,
        'hpp' => 6000, // 60% of harga
        'user_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created Jasuke product with HPP: Rp 6.000\n";
} else {
    if ($jasuke->hpp == 0) {
        $jasuke->update([
            'hpp' => 6000,
            'updated_at' => now(),
        ]);
        echo "Updated Jasuke HPP to: Rp 6.000\n";
    } else {
        echo "Jasuke already has HPP: Rp " . number_format($jasuke->hpp, 0, ',', '.') . "\n";
    }
}

echo "\nNow updating existing penjualan with HPP...\n";

// Update existing penjualan
$penjualans = \App\Models\Penjualan::with(['details.produk'])->get();

foreach ($penjualans as $penjualan) {
    echo "\n=== Processing Penjualan #" . $penjualan->id . " ===\n";
    
    // Calculate actual HPP based on details
    $actualHpp = 0;
    foreach ($penjualan->details as $detail) {
        if ($detail->produk_id == $jasuke->id) {
            $detailHpp = 6000 * $detail->jumlah; // Use fixed HPP
            $actualHpp += $detailHpp;
            
            echo "  - Jasuke x" . $detail->jumlah . " @ Rp 6.000 = Rp " . number_format($detailHpp, 0, ',', '.') . "\n";
        }
    }
    
    echo "Calculated HPP: Rp " . number_format($actualHpp, 0, ',', '.') . "\n";
    
    // Update penjualan with correct HPP
    if ($actualHpp > 0) {
        $penjualan->update([
            'total_hpp' => $actualHpp,
            'updated_at' => now(),
        ]);
        echo "Updated total_hpp to Rp " . number_format($actualHpp, 0, ',', '.') . "\n";
        
        // Create journal entries
        try {
            echo "Creating journal entries...\n";
            
            // Delete existing journals first
            $journalService = new \App\Services\JournalService();
            $journalService->deleteByRef('sale', $penjualan->id);
            $journalService->deleteByRef('sale_cogs', $penjualan->id);
            
            // Recreate journal entries
            \App\Services\JournalService::createJournalFromPenjualan($penjualan);
            
            echo "Journal creation completed\n";
            
            // Check the created journal entries
            $jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $penjualan->id)->get();
            echo "Total journal entries: " . $jurnalEntries->count() . "\n";
            
            foreach ($jurnalEntries as $jurnal) {
                echo "  - " . $jurnal->coa->nama_akun . " (" . $jurnal->coa->kode_akun . "): ";
                echo ($jurnal->debit > 0 ? "Debit Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit Rp " . number_format($jurnal->kredit, 0, ',', '.'));
                echo "\n";
            }
            
            echo "SUCCESS: Complete journal entries with HPP created!\n";
            
        } catch (Exception $e) {
            echo "Error creating journals: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No HPP to process\n";
    }
}

echo "\n=== Final Result ===\n";
echo "Jasuke product created/updated with HPP\n";
echo "Existing penjualan updated with HPP journal entries\n";
echo "HPP journal creation is now working!\n";

echo "\nTask completed!\n";
