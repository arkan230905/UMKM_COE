<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StockMovement;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;

echo "=== FIX MISSING INITIAL STOCK ===\n\n";

try {
    // 1. Fix Bahan Baku
    echo "=== FIXING BAHAN BAKU ===\n";
    
    $bahanBakus = BahanBaku::where('stok', '>', 0)->get();
    
    foreach ($bahanBakus as $bb) {
        // Cek apakah sudah ada initial stock movement
        $hasInitial = StockMovement::where('item_type', 'material')
            ->where('item_id', $bb->id)
            ->where('ref_type', 'initial_stock')
            ->exists();
        
        if (!$hasInitial) {
            // Hitung stok awal berdasarkan master stok - total movements
            $totalMovements = StockMovement::where('item_type', 'material')
                ->where('item_id', $bb->id)
                ->sum(\DB::raw('CASE WHEN direction = "in" THEN qty ELSE -qty END'));
            
            $initialStock = $bb->stok - $totalMovements;
            
            if ($initialStock > 0) {
                // Buat initial stock movement dengan tanggal sebelum movements lain
                $earliestMovement = StockMovement::where('item_type', 'material')
                    ->where('item_id', $bb->id)
                    ->orderBy('tanggal')
                    ->first();
                
                $initialDate = $earliestMovement ? 
                    date('Y-m-d', strtotime($earliestMovement->tanggal . ' -1 day')) : 
                    '2026-04-01';
                
                StockMovement::create([
                    'item_type' => 'material',
                    'item_id' => $bb->id,
                    'tanggal' => $initialDate,
                    'direction' => 'in',
                    'qty' => $initialStock,
                    'satuan' => $bb->satuan ? $bb->satuan->nama : 'KG',
                    'unit_cost' => $bb->harga_satuan ? $bb->harga_satuan : 0,
                    'total_cost' => $initialStock * ($bb->harga_satuan ? $bb->harga_satuan : 0),
                    'ref_type' => 'initial_stock',
                    'ref_id' => 0,
                    'keterangan' => 'Saldo awal stok',
                ]);
                
                echo "✅ Created initial stock for {$bb->nama_bahan}: {$initialStock} " . ($bb->satuan ? $bb->satuan->nama : 'KG') . "\n";
            } else {
                echo "⚠️  {$bb->nama_bahan}: Initial stock calculated as {$initialStock} (skipped)\n";
            }
        } else {
            echo "✅ {$bb->nama_bahan}: Initial stock already exists\n";
        }
    }
    
    // 2. Fix Bahan Pendukung
    echo "\n=== FIXING BAHAN PENDUKUNG ===\n";
    
    $bahanPendukungs = BahanPendukung::where('stok', '>', 0)->get();
    
    foreach ($bahanPendukungs as $bp) {
        // Cek apakah sudah ada initial stock movement
        $hasInitial = StockMovement::where('item_type', 'support')
            ->where('item_id', $bp->id)
            ->where('ref_type', 'initial_stock')
            ->exists();
        
        if (!$hasInitial) {
            // Hitung stok awal berdasarkan master stok - total movements
            $totalMovements = StockMovement::where('item_type', 'support')
                ->where('item_id', $bp->id)
                ->sum(\DB::raw('CASE WHEN direction = "in" THEN qty ELSE -qty END'));
            
            $initialStock = $bp->stok - $totalMovements;
            
            if ($initialStock > 0) {
                // Buat initial stock movement dengan tanggal sebelum movements lain
                $earliestMovement = StockMovement::where('item_type', 'support')
                    ->where('item_id', $bp->id)
                    ->orderBy('tanggal')
                    ->first();
                
                $initialDate = $earliestMovement ? 
                    date('Y-m-d', strtotime($earliestMovement->tanggal . ' -1 day')) : 
                    '2026-04-01';
                
                StockMovement::create([
                    'item_type' => 'support',
                    'item_id' => $bp->id,
                    'tanggal' => $initialDate,
                    'direction' => 'in',
                    'qty' => $initialStock,
                    'satuan' => $bp->satuan ? $bp->satuan->nama : 'Unit',
                    'unit_cost' => $bp->harga_satuan ? $bp->harga_satuan : 0,
                    'total_cost' => $initialStock * ($bp->harga_satuan ? $bp->harga_satuan : 0),
                    'ref_type' => 'initial_stock',
                    'ref_id' => 0,
                    'keterangan' => 'Saldo awal stok',
                ]);
                
                echo "✅ Created initial stock for {$bp->nama_bahan}: {$initialStock} " . ($bp->satuan ? $bp->satuan->nama : 'Unit') . "\n";
            } else {
                echo "⚠️  {$bp->nama_bahan}: Initial stock calculated as {$initialStock} (skipped)\n";
            }
        } else {
            echo "✅ {$bp->nama_bahan}: Initial stock already exists\n";
        }
    }
    
    echo "\n✅ INITIAL STOCK FIX COMPLETED!\n";
    echo "🔍 Sekarang cek laporan kartu stok, stok awal seharusnya muncul.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}