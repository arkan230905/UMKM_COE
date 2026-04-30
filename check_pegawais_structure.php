<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Pegawais table structure...\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('pegawais');
echo "Pegawais Table Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}

$hasUserId = \Illuminate\Support\Facades\Schema::hasColumn('pegawais', 'user_id');
echo "\nHas user_id column: " . ($hasUserId ? "YES" : "NO") . "\n";

// Check migration status
$migrationStatus = \Illuminate\Support\Facades\DB::table('migrations')
    ->where('migration', 'like', '%add_user_id_to_critical_tables%')
    ->first();

if ($migrationStatus) {
    echo "Migration status: " . $migrationStatus->migration . " (Batch " . $migrationStatus->batch . ")\n";
} else {
    echo "Migration not found in migrations table\n";
}

echo "\nPegawais structure check completed!\n";
