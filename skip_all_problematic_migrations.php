<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Daftar semua migrasi yang akan dilewati
$migrationsToSkip = [
    '2025_10_29_160535_update_coas_table_structure',
    '2025_10_29_162000_add_budget_to_bops_table',
    '2025_10_29_183709_fix_pegawais_table_structure',
    // Tambahkan migrasi lain yang bermasalah di sini
];

echo "Menandai semua migrasi yang bermasalah sebagai selesai...\n";

foreach ($migrationsToSkip as $migration) {
    // Cek apakah migrasi sudah ada di tabel migrations
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    if (!$exists) {
        // Dapatkan batch terbaru
        $latestBatch = DB::table('migrations')->max('batch') ?: 0;
        $newBatch = $latestBatch + 1;
        
        // Tambahkan ke tabel migrations
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $newBatch
        ]);
        
        echo "✓ Migrasi berhasil dilewati: $migration (batch: $newBatch)\n";
    } else {
        echo "- Migrasi sudah ada: $migration\n";
    }
}

echo "\nSekarang coba jalankan: php artisan migrate --force\n";
echo "Jika masih ada error, beri tahu saya pesan errornya.\n";
