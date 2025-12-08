<?php

/**
 * Script untuk menjalankan semua migration penting
 * Akan skip migration yang error dan lanjut ke yang berikutnya
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Menjalankan Semua Migration Penting ===\n\n";

// Daftar migration penting yang harus dijalankan
$criticalMigrations = [
    '2025_10_30_000002_update_asets_table.php',
    '2025_11_10_121623_add_missing_columns_to_asets_table.php',
];

$successCount = 0;
$failCount = 0;
$skippedCount = 0;

foreach ($criticalMigrations as $migration) {
    $path = "database/migrations/$migration";
    
    if (!file_exists($path)) {
        echo "⚠ SKIP: $migration (file tidak ditemukan)\n";
        $skippedCount++;
        continue;
    }
    
    echo "Menjalankan: $migration ... ";
    
    try {
        Artisan::call('migrate', [
            '--path' => $path,
            '--force' => true,
        ]);
        
        $output = Artisan::output();
        
        if (strpos($output, 'DONE') !== false) {
            echo "✓ BERHASIL\n";
            $successCount++;
        } elseif (strpos($output, 'Nothing to migrate') !== false) {
            echo "⚠ SUDAH DIJALANKAN\n";
            $skippedCount++;
        } else {
            echo "⚠ SKIP (sudah ada)\n";
            $skippedCount++;
        }
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

echo "\n=== Menambahkan Kolom yang Hilang Secara Manual ===\n\n";

// Tambahkan kode_aset jika belum ada
if (!Schema::hasColumn('asets', 'kode_aset')) {
    echo "Menambahkan kolom kode_aset ... ";
    try {
        Schema::table('asets', function ($table) {
            $table->string('kode_aset')->nullable()->after('id');
        });
        
        // Generate kode untuk existing records
        $asets = DB::table('asets')->whereNull('kode_aset')->get();
        foreach ($asets as $index => $aset) {
            $kode = 'AST-' . date('Ym') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            DB::table('asets')->where('id', $aset->id)->update(['kode_aset' => $kode]);
        }
        
        // Make it unique
        Schema::table('asets', function ($table) {
            $table->string('kode_aset')->unique()->change();
        });
        
        echo "✓ BERHASIL\n";
        $successCount++;
    } catch (\Exception $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        $failCount++;
    }
} else {
    echo "✓ Kolom kode_aset sudah ada\n";
}

echo "\n=== Ringkasan ===\n";
echo "✓ Berhasil: $successCount\n";
echo "⚠ Dilewati: $skippedCount\n";
echo "✗ Gagal: $failCount\n";

echo "\n=== Verifikasi Struktur Tabel Asets ===\n";
$columns = Schema::getColumnListing('asets');
echo "Kolom yang ada: " . implode(', ', $columns) . "\n";

$requiredColumns = ['id', 'kode_aset', 'nama_aset', 'jenis_aset_id', 'kategori_aset_id'];
echo "\nKolom yang diperlukan:\n";
foreach ($requiredColumns as $col) {
    $status = in_array($col, $columns) ? '✓' : '✗';
    echo "  $status $col\n";
}

echo "\n✓ Selesai! Silakan refresh halaman browser Anda.\n";
