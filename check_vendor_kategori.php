<?php
/**
 * Script untuk mengecek dan mengupdate kategori vendor
 * Jalankan dengan: php check_vendor_kategori.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Vendor;

echo "=== DAFTAR VENDOR DAN KATEGORI ===\n\n";

$vendors = Vendor::all();

foreach ($vendors as $vendor) {
    echo "ID: {$vendor->id}\n";
    echo "Nama: {$vendor->nama_vendor}\n";
    echo "Kategori: " . ($vendor->kategori ?? 'NULL/KOSONG') . "\n";
    echo "---\n";
}

echo "\n=== UPDATE VENDOR PLN KE BAHAN PENDUKUNG ===\n";

// Update vendor PLN ke Bahan Pendukung
$pln = Vendor::where('nama_vendor', 'like', '%PLN%')->first();
if ($pln) {
    $pln->kategori = 'Bahan Pendukung';
    $pln->save();
    echo "Vendor PLN berhasil diupdate ke kategori 'Bahan Pendukung'\n";
} else {
    echo "Vendor PLN tidak ditemukan\n";
}

echo "\n=== DAFTAR VENDOR SETELAH UPDATE ===\n\n";

$vendors = Vendor::all();
foreach ($vendors as $vendor) {
    $badge = $vendor->kategori === 'Bahan Pendukung' ? '[BP]' : '[BB]';
    echo "{$badge} {$vendor->nama_vendor} - {$vendor->kategori}\n";
}

echo "\nSelesai!\n";
