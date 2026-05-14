<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get all tables
$tables = \Illuminate\Support\Facades\DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");

echo "=== DAFTAR TABEL DI DATABASE ===\n\n";
foreach ($tables as $table) {
    $tableName = $table->TABLE_NAME;
    $count = \Illuminate\Support\Facades\DB::table($tableName)->count();
    echo $tableName . ": " . $count . " records\n";
}
