<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Nama file migrasi yang bermasalah
$migrationName = '2025_10_29_162000_add_budget_to_bops_table';

// Cek apakah sudah ada di tabel migrations
$exists = DB::table('migrations')
    ->where('migration', $migrationName)
    ->exists();

if (!$exists) {
    // Tambahkan ke tabel migrations
    DB::table('migrations')->insert([
        'migration' => $migrationName,
        'batch' => DB::table('migrations')->max('batch') + 1
    ]);
    
    echo "Berhasil melewatkan migrasi: $migrationName\n";
} else {
    echo "Migrasi $migrationName sudah ada di tabel migrations\n";
}

echo "Sekarang jalankan: php artisan migrate --force";
