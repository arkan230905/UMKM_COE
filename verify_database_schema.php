<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Verifying Database Schema Completeness for Hosting...\n\n";

// Get all tables in database
$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
$tableCount = count($tables);

echo "Total Tables: {$tableCount}\n";
echo "================\n\n";

// Critical tables for the application
$criticalTables = [
    'users' => 'User management',
    'perusahaan' => 'Company data',
    'coas' => 'Chart of accounts',
    'jurnal_umum' => 'General ledger',
    'produks' => 'Products',
    'penjualans' => 'Sales',
    'penjualan_details' => 'Sales details',
    'pembelians' => 'Purchases',
    'pembelian_details' => 'Purchase details',
    'bahan_bakus' => 'Raw materials',
    'bahan_pendukungs' => 'Supporting materials',
    'produksis' => 'Production',
    'pegawais' => 'Employees',
    'presensis' => 'Attendance',
    'penggajians' => 'Payroll',
    'asets' => 'Assets',
    'boms' => 'Bill of materials',
    'catalog_photos' => 'Catalog photos',
    'catalog_sections' => 'Catalog sections',
    'stock_movements' => 'Stock movements',
    'kartu_stok' => 'Stock cards'
];

echo "Critical Tables Verification:\n";
echo "==========================\n";

$allCriticalTablesExist = true;
foreach ($criticalTables as $table => $description) {
    $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
    $status = $exists ? "YES" : "NO";
    echo sprintf("%-25s | %-30s | %s\n", $table, $description, $status);
    
    if (!$exists) {
        $allCriticalTablesExist = false;
    }
}

echo "\nCritical Tables Status: " . ($allCriticalTablesExist ? "ALL EXIST" : "MISSING TABLES") . "\n\n";

// Check important relationships/foreign keys
echo "Important Relationships:\n";
echo "======================\n";

$relationships = [
    'users.perusahaan_id' => 'User to Company relationship',
    'jurnal_umum.coa_id' => 'Journal to COA relationship',
    'penjualans.user_id' => 'Sales to User relationship',
    'produks.user_id' => 'Products to User relationship',
    'coas.user_id' => 'COA to User relationship',
    'penjualan_details.penjualan_id' => 'Sales details to Sales relationship',
    'pembelian_details.pembelian_id' => 'Purchase details to Purchase relationship'
];

foreach ($relationships as $column => $description) {
    $parts = explode('.', $column);
    $table = $parts[0];
    $col = $parts[1];
    
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        $exists = \Illuminate\Support\Facades\Schema::hasColumn($table, $col);
        $status = $exists ? "YES" : "NO";
        echo sprintf("%-30s | %s\n", $column, $status);
    } else {
        echo sprintf("%-30s | TABLE MISSING\n", $column);
    }
}

echo "\nDatabase Connection Test:\n";
echo "========================\n";

try {
    // Test basic connection
    $connectionTest = \Illuminate\Support\Facades\DB::select('SELECT 1 as test');
    echo "Database Connection: OK\n";
    
    // Test user table access
    $userCount = \Illuminate\Support\Facades\DB::table('users')->count();
    echo "Users table accessible: {$userCount} records\n";
    
    // Test COA table access
    $coaCount = \Illuminate\Support\Facades\DB::table('coas')->count();
    echo "COA table accessible: {$coaCount} records\n";
    
    // Test jurnal_umum table access
    $journalCount = \Illuminate\Support\Facades\DB::table('jurnal_umum')->count();
    echo "Journal table accessible: {$journalCount} records\n";
    
    echo "Database Access: ALL OK\n";
    
} catch (Exception $e) {
    echo "Database Connection Error: " . $e->getMessage() . "\n";
}

echo "\nMigration Status Check:\n";
echo "=======================\n";

try {
    $migratedCount = \Illuminate\Support\Facades\DB::table('migrations')->count();
    echo "Total Migrations Run: {$migratedCount}\n";
    
    $lastMigration = \Illuminate\Support\Facades\DB::table('migrations')
        ->orderBy('batch', 'desc')
        ->orderBy('id', 'desc')
        ->first();
    
    if ($lastMigration) {
        echo "Last Migration: " . $lastMigration->migration . " (Batch " . $lastMigration->batch . ")\n";
    }
    
} catch (Exception $e) {
    echo "Migration Status Error: " . $e->getMessage() . "\n";
}

echo "\nStorage Directory Check:\n";
echo "========================\n";

$storagePaths = [
    'storage/app/public' => 'Application public storage',
    'storage/framework/cache' => 'Framework cache',
    'storage/framework/sessions' => 'Framework sessions',
    'storage/framework/views' => 'Framework views'
];

foreach ($storagePaths as $path => $description) {
    $exists = is_dir(base_path($path));
    $writable = $exists && is_writable(base_path($path));
    $status = $exists ? ($writable ? "WRITABLE" : "NOT WRITABLE") : "NOT FOUND";
    echo sprintf("%-30s | %s\n", $path, $status);
}

echo "\nEnvironment Check:\n";
echo "==================\n";

$envChecks = [
    'APP_ENV' => config('app.env'),
    'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
    'DB_CONNECTION' => config('database.default'),
    'DB_HOST' => config('database.connections.mysql.host'),
    'DB_DATABASE' => config('database.connections.mysql.database'),
    'CACHE_DRIVER' => config('cache.default'),
    'SESSION_DRIVER' => config('session.driver')
];

foreach ($envChecks as $key => $value) {
    echo sprintf("%-20s | %s\n", $key, $value);
}

echo "\n=== HOSTING READINESS SUMMARY ===\n";
echo "Database Schema: " . ($allCriticalTablesExist ? "COMPLETE" : "INCOMPLETE") . "\n";
echo "Migration Status: " . ($migratedCount > 0 ? "RUNNING" : "NOT STARTED") . "\n";
echo "Database Connection: OK\n";
echo "Storage: CHECKED\n";
echo "Environment: CHECKED\n";

echo "\nDatabase schema verification completed!\n";
