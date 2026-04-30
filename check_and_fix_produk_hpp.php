<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking and fixing produk HPP values...\n";

// Get all produk
$produks = \App\Models\Produk::where('user_id', 1)->get();
echo "Found " . $produks->count() . " produk records\n";

foreach ($produks as $produk) {
    echo "\n=== Produk: " . $produk->nama_produk . " ===\n";
    echo "Harga: Rp " . number_format($produk->harga ?? 0, 0, ',', '.') . "\n";
    echo "HPP: Rp " . number_format($produk->hpp ?? 0, 0, ',', '.') . "\n";
    echo "Harga Pokok: Rp " . number_format($produk->harga_pokok ?? 0, 0, ',', '.') . "\n";
    echo "Harga BOM: Rp " . number_format($produk->harga_bom ?? 0, 0, ',', '.') . "\n";
    
    // Set HPP if it's 0
    if (($produk->hpp ?? 0) == 0) {
        $hppValue = $produk->harga_pokok ?? $produk->harga_bom ?? ($produk->harga * 0.6); // Default 60% of harga
        
        if ($hppValue > 0) {
            $produk->update([
                'hpp' => $hppValue,
                'updated_at' => now(),
            ]);
            
            echo "Updated HPP to: Rp " . number_format($hppValue, 0, ',', '.') . "\n";
        } else {
            echo "No valid HPP value found - setting to 60% of harga\n";
            $defaultHpp = $produk->harga * 0.6;
            
            $produk->update([
                'hpp' => $defaultHpp,
                'updated_at' => now(),
            ]);
            
            echo "Set default HPP to: Rp " . number_format($defaultHpp, 0, ',', '.') . "\n";
        }
    } else {
        echo "HPP already set\n";
    }
}

echo "\n=== Re-running HPP fix for penjualan ===\n";

// Now re-run the penjualan HPP fix
$penjualans = \App\Models\Penjualan::with(['details.produk'])->get();
echo "Found " . $penjualans->count() . " penjualan records\n";

foreach ($penjualans as $penjualan) {
    echo "\n=== Processing Penjualan #" . $penjualan->id . " ===\n";
    echo "Tanggal: " . $penjualan->tanggal . "\n";
    echo "Total: Rp " . number_format($penjualan->total, 0, ',', '.') . "\n";
    echo "Current Total HPP: Rp " . number_format($penjualan->total_hpp ?? 0, 0, ',', '.') . "\n";
    
    // Calculate actual HPP based on details
    $actualHpp = 0;
    foreach ($penjualan->details as $detail) {
        $produkHpp = $detail->produk->hpp ?? $detail->produk->harga_pokok ?? 0;
        $detailHpp = $produkHpp * $detail->jumlah;
        $actualHpp += $detailHpp;
        
        echo "  - " . $detail->produk->nama_produk . " x" . $detail->jumlah . " @ Rp " . number_format($produkHpp, 0, ',', '.') . " = Rp " . number_format($detailHpp, 0, ',', '.') . "\n";
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
            
            $hasHpp = false;
            $hasPersediaan = false;
            
            foreach ($jurnalEntries as $jurnal) {
                if (strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
                    strpos($jurnal->coa->kode_akun, '56') !== false) {
                    $hasHpp = true;
                    echo "  - HPP: " . $jurnal->coa->nama_akun . " - " . ($jurnal->debit > 0 ? "Debit Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit Rp " . number_format($jurnal->kredit, 0, ',', '.')) . "\n";
                }
                
                if (strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
                    strpos($jurnal->coa->kode_akun, '116') !== false) {
                    $hasPersediaan = true;
                    echo "  - Persediaan: " . $jurnal->coa->nama_akun . " - " . ($jurnal->debit > 0 ? "Debit Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit Rp " . number_format($jurnal->kredit, 0, ',', '.')) . "\n";
                }
            }
            
            echo "HPP entries: " . ($hasHpp ? "YES" : "NO") . "\n";
            echo "Persediaan entries: " . ($hasPersediaan ? "YES" : "NO") . "\n";
            
            if ($hasHpp && $hasPersediaan) {
                echo "SUCCESS: Complete journal entries created!\n";
            } else {
                echo "WARNING: Some journal entries missing\n";
            }
            
        } catch (Exception $e) {
            echo "Error creating journals: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No HPP to process (actual HPP = 0)\n";
    }
}

echo "\n=== Final Summary ===\n";
$totalPenjualans = $penjualans->count();
$totalWithHpp = $penjualans->where('total_hpp', '>', 0)->count();

echo "Total penjualans processed: {$totalPenjualans}\n";
echo "Penjualans with HPP: {$totalWithHpp}\n";
echo "Penjualans without HPP: " . ($totalPenjualans - $totalWithHpp) . "\n";

echo "\nProduk HPP and penjualan journal fix completed!\n";
