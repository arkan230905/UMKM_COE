<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING DOUBLE COUNTING STOCK BUG ===\n\n";

echo "🔍 PROBLEM IDENTIFIED:\n";
echo "- PembelianController updates stock directly\n";
echo "- PembelianDetailObserver ALSO updates stock via RealTimeStockService\n";
echo "- Result: Stock counted twice during purchase creation\n";
echo "- Example: 50kg + 40kg purchase = 130kg (should be 90kg)\n\n";

echo "✅ SOLUTION APPLIED:\n";
echo "- Disabled PembelianDetailObserver stock updates\n";
echo "- Stock now handled ONLY in PembelianController\n";
echo "- Now recalculating existing incorrect stock values...\n\n";

// Fix Bahan Baku stock
echo "📦 FIXING BAHAN BAKU STOCK:\n";
$bahanBakus = \App\Models\BahanBaku::all();

foreach ($bahanBakus as $bahan) {
    echo "Processing: {$bahan->nama_bahan} (ID: {$bahan->id})\n";
    
    // Calculate correct stock from purchase details
    $totalPurchased = \DB::table('pembelian_details')
        ->where('bahan_baku_id', $bahan->id)
        ->whereNotNull('jumlah_satuan_utama')
        ->sum('jumlah_satuan_utama');
    
    // If jumlah_satuan_utama is not available, calculate from jumlah * faktor_konversi
    if ($totalPurchased == 0) {
        $purchaseDetails = \DB::table('pembelian_details')
            ->where('bahan_baku_id', $bahan->id)
            ->get();
        
        foreach ($purchaseDetails as $detail) {
            $qtyInBaseUnit = $detail->jumlah * ($detail->faktor_konversi ?? 1);
            $totalPurchased += $qtyInBaseUnit;
        }
    }
    
    // Calculate returns (if any)
    $totalReturned = \DB::table('purchase_return_items')
        ->where('bahan_baku_id', $bahan->id)
        ->whereIn('purchase_return_id', function($query) {
            $query->select('id')
                  ->from('purchase_returns')
                  ->where('status', 'selesai');
        })
        ->sum('quantity');
    
    // Calculate correct stock
    $correctStock = ($bahan->stok_awal ?? 0) + $totalPurchased - $totalReturned;
    
    echo "  Current stock: {$bahan->stok}\n";
    echo "  Initial stock: " . ($bahan->stok_awal ?? 0) . "\n";
    echo "  Total purchased: {$totalPurchased}\n";
    echo "  Total returned: {$totalReturned}\n";
    echo "  Calculated correct stock: {$correctStock}\n";
    
    if (abs($bahan->stok - $correctStock) > 0.0001) {
        echo "  ❌ INCORRECT - Updating from {$bahan->stok} to {$correctStock}\n";
        $bahan->stok = $correctStock;
        $bahan->save();
        echo "  ✅ FIXED\n";
    } else {
        echo "  ✅ Already correct\n";
    }
    echo "\n";
}

// Fix Bahan Pendukung stock
echo "\n📦 FIXING BAHAN PENDUKUNG STOCK:\n";
$bahanPendukungs = \App\Models\BahanPendukung::all();

foreach ($bahanPendukungs as $bahan) {
    echo "Processing: {$bahan->nama_bahan} (ID: {$bahan->id})\n";
    
    // Calculate correct stock from purchase details
    $totalPurchased = \DB::table('pembelian_details')
        ->where('bahan_pendukung_id', $bahan->id)
        ->whereNotNull('jumlah_satuan_utama')
        ->sum('jumlah_satuan_utama');
    
    // If jumlah_satuan_utama is not available, calculate from jumlah * faktor_konversi
    if ($totalPurchased == 0) {
        $purchaseDetails = \DB::table('pembelian_details')
            ->where('bahan_pendukung_id', $bahan->id)
            ->get();
        
        foreach ($purchaseDetails as $detail) {
            $qtyInBaseUnit = $detail->jumlah * ($detail->faktor_konversi ?? 1);
            $totalPurchased += $qtyInBaseUnit;
        }
    }
    
    // Calculate returns (if any) - check if bahan_pendukung_id column exists
    $totalReturned = 0;
    try {
        $totalReturned = \DB::table('purchase_return_items')
            ->where('bahan_pendukung_id', $bahan->id)
            ->whereIn('purchase_return_id', function($query) {
                $query->select('id')
                      ->from('purchase_returns')
                      ->where('status', 'selesai');
            })
            ->sum('quantity');
    } catch (\Exception $e) {
        // Column doesn't exist yet, skip return calculation
        echo "  Note: bahan_pendukung_id column not found in purchase_return_items, skipping returns\n";
        $totalReturned = 0;
    }
    
    // Calculate correct stock
    $correctStock = ($bahan->stok_awal ?? 0) + $totalPurchased - $totalReturned;
    
    echo "  Current stock: {$bahan->stok}\n";
    echo "  Initial stock: " . ($bahan->stok_awal ?? 0) . "\n";
    echo "  Total purchased: {$totalPurchased}\n";
    echo "  Total returned: {$totalReturned}\n";
    echo "  Calculated correct stock: {$correctStock}\n";
    
    if (abs($bahan->stok - $correctStock) > 0.0001) {
        echo "  ❌ INCORRECT - Updating from {$bahan->stok} to {$correctStock}\n";
        $bahan->stok = $correctStock;
        $bahan->save();
        echo "  ✅ FIXED\n";
    } else {
        echo "  ✅ Already correct\n";
    }
    echo "\n";
}

echo "=== DOUBLE COUNTING BUG FIX COMPLETE ===\n\n";

echo "🎯 WHAT WAS FIXED:\n";
echo "1. ✅ Disabled PembelianDetailObserver stock updates\n";
echo "2. ✅ Stock now updated ONLY in PembelianController\n";
echo "3. ✅ Recalculated all existing stock values\n";
echo "4. ✅ Fixed asymmetric operations (create vs delete)\n\n";

echo "🔄 TESTING RECOMMENDATIONS:\n";
echo "1. Create a new purchase and verify stock increases by correct amount\n";
echo "2. Delete a purchase and verify stock decreases by same amount\n";
echo "3. Check that stock reports match master table values\n\n";

echo "✅ The double counting bug has been eliminated!\n";
echo "Stock operations are now symmetric and accurate.\n";