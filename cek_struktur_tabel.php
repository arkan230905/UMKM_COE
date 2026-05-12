<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK STRUKTUR TABEL ===" . PHP_EOL;

// Cek struktur tabel bom_job_btkl
echo "Struktur tabel bom_job_btkl:" . PHP_EOL;
$structure = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_btkl');
foreach ($structure as $column) {
    echo "- {$column}" . PHP_EOL;
}

echo PHP_EOL;

// Cek struktur tabel btkls
echo "Struktur tabel btkls:" . PHP_EOL;
$structure = \Illuminate\Support\Facades\Schema::getColumnListing('btkls');
foreach ($structure as $column) {
    echo "- {$column}" . PHP_EOL;
}

echo PHP_EOL;

// Cek struktur tabel jabatans
echo "Struktur tabel jabatans:" . PHP_EOL;
$structure = \Illuminate\Support\Facades\Schema::getColumnListing('jabatans');
foreach ($structure as $column) {
    echo "- {$column}" . PHP_EOL;
}

echo PHP_EOL;

// Cek struktur tabel bom_job_bop
echo "Struktur tabel bom_job_bop:" . PHP_EOL;
$structure = \Illuminate\Support\Facades\Schema::getColumnListing('bom_job_bop');
foreach ($structure as $column) {
    echo "- {$column}" . PHP_EOL;
}
