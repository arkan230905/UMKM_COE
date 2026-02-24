<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Struktur Tabel Pembelian ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('pembelians');
echo "Kolom di tabel pembelians:\n";
foreach ($columns as $col) {
    echo "- $col\n";
}

echo "\n=== Struktur Tabel Pembelian Details ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('pembelian_details');
echo "Kolom di tabel pembelian_details:\n";
foreach ($columns as $col) {
    echo "- $col\n";
}

echo "\n=== Struktur Tabel Production ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('productions');
echo "Kolom di tabel productions:\n";
foreach ($columns as $col) {
    echo "- $col\n";
}

echo "\n=== Struktur Tabel Production Details ===\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('production_details');
echo "Kolom di tabel production_details:\n";
foreach ($columns as $col) {
    echo "- $col\n";
}
