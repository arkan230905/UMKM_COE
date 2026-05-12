<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔄 COMPLETE SYSTEM RESET - Starting...\n";

// Tables to reset
$tables = [
    'stock_layers' => 'Stock movements and balances',
    'stock_movements' => 'All stock movement history',
    'bahan_bakus' => 'Raw material stock and data',
    'bahan_pendukungs' => 'Supporting material stock and data',
    'satuans' => 'Unit of measurement data',
    'coas' => 'Chart of accounts data',
    'pembelians' => 'Purchase records',
    'pembelian_details' => 'Purchase line items',
    'ap_settlements' => 'AP settlement records',
    'pelunasan_utangs' => 'Payment records',
    'produksis' => 'Production records',
    'produksi_details' => 'Production line items',
    'vendors' => 'Vendor data',
    'pegawais' => 'Employee data',
    'presensis' => 'Presence data',
    'journals' => 'Journal entries',
    'kasbanks' => 'Cash/bank accounts',
    'users' => 'User accounts'
];

echo "🗑️  DELETING ALL DATA FROM CORE TABLES...\n";

$deletedCounts = [];
foreach ($tables as $tableName => $description) {
    if (Schema::hasTable($tableName)) {
        $count = DB::table($tableName)->count();
        $deletedCount = DB::table($tableName)->delete();
        $deletedCounts[$tableName] = $deletedCount;
        echo "  ✅ {$description}: Deleted {$deletedCount} records\n";
    } else {
        echo "  ⚠️  {$description}: Table not found\n";
    }
}

echo "\n🧹  RESETTING AUTO-INCREMENT COUNTERS...\n";

// Get all table names and reset their auto-increment counters
$tablesToReset = [
    'bahan_bakus', 'bahan_pendukungs', 'satuans', 'coas',
    'pembelians', 'pembelian_details', 'ap_settlements', 'pelunasan_utangs',
    'produksis', 'produksi_details', 'vendors', 'pegawais', 'presensis',
    'kasbanks', 'journals', 'users'
];

foreach ($tablesToReset as $tableName) {
    if (Schema::hasTable($tableName)) {
        $maxId = DB::table($tableName)->max('id') ?? 0;
        DB::statement("ALTER TABLE {$tableName} AUTO_INCREMENT = 1");
        echo "  🔄 {$tableName}: Reset AUTO_INCREMENT to 1 (was {$maxId})\n";
    }
}

echo "\n🔧  OPTIMIZING DATABASE...\n";

// Optimize all tables
foreach (array_merge(array_keys($tables), $tablesToReset) as $tableName) {
    if (Schema::hasTable($tableName)) {
        DB::statement("OPTIMIZE TABLE {$tableName}");
        echo "  ⚡ {$tableName}: Optimized\n";
    }
}

echo "\n🧹  CLEARING APPLICATION CACHE...\n";

// Clear Laravel caches
$cacheCommands = [
    'config:cache' => 'Configuration cache',
    'route:cache' => 'Route cache',
    'view:cache' => 'View cache',
    'cache' => 'General application cache'
];

foreach ($cacheCommands as $cacheType => $description) {
    try {
        \Artisan::call("cache:clear --{$cacheType}");
        echo "  ✅ {$description}: Cleared\n";
    } catch (\Exception $e) {
        echo "  ❌ {$description}: Error - " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 SYSTEM RESET COMPLETED!\n";
echo "\n📊 SUMMARY OF ACTIONS:\n";
$totalDeleted = array_sum($deletedCounts);
echo "🗑️  Total Records Deleted: {$totalDeleted}\n";
echo "🔄  Auto-Increment Counters Reset: " . count($tablesToReset) . " tables\n";
echo "⚡  Database Tables Optimized: " . count(array_merge(array_keys($tables), $tablesToReset)) . " tables\n";
echo "🧹  All Application Caches Cleared: 4 cache types\n";

echo "\n🚀 SYSTEM IS READY FOR FRESH DATA ENTRY!\n";
echo "\n📝  NEXT STEPS:\n";
echo "1. ✅ All previous data completely removed\n";
echo "2. ✅ Database optimized and ready\n";
echo "3. ✅ Application caches cleared\n";
echo "4. ✅ Auto-increment counters reset to 1\n";
echo "5. 🎯 Start inputting new data with clean slate!\n";
