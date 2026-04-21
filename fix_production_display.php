<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Production Display Issue ===\n";
echo "Problem: Transaksi Produksi shows 160 potong, but Laporan Stok shows 120 potong\n\n";

try {
    // 1. Check production ID 2 data
    echo "1. Checking Production ID 2 data...\n";
    
    $produksi = \App\Models\Produksi::find(2);
    if ($produksi) {
        echo "Production found: {$produksi->kode_produksi}\n";
        echo "Qty Produksi: {$produksi->qty_produksi}\n";
        echo "Status: {$produksi->status}\n\n";
        
        // Check production details for Ayam Potong
        $ayamPotongDetail = \App\Models\ProduksiDetail::where('produksi_id', 2)
            ->whereHas('bahanBaku', function($q) {
                $q->where('nama_bahan', 'Ayam Potong');
            })
            ->first();
        
        if ($ayamPotongDetail) {
            echo "Production Detail for Ayam Potong:\n";
            echo "- Qty Resep: {$ayamPotongDetail->qty_resep} {$ayamPotongDetail->satuan_resep}\n";
            echo "- Qty Konversi: {$ayamPotongDetail->qty_konversi}\n";
            echo "- Subtotal: {$ayamPotongDetail->subtotal}\n\n";
            
            // Check corresponding stock movement
            $stockMovement = \App\Models\StockMovement::where('item_type', 'material')
                ->where('item_id', 2) // Ayam Potong ID
                ->where('ref_type', 'production')
                ->where('ref_id', 2)
                ->first();
            
            if ($stockMovement) {
                echo "Stock Movement for this production:\n";
                echo "- Qty: {$stockMovement->qty}\n";
                echo "- Qty as Input: " . ($stockMovement->qty_as_input ?? 'null') . "\n";
                echo "- Satuan as Input: " . ($stockMovement->satuan_as_input ?? 'null') . "\n";
                echo "- Direction: {$stockMovement->direction}\n\n";
                
                // Check if qty_as_input matches production detail
                if ($stockMovement->qty_as_input != $ayamPotongDetail->qty_resep) {
                    echo "❌ MISMATCH FOUND!\n";
                    echo "Production Detail shows: {$ayamPotongDetail->qty_resep} {$ayamPotongDetail->satuan_resep}\n";
                    echo "Stock Movement shows: {$stockMovement->qty_as_input} {$stockMovement->satuan_as_input}\n\n";
                    
                    echo "Fixing stock movement data...\n";
                    $stockMovement->qty_as_input = $ayamPotongDetail->qty_resep;
                    $stockMovement->satuan_as_input = $ayamPotongDetail->satuan_resep;
                    $stockMovement->save();
                    
                    echo "✅ Stock movement updated!\n";
                    echo "- Qty as Input: {$stockMovement->qty_as_input}\n";
                    echo "- Satuan as Input: {$stockMovement->satuan_as_input}\n\n";
                } else {
                    echo "✅ Data already matches!\n";
                    echo "Production Detail: {$ayamPotongDetail->qty_resep} {$ayamPotongDetail->satuan_resep}\n";
                    echo "Stock Movement: {$stockMovement->qty_as_input} {$stockMovement->satuan_as_input}\n\n";
                }
            } else {
                echo "❌ No stock movement found for this production!\n\n";
            }
        } else {
            echo "❌ No production detail found for Ayam Potong!\n\n";
        }
    } else {
        echo "❌ Production ID 2 not found!\n\n";
    }
    
    // 2. Test the fix by checking what kartu stok will show now
    echo "2. Testing Kartu Stok display...\n";
    
    $stockService = new \App\Services\StockService();
    $report = $stockService->getStockReport(2, 'bahan_baku'); // Ayam Potong
    
    echo "Kartu Stok entries for Ayam Potong:\n";
    foreach ($report['entries'] as $entry) {
        if ($entry['ref_type'] === 'production') {
            echo "- Date: {$entry['tanggal']}\n";
            echo "- Keterangan: {$entry['keterangan']}\n";
            echo "- Qty Keluar: {$entry['qty_keluar']}\n\n";
            
            if (strpos($entry['keterangan'], '160') !== false) {
                echo "✅ SUCCESS! Kartu Stok now shows 160 potong\n";
            } else {
                echo "❌ Still not showing 160 potong\n";
            }
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "The issue was that stock_movements.qty_as_input didn't match produksi_details.qty_resep\n";
    echo "Now both should show 160 potong consistently.\n\n";
    
    echo "Please check:\n";
    echo "1. /transaksi/produksi/2 - should show 160 potong\n";
    echo "2. /laporan/kartu-stok?item_type=bahan_baku&item_id=2 - should also show 160 potong\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}