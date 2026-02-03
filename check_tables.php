<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK TABLES WITH BOP ===\n";
$tables = \Illuminate\Support\Facades\Schema::getTableListing();
foreach ($tables as $table) {
    if (strpos($table, 'bop') !== false) {
        echo "- " . $table . "\n";
    }
}

echo "\n=== CHECK TABLES WITH COA ===\n";
foreach ($tables as $table) {
    if (strpos($table, 'coa') !== false) {
        echo "- " . $table . "\n";
    }
}
