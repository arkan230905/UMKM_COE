<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Cari produk opak
$produk = \App\Models\Produk::where('nama_produk', 'like', '%opak%')->first();

if (!$produk) {
    echo "Produk opak tidak ditemukan\n";
    exit;
}

echo "=== CEK DATABASE VALUES ===\n";
echo "Produk ID: " . $produk->id . "\n";
echo "Nama Produk: " . $produk->nama_produk . "\n";
echo "Harga Jual (DB): " . ($produk->harga_jual ?? 'NULL') . "\n";
echo "Margin Percent (DB): " . ($produk->margin_percent ?? 'NULL') . "\n";
echo "HPP (stored): " . ($produk->hpp ?? 'NULL') . "\n";
echo "Harga BOM: " . ($produk->harga_bom ?? 'NULL') . "\n";
echo "Updated At: " . $produk->updated_at . "\n";

// Refresh data dari database
$produk->refresh();
echo "\n=== AFTER REFRESH ===\n";
echo "Harga Jual (DB): " . ($produk->harga_jual ?? 'NULL') . "\n";
echo "Margin Percent (DB): " . ($produk->margin_percent ?? 'NULL') . "\n";
