<?php
/**
 * Script untuk update data BOP Proses lama yang belum punya produk_id, periode, jumlah_produksi
 * 
 * Jalankan dengan: php update-old-bop-data.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BopProses;
use App\Models\Produk;

echo "===========================================\n";
echo "Update Old BOP Proses Data\n";
echo "===========================================\n\n";

// Get all BOP Proses that don't have produk_id or periode
$oldBopProses = BopProses::whereNull('produk_id')
    ->orWhereNull('periode')
    ->orWhereNull('jumlah_produksi')
    ->get();

if ($oldBopProses->count() === 0) {
    echo "✅ Tidak ada data BOP Proses lama yang perlu diupdate.\n";
    echo "   Semua data sudah memiliki produk_id, periode, dan jumlah_produksi.\n\n";
    exit(0);
}

echo "Ditemukan {$oldBopProses->count()} data BOP Proses lama yang perlu diupdate.\n\n";

foreach ($oldBopProses as $index => $bop) {
    echo "Processing BOP #{$bop->id}: {$bop->nama_bop_proses}...\n";
    
    // Get first produk for this user (atau bisa customize sesuai kebutuhan)
    $produk = Produk::where('user_id', $bop->user_id)->first();
    
    if (!$produk) {
        echo "   ⚠️  SKIP: Tidak ada produk untuk user_id {$bop->user_id}\n\n";
        continue;
    }
    
    // Set default values
    $updates = [];
    
    if (!$bop->produk_id) {
        $updates['produk_id'] = $produk->id;
        echo "   → Set produk_id: {$produk->id} ({$produk->nama_produk})\n";
    }
    
    if (!$bop->periode) {
        $updates['periode'] = now()->format('Y-m'); // Current month
        echo "   → Set periode: " . now()->format('Y-m') . "\n";
    }
    
    if (!$bop->jumlah_produksi || $bop->jumlah_produksi == 0) {
        // Use jumlah_produksi_perbulan if available, otherwise default to 1000
        $jumlahProduksi = $bop->jumlah_produksi_perbulan ?? 1000;
        $updates['jumlah_produksi'] = $jumlahProduksi;
        echo "   → Set jumlah_produksi: {$jumlahProduksi}\n";
    }
    
    if (!empty($updates)) {
        $bop->update($updates);
        echo "   ✅ Updated successfully!\n";
    } else {
        echo "   ℹ️  No updates needed\n";
    }
    
    echo "\n";
}

echo "===========================================\n";
echo "✅ Selesai! Semua data BOP Proses sudah diupdate.\n";
echo "===========================================\n";
