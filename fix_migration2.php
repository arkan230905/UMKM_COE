<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Daftar migrasi yang akan dilewati
$migrationsToSkip = [
    '2025_10_29_162000_add_budget_to_bops_table',
    '2025_10_29_183709_fix_pegawais_table_structure'
];

foreach ($migrationsToSkip as $migrationName) {
    // Cek apakah sudah ada di tabel migrations
    $exists = DB::table('migrations')
        ->where('migration', $migrationName)
        ->exists();

    if (!$exists) {
        // Dapatkan batch terbaru
        $latestBatch = DB::table('migrations')->max('batch');
        $newBatch = $latestBatch ? $latestBatch + 1 : 1;
        
        // Tambahkan ke tabel migrations
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $newBatch
        ]);
        
        echo "Berhasil melewatkan migrasi: $migrationName\n";
    } else {
        echo "Migrasi $migrationName sudah ada di tabel migrations\n";
    }
}

echo "\nSekarang coba jalankan: php artisan migrate --force\n";
echo "Jika masih ada error, kita akan perbaiki satu per satu.\n";
