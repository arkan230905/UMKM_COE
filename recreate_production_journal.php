<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RECREATING PRODUCTION JOURNAL ===\n\n";

// Get production record
$produksiId = 2;
$produksi = DB::table('produksis')->where('id', $produksiId)->first();

if (!$produksi) {
    echo "❌ Production ID {$produksiId} not found!\n";
    exit(1);
}

echo "Production Record:\n";
echo "  ID: {$produksi->id}\n";
echo "  Produk ID: {$produksi->produk_id}\n";
echo "  Tanggal: {$produksi->tanggal}\n";
echo "  Qty: {$produksi->qty_produksi}\n";
echo "  Total BBB: Rp " . number_format($produksi->total_bbb ?? 0, 0) . "\n";
echo "  Total BTKL: Rp " . number_format($produksi->total_btkl ?? 0, 0) . "\n";
echo "  Total BOP: Rp " . number_format($produksi->total_bop ?? 0, 0) . "\n";
echo "  Total Biaya: Rp " . number_format($produksi->total_biaya ?? 0, 0) . "\n";
echo "  Status: {$produksi->status}\n\n";

// Get product
$produk = DB::table('produks')->where('id', $produksi->produk_id)->first();
echo "Product: {$produk->nama_produk}\n\n";

echo "To recreate the journal, you need to:\n";
echo "1. Go to: /produksi (production page)\n";
echo "2. Find production ID {$produksiId} for product '{$produk->nama_produk}'\n";
echo "3. Click 'Edit' or 'Re-process' to regenerate the journal entries\n\n";

echo "OR you can trigger it programmatically by calling:\n";
echo "php artisan tinker\n";
echo ">>> \$produksi = App\\Models\\Produksi::find({$produksiId});\n";
echo ">>> \$controller = new App\\Http\\Controllers\\ProduksiController();\n";
echo ">>> // Call the method that creates journal entries\n\n";

echo "The new journal entries will use correct COAs:\n";
echo "  - 1171 (WIP BBB) instead of 2101\n";
echo "  - 1172 (WIP BTKL) instead of 2101\n";
echo "  - 1173 (WIP BOP) instead of 2101\n";
echo "  - 1161 (Pers. Barang Jadi Jasuke) for finished goods\n";
