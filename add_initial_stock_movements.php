<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MENAMBAHKAN INITIAL STOCK MOVEMENTS ===\n\n";

echo "🔍 MASALAH:\n";
echo "- Laporan stok tidak menampilkan stok awal\n";
echo "- Sistem membutuhkan entry 'initial_stock' di stock_movements\n";
echo "- Stok awal hanya ada di master table, tidak di movements\n\n";

echo "✅ SOLUSI:\n";
echo "- Buat entry initial_stock untuk setiap bahan baku\n";
echo "- Entry akan muncul di laporan sebagai stok awal\n\n";

// Data stok awal yang benar
$initialStocks = [
    1 => ['qty' => 50, 'price' => 32000, 'name' => 'Ayam Potong', 'unit' => 'KG'],
    2 => ['qty' => 40, 'price' => 48000, 'name' => 'Ayam Kampung', 'unit' => 'ekor'],
    3 => ['qty' => 50, 'price' => 50000, 'name' => 'Bebek', 'unit' => 'ekor'],
];

echo "📦 MENAMBAHKAN INITIAL STOCK MOVEMENTS:\n";

foreach ($initialStocks as $bahanBakuId => $data) {
    echo "Processing: {$data['name']} (ID: {$bahanBakuId})\n";
    
    // Cek apakah sudah ada initial_stock movement
    $existingInitial = \DB::table('stock_movements')
        ->where('item_type', 'material')
        ->where('item_id', $bahanBakuId)
        ->where('ref_type', 'initial_stock')
        ->first();
    
    if ($existingInitial) {
        echo "  ✅ Initial stock movement sudah ada (ID: {$existingInitial->id})\n";
        
        // Update jika berbeda
        $totalCost = $data['qty'] * $data['price'];
        if (abs($existingInitial->qty - $data['qty']) > 0.0001 || 
            abs($existingInitial->total_cost - $totalCost) > 0.01) {
            
            \DB::table('stock_movements')
                ->where('id', $existingInitial->id)
                ->update([
                    'qty' => $data['qty'],
                    'unit_cost' => $data['price'],
                    'total_cost' => $totalCost,
                    'updated_at' => now()
                ]);
            
            echo "  🔄 Updated: {$data['qty']} {$data['unit']} @ Rp{$data['price']}\n";
        }
    } else {
        // Buat entry initial_stock baru
        $totalCost = $data['qty'] * $data['price'];
        
        $movementId = \DB::table('stock_movements')->insertGetId([
            'item_type' => 'material',
            'item_id' => $bahanBakuId,
            'tanggal' => '2026-04-01', // Tanggal awal
            'ref_type' => 'initial_stock',
            'ref_id' => null,
            'direction' => 'in',
            'qty' => $data['qty'],
            'satuan' => $data['unit'],
            'unit_cost' => $data['price'],
            'total_cost' => $totalCost,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  ✅ Created initial stock movement (ID: {$movementId})\n";
        echo "  📊 {$data['qty']} {$data['unit']} @ Rp{$data['price']} = Rp" . number_format($totalCost) . "\n";
    }
    
    // Juga buat stock_layer jika belum ada
    $existingLayer = \DB::table('stock_layers')
        ->where('item_type', 'material')
        ->where('item_id', $bahanBakuId)
        ->where('ref_type', 'initial_stock')
        ->first();
    
    if (!$existingLayer) {
        $layerId = \DB::table('stock_layers')->insertGetId([
            'item_type' => 'material',
            'item_id' => $bahanBakuId,
            'tanggal' => '2026-04-01',
            'ref_type' => 'initial_stock',
            'ref_id' => null,
            'remaining_qty' => $data['qty'],
            'unit_cost' => $data['price'],
            'satuan' => $data['unit'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "  📋 Created stock layer (ID: {$layerId})\n";
    }
    
    echo "\n";
}

echo "=== VERIFIKASI HASIL ===\n";

// Cek hasil di database
foreach ($initialStocks as $bahanBakuId => $data) {
    $movements = \DB::table('stock_movements')
        ->where('item_type', 'material')
        ->where('item_id', $bahanBakuId)
        ->orderBy('tanggal', 'asc')
        ->get();
    
    echo "📦 {$data['name']}:\n";
    echo "  Total movements: " . $movements->count() . "\n";
    
    $initialMovement = $movements->where('ref_type', 'initial_stock')->first();
    if ($initialMovement) {
        echo "  ✅ Initial stock: {$initialMovement->qty} {$initialMovement->satuan}\n";
    } else {
        echo "  ❌ No initial stock movement found\n";
    }
    echo "\n";
}

echo "🎉 SELESAI!\n";
echo "Sekarang laporan stok akan menampilkan stok awal dengan benar.\n";
echo "Silakan refresh halaman laporan stok untuk melihat hasilnya.\n";