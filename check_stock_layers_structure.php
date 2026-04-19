<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING STOCK_LAYERS TABLE STRUCTURE ===\n\n";

try {
    $columns = DB::select("DESCRIBE stock_layers");
    
    echo "STOCK_LAYERS TABLE COLUMNS:\n";
    foreach ($columns as $col) {
        echo "- {$col->Field} | {$col->Type} | Null: {$col->Null} | Default: {$col->Default}\n";
    }
    
    echo "\n=== SAMPLE DATA ===\n";
    $sampleData = DB::table('stock_layers')->limit(3)->get();
    
    if ($sampleData->count() > 0) {
        echo "Found " . $sampleData->count() . " sample records:\n";
        foreach ($sampleData as $record) {
            echo "ID: {$record->id} | Type: {$record->item_type} | Item ID: {$record->item_id} | Qty: {$record->remaining_qty}\n";
        }
    } else {
        echo "No data found in stock_layers table\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";