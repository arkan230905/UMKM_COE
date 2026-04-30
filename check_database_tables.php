<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Database Tables for Migration Issues...\n\n";

// Check if bahan_pendukungs table exists
$hasBahanPendukungs = \Illuminate\Support\Facades\Schema::hasTable('bahan_pendukungs');
echo "Table 'bahan_pendukungs' exists: " . ($hasBahanPendukungs ? "YES" : "NO") . "\n";

// Check if bahan_bakus table exists
$hasBahanBakus = \Illuminate\Support\Facades\Schema::hasTable('bahan_bakus');
echo "Table 'bahan_bakus' exists: " . ($hasBahanBakus ? "YES" : "NO") . "\n";

// Check if purchase_returns table exists
$hasPurchaseReturns = \Illuminate\Support\Facades\Schema::hasTable('purchase_returns');
echo "Table 'purchase_returns' exists: " . ($hasPurchaseReturns ? "YES" : "NO") . "\n";

// Check if pembelian_details table exists
$hasPembelianDetails = \Illuminate\Support\Facades\Schema::hasTable('pembelian_details');
echo "Table 'pembelian_details' exists: " . ($hasPembelianDetails ? "YES" : "NO") . "\n";

// Check if purchase_return_items table exists
$hasPurchaseReturnItems = \Illuminate\Support\Facades\Schema::hasTable('purchase_return_items');
echo "Table 'purchase_return_items' exists: " . ($hasPurchaseReturnItems ? "YES" : "NO") . "\n";

echo "\n=== TABLE STRUCTURES ===\n";

if ($hasBahanPendukungs) {
    echo "\nbahan_pendukungs table structure:\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bahan_pendukungs');
    foreach ($columns as $column) {
        echo "  - {$column}\n";
    }
}

if ($hasBahanBakus) {
    echo "\nbahan_bakus table structure:\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bahan_bakus');
    foreach ($columns as $column) {
        echo "  - {$column}\n";
    }
}

echo "\n=== MIGRATION STATUS ===\n";

// Check migration status for problematic migration
try {
    $migrationStatus = \Illuminate\Support\Facades\DB::table('migrations')
        ->where('migration', '2025_11_19_040100_create_purchase_return_items_table')
        ->first();
    
    if ($migrationStatus) {
        echo "Migration status: " . $migrationStatus->batch . "\n";
    } else {
        echo "Migration not found in migrations table\n";
    }
} catch (Exception $e) {
    echo "Error checking migration status: " . $e->getMessage() . "\n";
}

echo "\nDatabase tables check completed!\n";
