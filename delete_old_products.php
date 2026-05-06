<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Produk;
use App\Models\BomDetail;
use App\Models\PenjualanDetail;
use App\Models\ProduksiDetail;
use Illuminate\Support\Facades\DB;

echo "=== Menghapus Semua Produk Lama ===\n\n";

// Get current user ID (user yang sedang login)
$userId = 2; // Sesuaikan dengan user_id Anda

echo "User ID: {$userId}\n";

// Get all products for this user
$produks = Produk::where('user_id', $userId)->get();

if ($produks->isEmpty()) {
    echo "✓ Tidak ada produk untuk dihapus.\n";
    exit(0);
}

echo "Ditemukan " . $produks->count() . " produk:\n\n";

DB::beginTransaction();

try {
    foreach ($produks as $produk) {
        echo "Menghapus: {$produk->nama_produk} (ID: {$produk->id})\n";
        
        // Hapus relasi terkait
        // 1. BOM Details
        $bomDetails = BomDetail::where('produk_id', $produk->id)->count();
        if ($bomDetails > 0) {
            BomDetail::where('produk_id', $produk->id)->delete();
            echo "  - Hapus {$bomDetails} BOM details\n";
        }
        
        // 2. Penjualan Details
        $penjualanDetails = PenjualanDetail::where('produk_id', $produk->id)->count();
        if ($penjualanDetails > 0) {
            echo "  ⚠ WARNING: Produk ini punya {$penjualanDetails} transaksi penjualan!\n";
            echo "  - Skip hapus (gunakan soft delete)\n";
            $produk->delete(); // Soft delete
            continue;
        }
        
        // 3. Produksi Details
        $produksiDetails = ProduksiDetail::where('produk_id', $produk->id)->count();
        if ($produksiDetails > 0) {
            echo "  ⚠ WARNING: Produk ini punya {$produksiDetails} transaksi produksi!\n";
            echo "  - Skip hapus (gunakan soft delete)\n";
            $produk->delete(); // Soft delete
            continue;
        }
        
        // 4. Harga Pokok Produksi (BBB, BTKL, BOP)
        DB::table('harga_pokok_produksi_biaya_bahan_baku')->where('user_id', $userId)->delete();
        DB::table('harga_pokok_produksi_btkl')->where('user_id', $userId)->delete();
        DB::table('harga_pokok_produksi_bop')->where('user_id', $userId)->delete();
        
        // 5. Stock Movements
        $stockMovements = DB::table('stock_movements')
            ->where('item_type', 'product')
            ->where('item_id', $produk->id)
            ->where('user_id', $userId)
            ->count();
        if ($stockMovements > 0) {
            DB::table('stock_movements')
                ->where('item_type', 'product')
                ->where('item_id', $produk->id)
                ->where('user_id', $userId)
                ->delete();
            echo "  - Hapus {$stockMovements} stock movements\n";
        }
        
        // Hapus produk (force delete jika tidak ada transaksi)
        $produk->forceDelete();
        echo "  ✓ Berhasil dihapus\n\n";
    }
    
    DB::commit();
    echo "\n✓ Selesai! Semua produk lama berhasil dihapus.\n";
    echo "Sekarang Anda bisa membuat produk baru yang fresh.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Rollback dilakukan, tidak ada data yang dihapus.\n";
}
