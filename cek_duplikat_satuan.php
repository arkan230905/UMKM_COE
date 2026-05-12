<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CEK DUPLIKAT SATUAN ===" . PHP_EOL;

// Ambil semua satuan
$satuans = \App\Models\Satuan::orderBy('nama')->get();

echo "Total satuan: " . $satuans->count() . PHP_EOL . PHP_EOL;

// Kelompokkan berdasarkan nama (case-insensitive)
$groups = [];
foreach ($satuans as $satuan) {
    $namaLower = strtolower($satuan->nama);
    if (!isset($groups[$namaLower])) {
        $groups[$namaLower] = [];
    }
    $groups[$namaLower][] = $satuan;
}

// Cari duplikat
$duplikats = [];
foreach ($groups as $nama => $items) {
    if (count($items) > 1) {
        $duplikats[$nama] = $items;
    }
}

echo "DUPLIKAT YANG DITEMUKAN:" . PHP_EOL;
echo "========================" . PHP_EOL;

foreach ($duplikats as $nama => $items) {
    echo PHP_EOL . "Nama: " . ucwords($nama) . PHP_EOL;
    foreach ($items as $item) {
        echo "  ID: {$item->id}, Kode: {$item->kode}, Nama: {$item->nama}" . PHP_EOL;
    }
}

echo PHP_EOL . "TOTAL DUPLIKAT: " . count($duplikats) . PHP_EOL;

// Cek satuan yang digunakan di bahan baku
echo PHP_EOL . "CEK PENGGUNAAN DI BAHAN BAKU:" . PHP_EOL;
echo "=================================" . PHP_EOL;

$usedSatuanIds = \App\Models\BahanBaku::pluck('satuan_id')->unique()->toArray();
echo "Jumlah satuan yang digunakan di bahan baku: " . count($usedSatuanIds) . PHP_EOL;

foreach ($duplikats as $nama => $items) {
    echo PHP_EOL . ucwords($nama) . ":" . PHP_EOL;
    foreach ($items as $item) {
        $isUsed = in_array($item->id, $usedSatuanIds);
        $status = $isUsed ? "DIGUNAKAN" : "TIDAK DIGUNAKAN";
        echo "  ID {$item->id} ({$item->kode}): {$status}" . PHP_EOL;
    }
}

echo PHP_EOL . "✅ Selesai!" . PHP_EOL;
