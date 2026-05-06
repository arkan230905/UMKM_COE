<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$userId = 2;
$produkId = 3;

echo "=== Hapus Data Biaya Bahan Test ===\n\n";

$deleted = \App\Models\BiayaBahanBaku::where('user_id', $userId)
    ->where('produk_id', $produkId)
    ->delete();

echo "✓ Dihapus: {$deleted} record\n";
