<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST NEW PRODUCT JOURNAL CREATION ===" . PHP_EOL;

// Cari produk yang belum memiliki COA HPP
$produk = \App\Models\Produk::where('hpp', 0)
    ->where('nama_produk', '!=', 'Jasuke')
    ->first();

if (!$produk) {
    echo "Tidak ada produk baru untuk testing" . PHP_EOL;
    exit;
}

echo "Testing dengan produk: " . $produk->nama_produk . PHP_EOL;
echo "HPP saat ini: " . $produk->hpp . PHP_EOL;
echo "coa_persediaan_id: " . ($produk->coa_persediaan_id ?? 'NULL') . PHP_EOL;
echo PHP_EOL;

// Update HPP produk untuk testing
$produk->hpp = 10000; // Rp 10,000 per unit
$produk->save();
echo "HPP diupdate ke: " . $produk->hpp . PHP_EOL;
echo PHP_EOL;

// Test COA creation logic
$journalService = new \App\Services\JournalService();
$userId = 1;

// Test getOrCreateCoaHpp
echo "Testing getOrCreateCoaHpp..." . PHP_EOL;
$coaHppKode = $journalService->getOrCreateCoaHpp($produk, $userId);
echo "COA HPP Kode: " . $coaHppKode . PHP_EOL;

// Test getOrCreateCoaPersediaan  
echo "Testing getOrCreateCoaPersediaan..." . PHP_EOL;
$coaPersediaanKode = $journalService->getOrCreateCoaPersediaan($produk, $userId);
echo "COA Persediaan Kode: " . $coaPersediaanKode . PHP_EOL;

echo PHP_EOL . "COA berhasil dibuat/ditemukan!" . PHP_EOL;

// Cek COA yang dibuat
$coaHpp = \App\Models\Coa::where('kode_akun', $coaHppKode)->first();
$coaPersediaan = \App\Models\Coa::where('kode_akun', $coaPersediaanKode)->first();

echo "COA HPP: " . $coaHpp->nama_akun . " (ID: " . $coaHpp->id . ")" . PHP_EOL;
echo "COA Persediaan: " . $coaPersediaan->nama_akun . " (ID: " . $coaPersediaan->id . ")" . PHP_EOL;
