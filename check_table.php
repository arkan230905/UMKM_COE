<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Stock Movements table columns:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('stock_movements');
foreach ($columns as $col) {
    echo "- $col\n";
}
