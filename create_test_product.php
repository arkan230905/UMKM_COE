<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATE TEST PRODUCT ===" . PHP_EOL;

// Buat produk baru untuk testing
$newProduk = \App\Models\Produk::create([
    'nama_produk' => 'Test Product Journal',
    'hpp' => 15000,
    'harga_pokok' => 15000,
    'harga_jual' => 25000,
    'satuan_id' => 1, // Asumsikan satuan dengan ID 1 ada
    'stok' => 100,
    'user_id' => 1,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Produk baru dibuat: " . $newProduk->nama_produk . PHP_EOL;
echo "HPP: " . $newProduk->hpp . PHP_EOL;
echo "ID: " . $newProduk->id . PHP_EOL;

// Test COA creation
$journalService = new \App\Services\JournalService();
$userId = 1;

echo PHP_EOL . "Testing COA creation..." . PHP_EOL;

$coaHppKode = $journalService->getOrCreateCoaHpp($newProduk, $userId);
echo "COA HPP Kode: " . $coaHppKode . PHP_EOL;

$coaPersediaanKode = $journalService->getOrCreateCoaPersediaan($newProduk, $userId);
echo "COA Persediaan Kode: " . $coaPersediaanKode . PHP_EOL;

// Cek COA yang dibuat
$coaHpp = \App\Models\Coa::where('kode_akun', $coaHppKode)->first();
$coaPersediaan = \App\Models\Coa::where('kode_akun', $coaPersediaanKode)->first();

echo PHP_EOL . "COA yang dibuat:" . PHP_EOL;
echo "- HPP: " . $coaHpp->nama_akun . " (Kode: " . $coaHpp->kode_akun . ")" . PHP_EOL;
echo "- Persediaan: " . $coaPersediaan->nama_akun . " (Kode: " . $coaPersediaan->kode_akun . ")" . PHP_EOL;

// Update produk dengan coa_persediaan_id
$newProduk->coa_persediaan_id = $coaPersediaan->id;
$newProduk->save();

echo PHP_EOL . "Produk updated dengan coa_persediaan_id: " . $coaPersediaan->id . PHP_EOL;
