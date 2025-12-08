<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Memperbaiki Tabel Asets ===\n\n";

// Tambahkan jenis_aset_id jika belum ada
if (!Schema::hasColumn('asets', 'jenis_aset_id')) {
    echo "Menambahkan kolom jenis_aset_id ... ";
    try {
        Schema::table('asets', function ($table) {
            $table->unsignedBigInteger('jenis_aset_id')->nullable()->after('nama_aset');
        });
        echo "✓ BERHASIL\n";
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "✓ Kolom jenis_aset_id sudah ada\n";
}

// Verifikasi
echo "\n=== Verifikasi Final ===\n";
$columns = Schema::getColumnListing('asets');
$requiredColumns = ['id', 'kode_aset', 'nama_aset', 'jenis_aset_id', 'kategori_aset_id'];

echo "Kolom yang diperlukan:\n";
foreach ($requiredColumns as $col) {
    $status = in_array($col, $columns) ? '✓' : '✗';
    echo "  $status $col\n";
}

if (count(array_intersect($requiredColumns, $columns)) === count($requiredColumns)) {
    echo "\n✅ SEMUA KOLOM SUDAH LENGKAP!\n";
    echo "✅ Silakan refresh halaman browser Anda.\n";
} else {
    echo "\n⚠ Masih ada kolom yang hilang.\n";
}
