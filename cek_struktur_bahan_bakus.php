<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK STRUKTUR TABEL BAHAN_BAKUS ===" . PHP_EOL;

// Cek struktur tabel
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('bahan_bakus');
echo "Kolom yang tersedia:" . PHP_EOL;
foreach ($columns as $column) {
    echo "- {$column}" . PHP_EOL;
}

echo PHP_EOL . "CEK CONTOH DATA:" . PHP_EOL;

// Cek contoh data yang ada
$sample = \Illuminate\Support\Facades\DB::table('bahan_bakus')->first();
if ($sample) {
    echo "Contoh data:" . PHP_EOL;
    foreach ($sample as $key => $value) {
        echo "- {$key}: '{$value}'" . PHP_EOL;
    }
} else {
    echo "Tidak ada data di tabel bahan_bakus" . PHP_EOL;
}
