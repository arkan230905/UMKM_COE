<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING BTKL TABLES ===\n";

$tables = DB::select("SHOW TABLES LIKE '%btkl%'");
echo "BTKL tables found: " . count($tables) . "\n";
foreach($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "- {$tableName}\n";
}

echo "\n=== CHECKING BOM TABLES ===\n";
$bomTables = DB::select("SHOW TABLES LIKE '%bom%'");
echo "BOM tables found: " . count($bomTables) . "\n";
foreach($bomTables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "- {$tableName}\n";
}