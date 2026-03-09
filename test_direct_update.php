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

echo "=== UPDATE TESTING ===\n";
echo "Before update:\n";
echo "Harga Jual: " . ($produk->harga_jual ?? 'NULL') . "\n";
echo "Margin: " . ($produk->margin_percent ?? 'NULL') . "\n";

// Update langsung tanpa events
\DB::table('produks')
    ->where('id', $produk->id)
    ->update([
        'harga_jual' => 10000,
        'margin_percent' => 37.55,
        'updated_at' => now()
    ]);

echo "\nAfter direct DB update:\n";
$produk->refresh();
echo "Harga Jual: " . ($produk->harga_jual ?? 'NULL') . "\n";
echo "Margin: " . ($produk->margin_percent ?? 'NULL') . "\n";
