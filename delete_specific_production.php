<?php

/**
 * Script untuk menghapus 1 produksi tertentu beserta semua data terkait
 * (jurnal, detail, proses, stock movement)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MENGHAPUS PRODUKSI TERTENTU ===\n\n";

// 1. Tampilkan daftar produksi yang ada
echo "Daftar produksi yang tersedia:\n";
echo str_repeat("-", 120) . "\n";
printf("%-5s %-15s %-30s %-15s %-10s %-15s\n", 
    "ID", "Tanggal", "Produk", "Status", "Qty", "Total Biaya");
echo str_repeat("-", 120) . "\n";

$produksis = DB::table('produksis')
    ->orderBy('id', 'desc')
    ->limit(20)
    ->get();

foreach ($produksis as $prod) {
    $produk = DB::table('produks')->where('id', $prod->produk_id)->first();
    
    printf("%-5s %-15s %-30s %-15s %-10s %-15s\n",
        $prod->id,
        $prod->tanggal,
        substr($produk->nama_produk ?? 'N/A', 0, 30),
        $prod->status,
        $prod->qty_produksi,
        number_format($prod->total_biaya, 0, ',', '.')
    );
}
echo str_repeat("-", 120) . "\n\n";

// 2. Minta input ID produksi yang akan dihapus
echo "Masukkan ID produksi yang akan dihapus: ";
$handle = fopen("php://stdin", "r");
$produksiId = trim(fgets($handle));
fclose($handle);

if (empty($produksiId) || !is_numeric($produksiId)) {
    echo "❌ ID produksi tidak valid.\n";
    exit(1);
}

// 3. Cek apakah produksi ada
$produksi = DB::table('produksis')->where('id', $produksiId)->first();

if (!$produksi) {
    echo "❌ Produksi dengan ID $produksiId tidak ditemukan.\n";
    exit(1);
}

$produk = DB::table('produks')->where('id', $produksi->produk_id)->first();

// 4. Tampilkan detail produksi yang akan dihapus
echo "\n=== DETAIL PRODUKSI YANG AKAN DIHAPUS ===\n";
echo "ID Produksi    : {$produksi->id}\n";
echo "Tanggal        : {$produksi->tanggal}\n";
echo "Produk         : " . ($produk->nama_produk ?? 'N/A') . "\n";
echo "Status         : {$produksi->status}\n";
echo "Qty Produksi   : {$produksi->qty_produksi}\n";
echo "Total Biaya    : Rp " . number_format($produksi->total_biaya, 0, ',', '.') . "\n\n";

// 5. Hitung data terkait yang akan dihapus
$detailCount = DB::table('produksi_details')->where('produksi_id', $produksiId)->count();
$prosesCount = DB::table('produksi_proses')->where('produksi_id', $produksiId)->count();
$journalCount = DB::table('jurnal_umum')
    ->where('referensi', $produksiId)
    ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
    ->count();
$stockMovementCount = DB::table('stock_movements')
    ->where('ref_type', 'produksi')
    ->where('ref_id', $produksiId)
    ->count();

echo "Data terkait yang akan dihapus:\n";
echo "- Produksi Detail  : $detailCount\n";
echo "- Produksi Proses  : $prosesCount\n";
echo "- Jurnal Umum      : $journalCount\n";
echo "- Stock Movement   : $stockMovementCount\n\n";

// 6. Konfirmasi
echo "⚠️  PERINGATAN: Tindakan ini TIDAK DAPAT DIBATALKAN!\n";
echo "Apakah Anda yakin ingin menghapus produksi ini? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) != 'y') {
    echo "Dibatalkan.\n";
    exit(0);
}
fclose($handle);

// 7. Mulai penghapusan dalam transaction
echo "\nMemulai penghapusan...\n";

DB::beginTransaction();

try {
    // Hapus jurnal umum
    $deleted = DB::table('jurnal_umum')
        ->where('referensi', $produksiId)
        ->whereIn('tipe_referensi', ['produksi_bbb', 'produksi_btkl', 'produksi_bop', 'produksi_transfer'])
        ->delete();
    echo "✅ Deleted $deleted journal entries\n";
    
    // Hapus stock movements
    $deleted = DB::table('stock_movements')
        ->where('ref_type', 'produksi')
        ->where('ref_id', $produksiId)
        ->delete();
    echo "✅ Deleted $deleted stock movements\n";
    
    // Hapus produksi proses
    $deleted = DB::table('produksi_proses')
        ->where('produksi_id', $produksiId)
        ->delete();
    echo "✅ Deleted $deleted produksi proses\n";
    
    // Hapus produksi detail
    $deleted = DB::table('produksi_details')
        ->where('produksi_id', $produksiId)
        ->delete();
    echo "✅ Deleted $deleted produksi details\n";
    
    // Hapus produksi
    $deleted = DB::table('produksis')
        ->where('id', $produksiId)
        ->delete();
    echo "✅ Deleted produksi record\n";
    
    DB::commit();
    
    echo "\n=== SELESAI ===\n";
    echo "✅ Produksi ID $produksiId berhasil dihapus!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: {$e->getMessage()}\n";
    echo "Penghapusan dibatalkan (rollback).\n";
    exit(1);
}

echo "\n";
