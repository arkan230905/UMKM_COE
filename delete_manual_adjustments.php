<?php

/**
 * Script untuk menghapus manual adjustment yang mengacau
 * Manual adjustment seharusnya tidak ada dalam operasi normal
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MENGHAPUS MANUAL ADJUSTMENTS ===\n\n";

// 1. Cek berapa banyak manual adjustment yang ada
$manualAdjustments = DB::table('stock_movements')
    ->where('ref_type', 'manual_adjustment')
    ->get();

echo "Total manual adjustments ditemukan: " . $manualAdjustments->count() . "\n\n";

if ($manualAdjustments->isEmpty()) {
    echo "✅ Tidak ada manual adjustment yang perlu dihapus.\n";
    exit(0);
}

// 2. Tampilkan detail manual adjustments
echo "Detail manual adjustments:\n";
echo str_repeat("-", 100) . "\n";
printf("%-5s %-15s %-10s %-12s %-10s %-15s %-30s\n", 
    "ID", "Item Type", "Item ID", "Direction", "Qty", "Tanggal", "Keterangan");
echo str_repeat("-", 100) . "\n";

foreach ($manualAdjustments as $adj) {
    printf("%-5s %-15s %-10s %-12s %-10s %-15s %-30s\n",
        $adj->id,
        $adj->item_type,
        $adj->item_id,
        $adj->direction,
        $adj->qty,
        $adj->tanggal,
        substr($adj->keterangan ?? '', 0, 30)
    );
}
echo str_repeat("-", 100) . "\n\n";

// 3. Konfirmasi penghapusan
echo "⚠️  PERINGATAN: Script ini akan menghapus SEMUA manual adjustment!\n";
echo "Manual adjustment adalah entry yang dibuat oleh legacy setter dan seharusnya tidak ada.\n\n";

// Untuk keamanan, kita akan menghapus satu per satu dengan log
echo "Menghapus manual adjustments...\n";

$deleted = 0;
foreach ($manualAdjustments as $adj) {
    try {
        DB::table('stock_movements')
            ->where('id', $adj->id)
            ->delete();
        
        echo "✅ Deleted ID {$adj->id}: {$adj->item_type} #{$adj->item_id} - {$adj->direction} {$adj->qty}\n";
        $deleted++;
    } catch (\Exception $e) {
        echo "❌ Failed to delete ID {$adj->id}: {$e->getMessage()}\n";
    }
}

echo "\n=== SELESAI ===\n";
echo "Total manual adjustments dihapus: $deleted\n";

// 4. Verifikasi
$remaining = DB::table('stock_movements')
    ->where('ref_type', 'manual_adjustment')
    ->count();

if ($remaining == 0) {
    echo "✅ Semua manual adjustments berhasil dihapus!\n";
} else {
    echo "⚠️  Masih ada $remaining manual adjustments yang tersisa.\n";
}

echo "\n";
