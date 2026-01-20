<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING boms TABLE STRUCTURE ===\n\n";

$columns = \DB::select('DESCRIBE boms');

foreach ($columns as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type} | Null: {$column->Null} | Default: " . ($column->Default ?? 'NULL') . "\n";
}

echo "\n=== DONE ===\n";
