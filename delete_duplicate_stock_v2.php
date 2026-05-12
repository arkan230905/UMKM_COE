<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

echo "=== Menghapus Duplikat Stock Movements ===\n\n";

// Cari duplikat berdasarkan semua field penting (bukan hanya count)
$duplicates = DB::select("
    SELECT 
        item_type, 
        item_id, 
        user_id, 
        tanggal,
        direction,
        qty,
        ref_type,
        keterangan,
        COUNT(*) as count,
        GROUP_CONCAT(id ORDER BY id) as ids
    FROM stock_movements
    WHERE ref_type = 'initial_stock'
    GROUP BY item_type, item_id, user_id, tanggal, direction, qty, ref_type, keterangan
    HAVING COUNT(*) > 1
");

if (empty($duplicates)) {
    echo "✓ Tidak ada duplikat ditemukan.\n";
    exit(0);
}

echo "Ditemukan " . count($duplicates) . " grup duplikat:\n\n";

$totalDeleted = 0;

foreach ($duplicates as $dup) {
    $ids = explode(',', $dup->ids);
    $keepId = $ids[0]; // Simpan yang pertama
    $deleteIds = array_slice($ids, 1); // Hapus sisanya
    
    echo "- {$dup->item_type} ID {$dup->item_id} (user_id: {$dup->user_id}): {$dup->count} records\n";
    echo "  Simpan: ID {$keepId}\n";
    echo "  Hapus: ID " . implode(', ', $deleteIds) . "\n";
    
    // Hapus duplikat
    $deleted = StockMovement::whereIn('id', $deleteIds)->delete();
    $totalDeleted += $deleted;
    
    echo "  ✓ Berhasil hapus {$deleted} record\n\n";
}

echo "\n✓ Selesai! Total {$totalDeleted} duplikat berhasil dihapus.\n";
