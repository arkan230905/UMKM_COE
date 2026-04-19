<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CLEANING UP DUPLICATE COA ENTRIES ===\n\n";

// Show before stats
$beforeCount = DB::table('coas')->count();
$beforeUnique = DB::table('coas')->distinct('kode_akun')->count('kode_akun');
echo "BEFORE: Total records: {$beforeCount}, Unique kode_akun: {$beforeUnique}\n\n";

// Get all COA entries grouped by kode_akun
$duplicates = DB::table('coas')
    ->select('kode_akun', DB::raw('COUNT(*) as count'))
    ->groupBy('kode_akun')
    ->having('count', '>', 1)
    ->get();

if ($duplicates->isEmpty()) {
    echo "✅ No duplicate COA entries found.\n";
    exit;
}

echo "Found {$duplicates->count()} account codes with duplicates:\n\n";

// Disable foreign key checks temporarily
DB::statement('SET FOREIGN_KEY_CHECKS=0');

$totalDeleted = 0;

foreach ($duplicates as $duplicate) {
    $kodeAkun = $duplicate->kode_akun;
    $count = $duplicate->count;
    
    echo "- Kode Akun: {$kodeAkun} ({$count} duplicates)\n";
    
    // Get all records with this kode_akun, ordered by id (keep the first one)
    $records = DB::table('coas')
        ->where('kode_akun', $kodeAkun)
        ->orderBy('id')
        ->get();
    
    // Keep the first record, delete the rest
    $keepId = $records->first()->id;
    $deleteIds = $records->slice(1)->pluck('id');
    
    if ($deleteIds->count() > 0) {
        // Update foreign key references to point to the kept record
        updateForeignKeyReferences($deleteIds, $keepId);
        
        DB::table('coas')
            ->whereIn('id', $deleteIds)
            ->delete();
        
        $deletedCount = $deleteIds->count();
        $totalDeleted += $deletedCount;
        
        echo "  → Kept ID: {$keepId}, Deleted IDs: " . $deleteIds->implode(', ') . " ({$deletedCount} records)\n";
    }
}

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=1');

// Show after stats
$afterCount = DB::table('coas')->count();
$afterUnique = DB::table('coas')->distinct('kode_akun')->count('kode_akun');

echo "\n=== SUMMARY ===\n";
echo "Total duplicate entries deleted: {$totalDeleted}\n";
echo "AFTER: Total records: {$afterCount}, Unique kode_akun: {$afterUnique}\n";

if ($afterCount == $afterUnique) {
    echo "✅ Cleanup completed successfully - No duplicates remaining!\n";
} else {
    echo "⚠️  WARNING: Duplicates still exist ({$afterCount} records vs {$afterUnique} unique codes)\n";
}

/**
 * Update foreign key references to point to the kept record
 */
function updateForeignKeyReferences($deleteIds, $keepId)
{
    // Tables that reference coas
    $tables = [
        'pembayaran_beban' => ['akun_kas_id'],
        'pelunasan_utang' => ['akun_kas_id'],
        'jurnal_details' => ['coa_id'],
        'coa_period_balances' => ['coa_id'],
        'bops' => ['coa_id'],
    ];
    
    foreach ($tables as $table => $columns) {
        foreach ($columns as $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)
                    ->whereIn($column, $deleteIds)
                    ->update([$column => $keepId]);
            }
        }
    }
}
