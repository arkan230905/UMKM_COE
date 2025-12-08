<?php

/**
 * Script untuk mengecek dan memperbaiki semua tabel
 * Menjalankan php artisan migrate --force dengan skip error
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  SISTEM CEK & PERBAIKAN OTOMATIS DATABASE                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Jalankan migrate dengan force (skip yang error)
echo "📦 Menjalankan semua migration yang belum dijalankan...\n";
echo str_repeat("-", 60) . "\n";

try {
    // Capture output
    ob_start();
    Artisan::call('migrate', ['--force' => true]);
    $output = Artisan::output();
    ob_end_clean();
    
    // Parse output
    if (strpos($output, 'Nothing to migrate') !== false) {
        echo "✓ Semua migration sudah dijalankan\n";
    } else {
        echo $output;
    }
} catch (\Exception $e) {
    echo "⚠ Ada migration yang error (akan dilanjutkan): " . $e->getMessage() . "\n";
}

echo "\n";
echo str_repeat("=", 60) . "\n";
echo "VERIFIKASI TABEL PENTING\n";
echo str_repeat("=", 60) . "\n\n";

// Daftar tabel dan kolom penting yang harus ada
$tableChecks = [
    'users' => ['id', 'name', 'email', 'password', 'role', 'perusahaan_id'],
    'perusahaan' => ['id', 'nama', 'alamat', 'email', 'telepon', 'kode'],
    'asets' => ['id', 'kode_aset', 'nama_aset', 'jenis_aset_id', 'kategori_aset_id'],
    'jenis_asets' => ['id', 'nama'],
    'kategori_asets' => ['id', 'jenis_aset_id', 'nama'],
    'pegawais' => ['id', 'nama', 'email'],
    'produks' => ['id', 'nama_produk', 'harga_jual'],
    'vendors' => ['id', 'nama_vendor'],
    'bahan_bakus' => ['id', 'nama_bahan'],
    'coas' => ['id', 'kode_akun', 'nama_akun'],
];

$allGood = true;

foreach ($tableChecks as $table => $requiredColumns) {
    echo "📋 Tabel: $table\n";
    
    // Cek apakah tabel ada
    if (!Schema::hasTable($table)) {
        echo "   ✗ Tabel tidak ada!\n";
        $allGood = false;
        continue;
    }
    
    // Cek kolom
    $existingColumns = Schema::getColumnListing($table);
    $missingColumns = array_diff($requiredColumns, $existingColumns);
    
    if (empty($missingColumns)) {
        echo "   ✓ Semua kolom lengkap\n";
    } else {
        echo "   ⚠ Kolom yang hilang: " . implode(', ', $missingColumns) . "\n";
        $allGood = false;
    }
    
    // Hitung jumlah record
    try {
        $count = DB::table($table)->count();
        echo "   📊 Jumlah data: $count record\n";
    } catch (\Exception $e) {
        echo "   ⚠ Error menghitung data\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "RINGKASAN\n";
echo str_repeat("=", 60) . "\n\n";

if ($allGood) {
    echo "✅ SEMUA TABEL SUDAH LENGKAP DAN SIAP DIGUNAKAN!\n\n";
    echo "🎉 Sistem Anda sudah siap 100%!\n";
    echo "🚀 Silakan refresh halaman browser dan mulai gunakan sistem.\n";
} else {
    echo "⚠ Ada beberapa tabel yang perlu diperbaiki.\n";
    echo "💡 Jalankan: php artisan migrate --force\n";
    echo "💡 Atau hubungi developer untuk bantuan.\n";
}

echo "\n";
echo "📝 Log disimpan di: storage/logs/laravel.log\n";
echo "\n";
