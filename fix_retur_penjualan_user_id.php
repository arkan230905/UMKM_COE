<?php

/**
 * Script untuk fix user_id di tabel retur_penjualans
 * 
 * Cara pakai:
 * php fix_retur_penjualan_user_id.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Fix Retur Penjualan user_id ===\n\n";

// Step 1: Check if user_id column exists
echo "Step 1: Checking if user_id column exists...\n";
$columns = Schema::getColumnListing('retur_penjualans');

if (!in_array('user_id', $columns)) {
    echo "❌ Column user_id NOT FOUND!\n";
    echo "Please run: php artisan migrate\n";
    exit(1);
}

echo "✅ Column user_id exists\n\n";

// Step 2: Check for records without user_id
echo "Step 2: Checking for records without user_id...\n";
$nullUserIdCount = DB::table('retur_penjualans')
    ->whereNull('user_id')
    ->count();

echo "Found {$nullUserIdCount} records without user_id\n\n";

if ($nullUserIdCount === 0) {
    echo "✅ All records have user_id. No fix needed!\n";
    exit(0);
}

// Step 3: Update records based on related penjualan
echo "Step 3: Updating records based on related penjualan...\n";

try {
    $updated = DB::statement("
        UPDATE retur_penjualans rp
        JOIN penjualans p ON rp.penjualan_id = p.id
        SET rp.user_id = p.user_id
        WHERE rp.user_id IS NULL
    ");
    
    echo "✅ Updated records based on penjualan\n\n";
} catch (\Exception $e) {
    echo "❌ Error updating: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Check if there are still records without user_id
echo "Step 4: Checking for remaining records without user_id...\n";
$remainingNullCount = DB::table('retur_penjualans')
    ->whereNull('user_id')
    ->count();

if ($remainingNullCount > 0) {
    echo "⚠️  Found {$remainingNullCount} records still without user_id\n";
    echo "These records have invalid penjualan_id\n\n";
    
    // Show problematic records
    $problematicRecords = DB::select("
        SELECT rp.id, rp.nomor_retur, rp.penjualan_id
        FROM retur_penjualans rp
        LEFT JOIN penjualans p ON rp.penjualan_id = p.id
        WHERE rp.user_id IS NULL
    ");
    
    echo "Problematic records:\n";
    foreach ($problematicRecords as $record) {
        echo "  - ID: {$record->id}, Nomor: {$record->nomor_retur}, Penjualan ID: {$record->penjualan_id}\n";
    }
    
    echo "\nOptions:\n";
    echo "1. Delete these records (recommended if they're orphaned)\n";
    echo "2. Manually assign user_id\n\n";
    
    echo "Do you want to delete these records? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $answer = trim($line);
    
    if (strtolower($answer) === 'yes') {
        DB::table('retur_penjualans')
            ->whereNull('user_id')
            ->delete();
        echo "✅ Deleted {$remainingNullCount} problematic records\n";
    } else {
        echo "⚠️  Please manually fix these records\n";
        exit(1);
    }
} else {
    echo "✅ All records now have user_id\n";
}

// Step 5: Final verification
echo "\nStep 5: Final verification...\n";
$totalRecords = DB::table('retur_penjualans')->count();
$recordsWithUserId = DB::table('retur_penjualans')
    ->whereNotNull('user_id')
    ->count();

echo "Total records: {$totalRecords}\n";
echo "Records with user_id: {$recordsWithUserId}\n";

if ($totalRecords === $recordsWithUserId) {
    echo "\n✅ SUCCESS! All retur_penjualans records have user_id\n";
    echo "You can now access /transaksi/penjualan without errors\n";
} else {
    echo "\n❌ FAILED! Some records still don't have user_id\n";
    exit(1);
}
