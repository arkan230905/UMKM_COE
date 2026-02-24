<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Detail Kolom item_type ===\n";

// Use raw SQL to get column details
$result = \DB::select("SHOW COLUMNS FROM stock_movements WHERE Field = 'item_type'");

foreach ($result as $row) {
    echo "Field: {$row->Field}\n";
    echo "Type: {$row->Type}\n";
    echo "Null: {$row->Null}\n";
    echo "Key: {$row->Key}\n";
    echo "Default: {$row->Default}\n";
    echo "Extra: {$row->Extra}\n";
}

// Test with shorter value
echo "\n=== Test Insert dengan 'bahan' ===\n";
try {
    \DB::table('stock_movements')->insert([
        'item_type' => 'bahan',
        'item_id' => 14,
        'tanggal' => '2026-01-31',
        'direction' => 'in',
        'qty' => 100,
        'satuan' => 'Liter',
        'unit_cost' => 1000,
        'total_cost' => 100000,
        'ref_type' => 'adjustment',
        'ref_id' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✅ Insert dengan 'bahan' berhasil\n";
    
    // Delete test record
    \DB::table('stock_movements')->where('item_type', 'bahan')->where('item_id', 14)->delete();
    echo "🗑️ Test record dihapus\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
