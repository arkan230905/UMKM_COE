<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fix Jabatan Gudang Category ===" . PHP_EOL;

// Update all gudang-related jabatans to btkl
$result = DB::table('jabatans')
    ->where('nama', 'like', '%gudang%')
    ->update(['kategori' => 'btkl']);

echo "Updated " . $result . " jabatan gudang records" . PHP_EOL;

echo "✅ Kategori jabatan gudang diubah ke 'btkl'" . PHP_EOL;
