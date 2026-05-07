<?php

/**
 * Script untuk menghapus jurnal produksi yang statusnya masih draft
 * Jurnal seharusnya hanya dibuat saat produksi selesai, bukan saat dibuat
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MENGHAPUS JURNAL PRODUKSI DRAFT ===\n\n";

// 1. Cari produksi yang masih draft tapi sudah ada jurnalnya
$draftProductions = DB::table('produksis')
    ->where('status', 'draft')
    ->get();

echo "Total produksi dengan status draft: " . $draftProductions->count() . "\n\n";

if ($draftProductions->isEmpty()) {
    echo "✅ Tidak ada produksi draft.\n";
    exit(0);
}

// 2. Untuk setiap produksi draft, cek apakah ada jurnal
$productionsWithJournals = [];
foreach ($draftProductions as $prod) {
    $journalCount = DB::table('jurnal_umum')
        ->where('referensi', $prod->id)
        ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
        ->count();
    
    if ($journalCount > 0) {
        $productionsWithJournals[] = [
            'produksi' => $prod,
            'journal_count' => $journalCount
        ];
    }
}

echo "Produksi draft yang memiliki jurnal: " . count($productionsWithJournals) . "\n\n";

if (empty($productionsWithJournals)) {
    echo "✅ Tidak ada jurnal yang perlu dihapus.\n";
    exit(0);
}

// 3. Tampilkan detail
echo "Detail produksi draft dengan jurnal:\n";
echo str_repeat("-", 120) . "\n";
printf("%-5s %-15s %-30s %-15s %-10s %-15s\n", 
    "ID", "Tanggal", "Produk", "Status", "Qty", "Jurnal Count");
echo str_repeat("-", 120) . "\n";

foreach ($productionsWithJournals as $item) {
    $prod = $item['produksi'];
    $produk = DB::table('produks')->where('id', $prod->produk_id)->first();
    
    printf("%-5s %-15s %-30s %-15s %-10s %-15s\n",
        $prod->id,
        $prod->tanggal,
        substr($produk->nama_produk ?? 'N/A', 0, 30),
        $prod->status,
        $prod->qty_produksi,
        $item['journal_count']
    );
}
echo str_repeat("-", 120) . "\n\n";

// 4. Konfirmasi
echo "⚠️  PERINGATAN: Script ini akan:\n";
echo "1. Menghapus JURNAL dari produksi yang masih draft\n";
echo "2. TIDAK menghapus data produksi (hanya jurnalnya)\n\n";

echo "Apakah Anda ingin melanjutkan? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) != 'y') {
    echo "Dibatalkan.\n";
    exit(0);
}
fclose($handle);

// 5. Hapus jurnal
echo "\nMenghapus jurnal produksi draft...\n";

$totalDeleted = 0;
foreach ($productionsWithJournals as $item) {
    $prod = $item['produksi'];
    
    try {
        $deleted = DB::table('jurnal_umum')
            ->where('referensi', $prod->id)
            ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
            ->delete();
        
        echo "✅ Deleted {$deleted} journal entries for Produksi ID {$prod->id}\n";
        $totalDeleted += $deleted;
    } catch (\Exception $e) {
        echo "❌ Failed to delete journals for Produksi ID {$prod->id}: {$e->getMessage()}\n";
    }
}

echo "\n=== SELESAI ===\n";
echo "Total jurnal dihapus: $totalDeleted\n";

// 6. Verifikasi
$remaining = 0;
foreach ($productionsWithJournals as $item) {
    $prod = $item['produksi'];
    $count = DB::table('jurnal_umum')
        ->where('referensi', $prod->id)
        ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
        ->count();
    $remaining += $count;
}

if ($remaining == 0) {
    echo "✅ Semua jurnal produksi draft berhasil dihapus!\n";
} else {
    echo "⚠️  Masih ada $remaining jurnal yang tersisa.\n";
}

echo "\n";
