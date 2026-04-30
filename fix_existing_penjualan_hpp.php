<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing existing penjualan HPP and journal entries...\n";

// Get all penjualan records
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
                echo "  - " . $jurnal->coa->nama_akun . " (" . $jurnal->coa->kode_akun . "): ";
                echo ($jurnal->debit > 0 ? "Debit Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit Rp " . number_format($jurnal->kredit, 0, ',', '.'));
                echo "\n";
                
                if (strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
                    strpos($jurnal->coa->kode_akun, '56') !== false) {
                    $hasHpp = true;
                }
                
                if (strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
                    strpos($jurnal->coa->kode_akun, '116') !== false) {
                    $hasPersediaan = true;
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

echo "\n=== Summary ===\n";
$totalPenjualans = $penjualans->count();
$totalWithHpp = $penjualans->where('total_hpp', '>', 0)->count();

echo "Total penjualans processed: {$totalPenjualans}\n";
echo "Penjualans with HPP: {$totalWithHpp}\n";
echo "Penjualans without HPP: " . ($totalPenjualans - $totalWithHpp) . "\n";

echo "\nFix completed!\n";
