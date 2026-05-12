<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== SISTEM RESET DATA UNTUK HOSTING ===\n\n";

try {
    // 1. Daftar semua tabel yang akan direset
    echo "=== DAFTAR TABEL YANG AKAN DIRESET ===\n";
    
    $tablesToReset = [
        // Tabel BTKL
        'proses_produksis',
        'bop_proses', 
        'btkls',
        'bom_job_btkl',
        'proses_bops',
        'bom_proses',
        'bom_proses_bops',
        
        // Tabel transaksi
        'produksis',
        'produksi_details',
        'pembelians',
        'pembelian_details',
        'penjualans',
        'detail_penjualans',
        'retur_penjualans',
        'detail_retur_penjualans',
        
        // Tabel stock
        'stocks',
        'stock_mutations',
        
        // Tabel lainnya
        'bahan_bakus',
        'bahan_pendukungs',
        'pegawais',
        'jabatans',
        'asets',
        'coas',
    ];
    
    $backupData = [];
    $totalRecords = 0;
    
    foreach ($tablesToReset as $table) {
        if (Schema::hasTable($table)) {
            $data = DB::table($table)->get();
            $count = $data->count();
            if ($count > 0) {
                $backupData[$table] = $data->toArray();
                $totalRecords += $count;
                echo "- {$table}: {$count} records\n";
            }
        }
    }
    
    echo "Total records yang akan direset: {$totalRecords}\n\n";

    // 2. Backup data sebelum reset
    echo "=== BACKUP DATA SEBELUM RESET ===\n";
    $backupFile = 'hosting_backup_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
    echo "Backup disimpan ke: {$backupFile}\n\n";

    // 3. Konfirmasi reset
    echo "=== KONFIRMASI RESET TOTAL ===\n";
    echo "⚠️  PERINGATAN: Semua data akan dihapus!\n";
    echo "⚠️  Ini termasuk semua data transaksi, stock, dan master data!\n";
    echo "⚠️  Backup sudah disimpan di: {$backupFile}\n\n";
    
    // 4. Proses reset
    echo "=== PROSES RESET DATA ===\n";
    
    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    $resetCount = 0;
    foreach ($tablesToReset as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            if ($count > 0) {
                DB::table($table)->truncate();
                echo "✓ {$table} direset ({$count} records)\n";
                $resetCount++;
            } else {
                echo "- {$table} sudah kosong\n";
            }
        }
    }
    
    // Enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n=== RESET AUTO INCREMENT ===\n";
    
    // Reset auto increment untuk semua tabel
    foreach ($tablesToReset as $table) {
        if (Schema::hasTable($table)) {
            try {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                echo "✓ {$table} auto increment direset\n";
            } catch (Exception $e) {
                echo "- {$table} gagal reset auto increment: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== VERIFIKASI RESET ===\n";
    
    $totalRemaining = 0;
    foreach ($tablesToReset as $table) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "{$table}: {$count} records\n";
            $totalRemaining += $count;
        }
    }
    
    echo "\nTotal records tersisa: {$totalRemaining}\n";
    
    // 5. Status akhir
    echo "\n=== STATUS RESET ===\n";
    if ($totalRemaining == 0) {
        echo "✅ RESET BERHASIL SEMUA!\n";
        echo "✅ Database siap untuk hosting!\n";
        echo "✅ Semua data kembali ke 0!\n";
        echo "✅ Auto increment direset ke 1!\n";
    } else {
        echo "⚠️  Reset tidak sempurna!\n";
        echo "⚠️  Masih ada {$totalRemaining} records tersisa!\n";
    }
    
    echo "\n=== INFORMASI BACKUP ===\n";
    echo "📁 File backup: {$backupFile}\n";
    echo "📊 Total records yang dibackup: {$totalRecords}\n";
    echo "📅 Waktu backup: " . date('Y-m-d H:i:s') . "\n";
    echo "\n=== SIAP UNTUK HOSTING ===\n";
    echo "🚀 Database sudah bersih dan siap untuk hosting besok!\n";
    echo "🔧 Gunakan file backup jika perlu restore data!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ Stack trace: " . $e->getTraceAsString() . "\n";
}
