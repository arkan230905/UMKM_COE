<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== MENGOSONGKAN DATABASE UNTUK SIMULASI ===\n\n";

try {
    // 1. Backup data yang ada
    echo "=== BACKUP DATA YANG ADA ===\n";
    
    $tablesToBackup = [
        'proses_produksis',
        'bop_proses', 
        'btkls',
        'bom_job_btkl',
        'proses_bops',
        'bom_proses',
        'bom_proses_bops'
    ];
    
    $backupData = [];
    foreach ($tablesToBackup as $table) {
        if (Schema::hasTable($table)) {
            $data = DB::table($table)->get();
            if ($data->count() > 0) {
                $backupData[$table] = $data->toArray();
                echo "Backup {$table}: {$data->count()} records\n";
            }
        }
    }
    
    // Simpan backup ke file
    $backupFile = 'database_backup_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    echo "Backup disimpan ke: {$backupFile}\n\n";

    // 2. Konfirmasi penghapusan
    echo "=== KONFIRMASI PENGHAPUSAN ===\n";
    echo "Anda akan menghapus semua data dari tabel berikut:\n";
    foreach ($tablesToBackup as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "- {$table}: {$count} records\n";
        }
    }
    echo "\n";
    
    // 3. Kosongkan tabel
    echo "=== MENGOSONGKAN TABEL ===\n";
    
    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    foreach ($tablesToBackup as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            if ($count > 0) {
                DB::table($table)->truncate();
                echo "✓ {$table} dikosongkan ({$count} records)\n";
            } else {
                echo "- {$table} sudah kosong\n";
            }
        }
    }
    
    // Enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n=== RESET AUTO INCREMENT ===\n";
    
    // Reset auto increment ke 1
    foreach ($tablesToBackup as $table) {
        if (Schema::hasTable($table)) {
            try {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                echo "✓ {$table} auto increment direset ke 1\n";
            } catch (Exception $e) {
                echo "- {$table} gagal reset auto increment: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== VERIFIKASI HASIL ===\n";
    
    foreach ($tablesToBackup as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "{$table}: {$count} records\n";
        }
    }
    
    echo "\n=== DATABASE SUDAH KOSONG ===\n";
    echo "Siap untuk simulasi dari awal!\n";
    echo "Backup data tersimpan di: {$backupFile}\n";
    echo "\nUntuk restore data, jalankan script restore dengan file backup.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
