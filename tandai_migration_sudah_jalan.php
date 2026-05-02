<?php
/**
 * TANDAI SEMUA MIGRATION SEBAGAI SUDAH JALAN
 * ===========================================
 * Script ini untuk teman Anda yang sudah import database
 * tapi Laravel masih minta migrate
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==============================================\n";
echo "TANDAI MIGRATION SEBAGAI SUDAH JALAN\n";
echo "==============================================\n\n";

// Cek apakah tabel migrations ada
try {
    $migrationsExist = DB::select("SHOW TABLES LIKE 'migrations'");
    
    if (empty($migrationsExist)) {
        echo "❌ Tabel 'migrations' tidak ada!\n";
        echo "   Buat dulu dengan: php artisan migrate:install\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Ambil semua file migration
$migrationPath = __DIR__ . '/database/migrations';
$migrationFiles = glob($migrationPath . '/*.php');

if (empty($migrationFiles)) {
    echo "❌ Tidak ada file migration ditemukan!\n\n";
    exit(1);
}

echo "Ditemukan " . count($migrationFiles) . " file migration\n\n";

$inserted = 0;
$skipped = 0;
$batch = 1;

foreach ($migrationFiles as $file) {
    $filename = basename($file, '.php');
    
    // Cek apakah migration sudah ada di database
    $exists = DB::table('migrations')
        ->where('migration', $filename)
        ->exists();
    
    if ($exists) {
        echo "⚪ {$filename}: Sudah ada\n";
        $skipped++;
    } else {
        // Insert ke tabel migrations
        DB::table('migrations')->insert([
            'migration' => $filename,
            'batch' => $batch
        ]);
        echo "✅ {$filename}: Ditandai sebagai sudah jalan\n";
        $inserted++;
    }
}

echo "\n";
echo "==============================================\n";
echo "RINGKASAN\n";
echo "==============================================\n\n";
echo "Total migration: " . count($migrationFiles) . "\n";
echo "✅ Ditandai sebagai sudah jalan: {$inserted}\n";
echo "⚪ Sudah ada sebelumnya: {$skipped}\n\n";

if ($inserted > 0) {
    echo "🎉 Berhasil! Sekarang jalankan:\n";
    echo "   php artisan migrate:status\n\n";
    echo "   Semua migration harus status 'Ran'\n\n";
} else {
    echo "✅ Semua migration sudah ditandai sebelumnya.\n\n";
}

echo "==============================================\n";
echo "SELESAI\n";
echo "==============================================\n";
