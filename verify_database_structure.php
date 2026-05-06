<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DATABASE STRUCTURE VERIFICATION ===\n\n";

// Required tables with their critical columns
$requiredTables = [
    // Core Authentication
    'users' => ['id', 'name', 'email', 'password'],
    'roles' => ['id', 'name'],
    'role_user' => ['user_id', 'role_id'],
    
    // Master Data
    'produks' => ['id', 'user_id', 'nama_produk', 'harga_jual', 'coa_persediaan_id'],
    'bahan_bakus' => ['id', 'user_id', 'nama_bahan', 'harga_satuan'],
    'bahan_pendukungs' => ['id', 'user_id', 'nama_bahan', 'harga_satuan'],
    'satuans' => ['id', 'user_id', 'nama', 'kode_satuan'],
    'coas' => ['id', 'user_id', 'kode_akun', 'nama_akun', 'tipe_akun', 'saldo_normal'],
    'vendors' => ['id', 'user_id', 'nama_vendor'],
    
    // Production Master Data
    'komponen_bops' => ['id', 'user_id', 'kode_komponen', 'nama_komponen', 'satuan', 'tarif_per_satuan', 'is_active'],
    'proses_produksis' => ['id', 'user_id', 'nama_proses', 'kapasitas_per_jam', 'tarif_btkl'],
    'bop_proses' => ['id', 'user_id', 'proses_produksi_id', 'total_bop_per_produk'],
    
    // Biaya Bahan
    'biaya_bahan_baku' => ['id', 'user_id', 'produk_id', 'bahan_baku_id', 'jumlah', 'satuan', 'harga_satuan', 'subtotal'],
    
    // Production Transactions
    'produksis' => ['id', 'user_id', 'produk_id', 'tanggal', 'qty_produksi', 'total_biaya', 'status'],
    'produksi_details' => ['id', 'produksi_id', 'item_type', 'item_id', 'qty_konversi', 'harga_satuan', 'subtotal'],
    
    // Accounting
    'jurnal_umum' => ['id', 'user_id', 'coa_id', 'tanggal', 'keterangan', 'debit', 'kredit', 'referensi', 'tipe_referensi'],
    'pembelians' => ['id', 'user_id', 'vendor_id', 'tanggal', 'total'],
    'penjualans' => ['id', 'user_id', 'tanggal', 'total', 'payment_method'],
    
    // HPP (Harga Pokok Produksi)
    'harga_pokok_produksi_biaya_bahan_baku' => ['id', 'user_id', 'biaya_bahan_baku_id'],
    'harga_pokok_produksi_btkl' => ['id', 'user_id', 'proses_produksi_id'],
    'harga_pokok_produksi_bop' => ['id', 'user_id', 'bop_proses_id'],
    
    // Stock Management
    'stock_movements' => ['id', 'user_id', 'item_type', 'item_id', 'movement_type', 'quantity', 'tanggal'],
    'stock_layers' => ['id', 'user_id', 'item_type', 'item_id', 'quantity', 'remaining_qty', 'unit_cost'],
];

$results = [
    'total' => count($requiredTables),
    'exists' => 0,
    'missing' => 0,
    'column_issues' => 0,
    'missing_tables' => [],
    'tables_with_issues' => [],
];

echo "Checking " . $results['total'] . " required tables...\n\n";

