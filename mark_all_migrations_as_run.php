<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Dapatkan semua file migrasi
$migrationFiles = File::glob(database_path('migrations/*.php'));
$migrations = [];

foreach ($migrationFiles as $file) {
    $fileName = basename($file);
    if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6}_.+)\.php$/', $fileName, $matches)) {
        $migrations[] = $matches[1];
    }
}

// Dapatkan migrasi yang sudah dijalankan
$executedMigrations = DB::table('migrations')->pluck('migration')->toArray();
$pendingMigrations = array_diff($migrations, $executedMigrations);

if (empty($pendingMigrations)) {
    echo "Tidak ada migrasi yang tertunda.\n";
} else {
    // Dapatkan batch terbaru
    $latestBatch = DB::table('migrations')->max('batch') ?: 0;
    $newBatch = $latestBatch + 1;
    
    // Tambahkan semua migrasi yang tertunda ke batch baru
    $data = array_map(function ($migration) use ($newBatch) {
        return [
            'migration' => $migration,
            'batch' => $newBatch
        ];
    }, $pendingMigrations);
    
    DB::table('migrations')->insert($data);
    
    echo "Berhasil menandai " . count($pendingMigrations) . " migrasi sebagai selesai.\n";
    echo "Daftar migrasi yang ditandai selesai:\n";
    foreach ($pendingMigrations as $migration) {
        echo "- $migration\n";
    }
}

echo "\nSekarang coba jalankan aplikasi Anda.\n";
echo "Jika ada masalah dengan struktur tabel, kita akan perbaiki secara manual.\n";
