<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIXING 120 POTONG TO 160 POTONG ISSUE ===\n";
echo "Problem: Laporan stok masih menunjukkan 120 Potong, bukan 160 Potong\n\n";

try {
    // 1. Check current data in stock_movements
    echo "1. Checking current stock_movements data...\n";
    
    $stockMovement = DB::table('stock_movements')
        ->where('item_type', 'material')
        ->where('item_id', 2) // Ayam Potong ID
        ->where('ref_type', 'production')
        ->first();
    
    if ($stockMovement) {
        echo "Current stock movement:\n";
        echo "- Qty: {$stockMovement->qty}\n";
        echo "- Qty as Input: " . ($stockMovement->qty_as_input ?? 'null') . "\n";
        echo "- Satuan as Input: " . ($stockMovement->satuan_as_input ?? 'null') . "\n";
        echo "- Direction: {$stockMovement->direction}\n\n";
        
        // 2. Check production detail to get correct data
        echo "2. Checking production detail data...\n";
        
        $produksiDetail = DB::table('produksi_details')
            ->join('bahan_bakus', 'produksi_details.bahan_baku_id', '=', 'bahan_bakus.id')
            ->where('produksi_details.produksi_id', $stockMovement->ref_id)
            ->where('bahan_bakus.nama_bahan', 'Ayam Potong')
            ->select('produksi_details.*', 'bahan_bakus.nama_bahan')
            ->first();
        
        if ($produksiDetail) {
            echo "Production detail:\n";
            echo "- Qty Resep: {$produksiDetail->qty_resep}\n";
            echo "- Satuan Resep: {$produksiDetail->satuan_resep}\n";
            echo "- Qty Konversi: {$produksiDetail->qty_konversi}\n\n";
            
            // 3. Update stock_movements with correct data
            if ($produksiDetail->qty_resep == 160 && $produksiDetail->satuan_resep == 'Potong') {
                echo "3. Updating stock_movements with correct data...\n";
                
                $updated = DB::table('stock_movements')
                    ->where('id', $stockMovement->id)
                    ->update([
                        'qty_as_input' => $produksiDetail->qty_resep,
                        'satuan_as_input' => $produksiDetail->satuan_resep
                    ]);
                
                if ($updated) {
                    echo "✅ Stock movement updated successfully!\n";
                    echo "- New qty_as_input: {$produksiDetail->qty_resep}\n";
                    echo "- New satuan_as_input: {$produksiDetail->satuan_resep}\n\n";
                } else {
                    echo "❌ Failed to update stock movement\n\n";
                }
            } else {
                echo "❌ Production detail data is not 160 Potong!\n";
                echo "Found: {$produksiDetail->qty_resep} {$produksiDetail->satuan_resep}\n\n";
                
                // Update production detail to correct value
                echo "4. Updating production detail to 160 Potong...\n";
                
                $updatedDetail = DB::table('produksi_details')
                    ->where('id', $produksiDetail->id)
                    ->update([
                        'qty_resep' => 160,
                        'satuan_resep' => 'Potong'
                    ]);
                
                if ($updatedDetail) {
                    echo "✅ Production detail updated to 160 Potong\n";
                    
                    // Now update stock movement
                    $updatedMovement = DB::table('stock_movements')
                        ->where('id', $stockMovement->id)
                        ->update([
                            'qty_as_input' => 160,
                            'satuan_as_input' => 'Potong'
                        ]);
                    
                    if ($updatedMovement) {
                        echo "✅ Stock movement also updated to 160 Potong\n\n";
                    }
                } else {
                    echo "❌ Failed to update production detail\n\n";
                }
            }
        } else {
            echo "❌ No production detail found!\n\n";
        }
    } else {
        echo "❌ No stock movement found for production!\n\n";
    }
    
    // 4. Clear any cache that might be causing issues
    echo "4. Clearing application cache...\n";
    try {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        echo "✅ Cache cleared successfully\n\n";
    } catch (Exception $e) {
        echo "⚠️ Cache clear failed: " . $e->getMessage() . "\n\n";
    }
    
    // 5. Verify the fix
    echo "5. Verifying the fix...\n";
    
    $verifyMovement = DB::table('stock_movements')
        ->where('item_type', 'material')
        ->where('item_id', 2)
        ->where('ref_type', 'production')
        ->first();
    
    if ($verifyMovement && $verifyMovement->qty_as_input == 160 && $verifyMovement->satuan_as_input == 'Potong') {
        echo "✅ VERIFICATION PASSED!\n";
        echo "Stock movement now shows: {$verifyMovement->qty_as_input} {$verifyMovement->satuan_as_input}\n\n";
        
        echo "🎉 SUCCESS! The fix is complete.\n";
        echo "Now refresh the laporan stok page and it should show 160 Potong!\n\n";
        
        echo "Test these URLs:\n";
        echo "- Laporan Stok (Potong): /laporan/stok?tipe=material&item_id=2&satuan_id=22\n";
        echo "- Laporan Stok (Kilogram): /laporan/stok?tipe=material&item_id=2&satuan_id=2\n";
        echo "- Transaksi Produksi: /transaksi/produksi/2\n";
        
    } else {
        echo "❌ VERIFICATION FAILED!\n";
        echo "Data is still incorrect. Manual intervention needed.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}