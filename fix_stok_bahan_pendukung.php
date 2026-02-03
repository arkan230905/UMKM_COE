<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX STOK BAHAN PENDUKUNG ===\n\n";

// 1. Cek stock layers untuk bahan pendukung
echo "1. CEK STOCK LAYERS BAHAN PENDUKUNG:\n";
$stockLayers = \DB::table('stock_layers')
    ->where('item_type', 'support')
    ->get();

echo "Jumlah stock layers: " . $stockLayers->count() . "\n";
foreach ($stockLayers as $layer) {
    $item = \App\Models\BahanPendukung::find($layer->item_id);
    echo "- ID: {$layer->id}, Item: " . ($item ? $item->nama_bahan : 'Unknown') . ", Qty: {$layer->remaining_qty}, Satuan: {$layer->satuan}\n";
}

// 2. Tambah stock layers untuk Air Bersih jika belum ada
echo "\n2. TAMBAH STOCK LAYERS UNTUK AIR BERSIH:\n";
$airBersih = \App\Models\BahanPendukung::where('nama_bahan', 'Air Bersih')->first();
if ($airBersih) {
    $existingLayer = \DB::table('stock_layers')
        ->where('item_type', 'support')
        ->where('item_id', $airBersih->id)
        ->first();
    
    if (!$existingLayer) {
        echo "- Menambah stock layer untuk {$airBersih->nama_bahan}\n";
        
        // Tambah stock layer awal
        \DB::table('stock_layers')->insert([
            'item_type' => 'support',
            'item_id' => $airBersih->id,
            'satuan' => 'Liter',
            'tanggal' => now(),
            'unit_cost' => 10000, // Rp 10.000 per Liter
            'remaining_qty' => 10.0000,
            'ref_type' => 'initial',
            'ref_id' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "- Stock layer berhasil ditambah\n";
    } else {
        echo "- Stock layer sudah ada dengan qty: {$existingLayer->remaining_qty}\n";
    }
}

// 3. Test konsumsi
echo "\n3. TEST KONSUMSI BAHAN PENDUKUNG:\n";
$stock = app(\App\Services\StockService::class);

try {
    $qtyKonsumsi = 0.1; // 0.1 Liter
    echo "- Konsumsi {$qtyKonsumsi} Liter Air Bersih\n";
    
    $result = $stock->consume('support', $airBersih->id, $qtyKonsumsi, 'Liter', 'production', 999, now());
    echo "- Konsumsi berhasil: {$result}\n";
    
    // Update stok master
    $airBersih->fresh();
    echo "- Stok setelah: {$airBersih->stok} Liter\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SELESAI ===\n";