foreach ($requiredTables as $tableName => $requiredColumns) {
    $exists = Schema::hasTable($tableName);
    
    if ($exists) {
        $results['exists']++;
        echo "✅ {$tableName}\n";
        
        // Check columns
        $actualColumns = Schema::getColumnListing($tableName);
        $missingColumns = array_diff($requiredColumns, $actualColumns);
        
        if (!empty($missingColumns)) {
            $results['column_issues']++;
            $results['tables_with_issues'][] = $tableName;
            echo "   ⚠️  Missing columns: " . implode(', ', $missingColumns) . "\n";
        } else {
            echo "   ✓ All required columns present\n";
        }
    } else {
        $results['missing']++;
        $results['missing_tables'][] = $tableName;
        echo "❌ {$tableName} - TABLE MISSING!\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total tables checked: {$results['total']}\n";
echo "✅ Tables exist: {$results['exists']}\n";
echo "❌ Tables missing: {$results['missing']}\n";
echo "⚠️  Tables with column issues: {$results['column_issues']}\n";

if ($results['missing'] > 0) {
    echo "\n=== MISSING TABLES ===\n";
    foreach ($results['missing_tables'] as $table) {
        echo "- {$table}\n";
    }
    echo "\n⚠️  Run: php artisan migrate\n";
}

if ($results['column_issues'] > 0) {
    echo "\n=== TABLES WITH COLUMN ISSUES ===\n";
    foreach ($results['tables_with_issues'] as $table) {
        echo "- {$table}\n";
    }
    echo "\n⚠️  Some migrations may need to be re-run\n";
}

// Check multi-tenant columns
echo "\n=== MULTI-TENANT CHECK ===\n";
$multiTenantTables = [
    'produks', 'bahan_bakus', 'bahan_pendukungs', 'satuans', 'coas',
    'komponen_bops', 'proses_produksis', 'bop_proses',
    'biaya_bahan_baku', 'produksis', 'jurnal_umum', 'pembelians', 'penjualans',
    'harga_pokok_produksi_biaya_bahan_baku', 'harga_pokok_produksi_btkl', 'harga_pokok_produksi_bop',
    'stock_movements', 'stock_layers'
];

$missingUserId = [];
foreach ($multiTenantTables as $table) {
    if (Schema::hasTable($table)) {
        $columns = Schema::getColumnListing($table);
        if (!in_array('user_id', $columns)) {
            $missingUserId[] = $table;
        }
    }
}

if (empty($missingUserId)) {
    echo "✅ All multi-tenant tables have user_id column\n";
} else {
    echo "⚠️  Tables missing user_id column:\n";
    foreach ($missingUserId as $table) {
        echo "   - {$table}\n";
    }
}

// Check critical indexes
echo "\n=== INDEX CHECK ===\n";
$criticalIndexes = [
    'biaya_bahan_baku' => ['user_id', 'produk_id'],
    'jurnal_umum' => ['user_id', 'coa_id', 'tanggal'],
    'produksis' => ['user_id', 'produk_id'],
    'komponen_bops' => ['user_id'],
];

foreach ($criticalIndexes as $table => $indexColumns) {
    if (Schema::hasTable($table)) {
        echo "✓ {$table} exists (indexes should be checked manually)\n";
    }
}

// Check foreign keys
echo "\n=== FOREIGN KEY CHECK ===\n";
$foreignKeys = [
    'biaya_bahan_baku' => ['user_id' => 'users', 'produk_id' => 'produks', 'bahan_baku_id' => 'bahan_bakus'],
    'jurnal_umum' => ['user_id' => 'users', 'coa_id' => 'coas'],
    'produksis' => ['user_id' => 'users', 'produk_id' => 'produks'],
    'komponen_bops' => ['user_id' => 'users'],
];

echo "✓ Foreign keys should be verified manually in database\n";
echo "  Run: SHOW CREATE TABLE table_name;\n";

// Final status
echo "\n=== FINAL STATUS ===\n";
if ($results['missing'] == 0 && $results['column_issues'] == 0 && empty($missingUserId)) {
    echo "✅ DATABASE STRUCTURE IS CORRECT!\n";
    echo "✅ All required tables exist\n";
    echo "✅ All required columns present\n";
    echo "✅ Multi-tenant columns present\n";
    echo "\nYou can now:\n";
    echo "1. Run seeders: php artisan db:seed\n";
    echo "2. Start server: php artisan serve\n";
} else {
    echo "⚠️  DATABASE STRUCTURE HAS ISSUES!\n";
    echo "\nRecommended actions:\n";
    echo "1. Run: php artisan migrate\n";
    echo "2. Check migration files in database/migrations/\n";
    echo "3. Re-run this script to verify\n";
}

// Export table list
echo "\n=== ALL TABLES IN DATABASE ===\n";
$allTables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tableKey = "Tables_in_{$dbName}";

echo "Total tables: " . count($allTables) . "\n";
foreach ($allTables as $table) {
    echo "- {$table->$tableKey}\n";
}
