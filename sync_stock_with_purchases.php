<?php

/**
 * Script untuk menyesuaikan stok dengan data pembelian dari screenshot
 * Jalankan: php sync_stock_with_purchases.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

echo "🔄 SYNC STOCK WITH PURCHASES\n";
echo "==============================\n\n";

// Login as user 1
auth()->loginUsingId(1);

try {
    // Data dari screenshot pembelian
    $purchases = [
        ['item' => 'Jagung', 'type' => 'bahan_baku', 'qty' => 10, 'price' => 50000],
        ['item' => 'Susu', 'type' => 'bahan_pendukung', 'qty' => 10, 'price' => 12000],
        ['item' => 'Susu', 'type' => 'bahan_pendukung', 'qty' => 10, 'price' => 25000],
        ['item' => 'Kemasan Cup', 'type' => 'bahan_pendukung', 'qty' => 5, 'price' => 20000],
        ['item' => 'Jagung', 'type' => 'bahan_baku', 'qty' => 5, 'price' => 50000],
    ];
    
    echo "📊 Current Stock Status:\n\n";
    
    // Check Jagung (Bahan Baku)
    $jagung = BahanBaku::where('nama_bahan', 'LIKE', '%Jagung%')->first();
    if ($jagung) {
        echo "🌽 Jagung (Bahan Baku):\n";
        echo "   Saldo Awal: " . number_format($jagung->saldo_awal, 2, ',', '.') . " {$jagung->satuan->nama}\n";
        
        $movements = StockMovement::where('item_type', 'material')
            ->where('item_id', $jagung->id)
            ->get();
        
        $totalIn = $movements->where('direction', 'in')->sum('qty');
        $totalOut = $movements->where('direction', 'out')->sum('qty');
        
        echo "   Stock IN: " . number_format($totalIn, 2, ',', '.') . "\n";
        echo "   Stock OUT: " . number_format($totalOut, 2, ',', '.') . "\n";
        echo "   Current Stock: " . number_format($jagung->stok_real_time, 2, ',', '.') . "\n";
        
        // Expected: Saldo Awal + Pembelian (10 + 5 = 15)
        $expectedPurchases = 10 + 5;
        echo "   Expected Purchases: {$expectedPurchases}\n";
        
        $purchaseMovements = $movements->where('direction', 'in')->where('ref_type', 'purchase');
        $actualPurchases = $purchaseMovements->sum('qty');
        echo "   Actual Purchases: " . number_format($actualPurchases, 2, ',', '.') . "\n\n";
    } else {
        echo "⚠️  Jagung tidak ditemukan\n\n";
    }
    
    // Check Susu (Bahan Pendukung)
    $susu = BahanPendukung::where('nama_bahan', 'Susu')->first();
    if ($susu) {
        echo "🥛 Susu (Bahan Pendukung):\n";
        echo "   Saldo Awal: " . number_format($susu->saldo_awal, 2, ',', '.') . " {$susu->satuan->nama}\n";
        
        $movements = StockMovement::where('item_type', 'support')
            ->where('item_id', $susu->id)
            ->get();
        
        $totalIn = $movements->where('direction', 'in')->sum('qty');
        $totalOut = $movements->where('direction', 'out')->sum('qty');
        
        echo "   Stock IN: " . number_format($totalIn, 2, ',', '.') . "\n";
        echo "   Stock OUT: " . number_format($totalOut, 2, ',', '.') . "\n";
        echo "   Current Stock: " . number_format($susu->stok_real_time, 2, ',', '.') . "\n";
        
        // Expected: Saldo Awal + Pembelian (10 + 10 = 20)
        $expectedPurchases = 10 + 10;
        echo "   Expected Purchases: {$expectedPurchases}\n";
        
        $purchaseMovements = $movements->where('direction', 'in')->where('ref_type', 'purchase');
        $actualPurchases = $purchaseMovements->sum('qty');
        echo "   Actual Purchases: " . number_format($actualPurchases, 2, ',', '.') . "\n\n";
    } else {
        echo "⚠️  Susu tidak ditemukan\n\n";
    }
    
    // Check Kemasan Cup (Bahan Pendukung)
    $kemasanCup = BahanPendukung::where('nama_bahan', 'Kemasan Cup')->first();
    if ($kemasanCup) {
        echo "📦 Kemasan Cup (Bahan Pendukung):\n";
        echo "   Saldo Awal: " . number_format($kemasanCup->saldo_awal, 2, ',', '.') . " {$kemasanCup->satuan->nama}\n";
        
        $movements = StockMovement::where('item_type', 'support')
            ->where('item_id', $kemasanCup->id)
            ->get();
        
        $totalIn = $movements->where('direction', 'in')->sum('qty');
        $totalOut = $movements->where('direction', 'out')->sum('qty');
        
        echo "   Stock IN: " . number_format($totalIn, 2, ',', '.') . "\n";
        echo "   Stock OUT: " . number_format($totalOut, 2, ',', '.') . "\n";
        echo "   Current Stock: " . number_format($kemasanCup->stok_real_time, 2, ',', '.') . "\n";
        
        // Expected: Saldo Awal + Pembelian (5)
        $expectedPurchases = 5;
        echo "   Expected Purchases: {$expectedPurchases}\n";
        
        $purchaseMovements = $movements->where('direction', 'in')->where('ref_type', 'purchase');
        $actualPurchases = $purchaseMovements->sum('qty');
        echo "   Actual Purchases: " . number_format($actualPurchases, 2, ',', '.') . "\n\n";
    } else {
        echo "⚠️  Kemasan Cup tidak ditemukan\n\n";
    }
    
    // Check Keju (Bahan Pendukung)
    $keju = BahanPendukung::where('nama_bahan', 'Keju')->first();
    if ($keju) {
        echo "🧀 Keju (Bahan Pendukung):\n";
        echo "   Saldo Awal: " . number_format($keju->saldo_awal, 2, ',', '.') . " {$keju->satuan->nama}\n";
        echo "   Current Stock: " . number_format($keju->stok_real_time, 2, ',', '.') . "\n";
        echo "   Expected: Saldo Awal (no purchases)\n\n";
    }
    
    echo "==============================\n";
    echo "✅ Stock check completed\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
