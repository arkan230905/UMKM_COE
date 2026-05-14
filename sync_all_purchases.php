<?php

/**
 * Script untuk menyesuaikan semua stok dengan pembelian dari screenshot
 * Jalankan: php sync_all_purchases.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

echo "🔄 SYNC ALL PURCHASES FROM SCREENSHOT\n";
echo "=======================================\n\n";

// Login as user 1
auth()->loginUsingId(1);

try {
    // Data pembelian dari screenshot
    $purchaseData = [
        [
            'name' => 'Jagung',
            'type' => 'bahan_baku',
            'purchases' => [
                ['qty' => 10, 'price' => 50000],
                ['qty' => 5, 'price' => 50000],
            ]
        ],
        [
            'name' => 'Susu',
            'type' => 'bahan_pendukung',
            'purchases' => [
                ['qty' => 10, 'price' => 12000],
                ['qty' => 10, 'price' => 25000],
            ]
        ],
        [
            'name' => 'Kemasan Cup',
            'type' => 'bahan_pendukung',
            'purchases' => [
                ['qty' => 5, 'price' => 20000],
            ]
        ],
    ];
    
    foreach ($purchaseData as $data) {
        echo str_repeat("-", 60) . "\n";
        echo "📦 {$data['name']}\n";
        
        // Find item
        if ($data['type'] == 'bahan_baku') {
            $item = BahanBaku::where('nama_bahan', 'LIKE', "%{$data['name']}%")->first();
            $itemType = 'material';
        } else {
            $item = BahanPendukung::where('nama_bahan', $data['name'])->first();
            $itemType = 'support';
        }
        
        if (!$item) {
            echo "   ⚠️  Item tidak ditemukan, skip...\n\n";
            continue;
        }
        
        echo "   ID: {$item->id}\n";
        echo "   Satuan: {$item->satuan->nama}\n";
        echo "   Saldo Awal: " . number_format($item->saldo_awal, 2, ',', '.') . "\n";
        
        // Check existing purchases
        $existingPurchases = StockMovement::where('item_type', $itemType)
            ->where('item_id', $item->id)
            ->where('direction', 'in')
            ->where('ref_type', 'purchase')
            ->get();
        
        echo "   Existing Purchases: {$existingPurchases->count()}\n";
        
        $totalExistingQty = $existingPurchases->sum('qty');
        $totalExpectedQty = array_sum(array_column($data['purchases'], 'qty'));
        
        echo "   Total Existing Qty: " . number_format($totalExistingQty, 2, ',', '.') . "\n";
        echo "   Total Expected Qty: " . number_format($totalExpectedQty, 2, ',', '.') . "\n";
        
        // Add missing purchases
        if ($totalExistingQty < $totalExpectedQty) {
            $qtyToAdd = $totalExpectedQty - $totalExistingQty;
            echo "\n   ➕ Adding missing purchases ({$qtyToAdd} units)...\n";
            
            // Find which purchases are missing
            $addedQty = $totalExistingQty;
            foreach ($data['purchases'] as $purchase) {
                if ($addedQty >= $totalExpectedQty) break;
                
                $qtyNeeded = min($purchase['qty'], $totalExpectedQty - $addedQty);
                
                if ($qtyNeeded > 0) {
                    StockMovement::create([
                        'user_id' => auth()->id(),
                        'item_type' => $itemType,
                        'item_id' => $item->id,
                        'tanggal' => now()->format('Y-m-d'),
                        'direction' => 'in',
                        'qty' => $qtyNeeded,
                        'unit' => $item->satuan->nama,
                        'unit_cost' => $purchase['price'],
                        'total_cost' => $qtyNeeded * $purchase['price'],
                        'ref_type' => 'purchase',
                        'ref_id' => 0,
                        'keterangan' => "Pembelian {$data['name']}",
                    ]);
                    
                    echo "      ✅ Added: {$qtyNeeded} @ Rp " . number_format($purchase['price'], 0, ',', '.') . "\n";
                    $addedQty += $qtyNeeded;
                }
            }
        } else {
            echo "   ✅ Purchases already complete\n";
        }
        
        // Show final stock
        $item->refresh();
        $finalStock = $item->stok_real_time;
        $expectedStock = $item->saldo_awal + $totalExpectedQty;
        
        echo "\n   📊 Final Stock:\n";
        echo "      Expected: " . number_format($expectedStock, 2, ',', '.') . "\n";
        echo "      Actual: " . number_format($finalStock, 2, ',', '.') . "\n";
        
        if ($finalStock == $expectedStock) {
            echo "      ✅ MATCH!\n";
        } else {
            echo "      ⚠️  MISMATCH!\n";
        }
        
        echo "\n";
    }
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ SYNC COMPLETED\n\n";
    
    // Summary
    echo "📋 SUMMARY:\n";
    
    $jagung = BahanBaku::where('nama_bahan', 'LIKE', '%Jagung%')->first();
    if ($jagung) {
        echo "   🌽 Jagung: " . number_format($jagung->stok_real_time, 2, ',', '.') . " {$jagung->satuan->nama}\n";
        echo "      (Saldo Awal + Pembelian 10 + Pembelian 5)\n";
    }
    
    $susu = BahanPendukung::where('nama_bahan', 'Susu')->first();
    if ($susu) {
        echo "   🥛 Susu: " . number_format($susu->stok_real_time, 2, ',', '.') . " {$susu->satuan->nama}\n";
        echo "      (Saldo Awal + Pembelian 10 + Pembelian 10)\n";
    }
    
    $kemasanCup = BahanPendukung::where('nama_bahan', 'Kemasan Cup')->first();
    if ($kemasanCup) {
        echo "   📦 Kemasan Cup: " . number_format($kemasanCup->stok_real_time, 2, ',', '.') . " {$kemasanCup->satuan->nama}\n";
        echo "      (Saldo Awal + Pembelian 5)\n";
    }
    
    $keju = BahanPendukung::where('nama_bahan', 'Keju')->first();
    if ($keju) {
        echo "   🧀 Keju: " . number_format($keju->stok_real_time, 2, ',', '.') . " {$keju->satuan->nama}\n";
        echo "      (Saldo Awal, no purchases)\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
