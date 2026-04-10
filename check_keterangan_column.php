<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Checking stock_movements table structure...\n";
$columns = Schema::getColumnListing('stock_movements');
echo "Columns: " . implode(', ', $columns) . "\n";

$hasKeterangan = Schema::hasColumn('stock_movements', 'keterangan');
echo "Has keterangan column: " . ($hasKeterangan ? 'YES' : 'NO') . "\n";