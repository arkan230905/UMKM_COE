<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

echo "=== Menghapus Duplikat Stock Movements ===\n\n";

// Cari duplikat berdasarkan item_type, item_id, ref_type='initial_stock'
$duplicates = DB::select("
    SELECT item_type, item_id, user_id, COUNT(*) as count
    FROM stock_movements
    WHERE ref_type = 'initial_stock'
    GROUP BY item_type, item_id, user_id
    HAVING COUNT(*) > 1
");

if (empty($duplicates)) {
    echo "✓ Tidak ada duplikat ditemukan.\n";
    exit(0);
}

echo "Ditemukan " . count($duplicates) . " item dengan duplikat:\n\n";

foreach ($duplicates as $dup) {
    echo "- {$dup->item_type} ID {$dup->item_id} (user_id: {$dup->user_id}): {$dup->count} records\n";
    
    // Ambil semua record duplikat, urutkan by created_at ASC
    $records = StockMovement::where('item_type', $dup->item_type)
        ->where('item_id', $dup->item_id)
        ->where('user_id', $dup->user_id)
        ->where('ref_type', 'initial_stock')
        ->orderBy('created_at', 'asc')
        ->get();
    
    // Simpan yang pertama (paling lama), hapus sisanya
    $keep = $records->first();
    $toDelete = $records->slice(1);
    
    echo "  Simpan: ID {$keep->id} (created: {$keep->created_at})\n";
    
    foreach ($toDelete as $record) {
        echo "  Hapus: ID {$record->id} (created: {$record->created_at})\n";
        $record->delete();
    }
    
    echo "\n";
}

echo "\n✓ Selesai! Duplikat berhasil dihapus.\n";
