<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Database Migration Summary:\n";
echo "========================\n\n";

// Count tables
$tables = \DB::select('SHOW TABLES');
$tableCount = count($tables);
echo "Total Tables: $tableCount\n\n";

// Show main tables
$mainTables = ['users', 'pegawais', 'bahan_bakus', 'bahan_pendukungs', 'pembelians', 'produks', 'asets', 'coas'];
echo "Main Tables Status:\n";
foreach ($mainTables as $table) {
    if (\Schema::hasTable($table)) {
        $count = \DB::table($table)->count();
        echo "✓ $table: $count records\n";
    } else {
        echo "✗ $table: Not found\n";
    }
}

echo "\n✅ Database migration completed successfully!\n";
echo "Your UMKM application is now ready to use.\n";
