<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING STOCK_MOVEMENTS TABLE STRUCTURE ===\n\n";

try {
    $columns = DB::select("DESCRIBE stock_movements");
    
    echo "STOCK_MOVEMENTS TABLE COLUMNS:\n";
    foreach ($columns as $col) {
        echo "- {$col->Field} | {$col->Type} | Null: {$col->Null} | Default: {$col->Default}\n";
    }
    
    echo "\n=== SAMPLE DATA ===\n";
    $sampleData = DB::table('stock_movements')->limit(3)->get();
    
    if ($sampleData->count() > 0) {
        echo "Found " . $sampleData->count() . " sample records:\n";
        foreach ($sampleData as $record) {
            echo "ID: {$record->id} | Type: {$record->item_type} | Item ID: {$record->item_id} | Direction: {$record->direction} | Qty: {$record->qty}\n";
        }
    } else {
        echo "No data found in stock_movements table\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";