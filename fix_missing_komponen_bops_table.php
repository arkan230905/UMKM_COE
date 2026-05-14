<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== FIX MISSING komponen_bops TABLE ===\n\n";

// Step 1: Check if table exists
echo "Step 1: Checking if table exists...\n";
$tableExists = Schema::hasTable('komponen_bops');
echo "   Table exists: " . ($tableExists ? "YES" : "NO") . "\n\n";

// Step 2: Check migration records
echo "Step 2: Checking migration records...\n";
$migrationRecords = DB::table('migrations')
    ->where('migration', 'like', '%komponen_bops%')
    ->get();

if ($migrationRecords->count() > 0) {
    echo "   Found " . $migrationRecords->count() . " migration record(s):\n";
    foreach ($migrationRecords as $record) {
        echo "   - {$record->migration} (batch: {$record->batch})\n";
    }
} else {
    echo "   No migration records found\n";
}

// Step 3: If table doesn't exist but migration is recorded, delete the migration record
if (!$tableExists && $migrationRecords->count() > 0) {
    echo "\n❌ PROBLEM DETECTED: Migration recorded but table doesn't exist!\n";
    echo "\nStep 3: Removing migration records...\n";
    
    $deleted = DB::table('migrations')
        ->where('migration', 'like', '%komponen_bops%')
        ->delete();
    
    echo "   ✅ Deleted {$deleted} migration record(s)\n";
    
    echo "\nStep 4: Now run migration again:\n";
    echo "   php artisan migrate\n\n";
    
} elseif (!$tableExists) {
    echo "\n❌ Table doesn't exist and no migration records found\n";
    echo "   This is unusual. Creating table manually...\n\n";
    
    // Create table manually
    echo "Step 3: Creating table manually...\n";
    
    try {
        DB::statement("
            CREATE TABLE `komponen_bops` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` bigint(20) unsigned DEFAULT NULL,
              `kode_komponen` varchar(20) NOT NULL COMMENT 'Kode unik komponen (BOP-001)',
              `nama_komponen` varchar(100) NOT NULL COMMENT 'Nama komponen (Listrik, Gas, Penyusutan Mesin)',
              `satuan` varchar(20) NOT NULL COMMENT 'Satuan (kWh, m³, jam)',
              `tarif_per_satuan` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tarif per satuan',
              `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status aktif',
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `komponen_bops_kode_komponen_unique` (`kode_komponen`),
              KEY `komponen_bops_user_id_index` (`user_id`),
              CONSTRAINT `komponen_bops_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "   ✅ Table created successfully!\n\n";
        
        // Verify
        $tableExists = Schema::hasTable('komponen_bops');
        echo "Step 4: Verifying table exists...\n";
        echo "   Table exists: " . ($tableExists ? "✅ YES" : "❌ NO") . "\n\n";
        
        if ($tableExists) {
            echo "=== SUCCESS ===\n";
            echo "Table 'komponen_bops' has been created!\n";
            echo "You can now refresh your browser.\n";
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Error creating table: " . $e->getMessage() . "\n";
        echo "\nPlease create the table manually using MySQL:\n\n";
        echo "CREATE TABLE `komponen_bops` (\n";
        echo "  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n";
        echo "  `user_id` bigint(20) unsigned DEFAULT NULL,\n";
        echo "  `kode_komponen` varchar(20) NOT NULL,\n";
        echo "  `nama_komponen` varchar(100) NOT NULL,\n";
        echo "  `satuan` varchar(20) NOT NULL,\n";
        echo "  `tarif_per_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,\n";
        echo "  `is_active` tinyint(1) NOT NULL DEFAULT 1,\n";
        echo "  `created_at` timestamp NULL DEFAULT NULL,\n";
        echo "  `updated_at` timestamp NULL DEFAULT NULL,\n";
        echo "  PRIMARY KEY (`id`),\n";
        echo "  UNIQUE KEY `komponen_bops_kode_komponen_unique` (`kode_komponen`),\n";
        echo "  KEY `komponen_bops_user_id_index` (`user_id`),\n";
        echo "  CONSTRAINT `komponen_bops_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE\n";
        echo ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n";
    }
    
} else {
    echo "\n✅ Table exists! Everything is OK.\n";
}
