<?php

require 'vendor/autoload.php';

echo "=== CHECKING OLD TABLES ===\n\n";

try {
    // Check if old tables still exist
    $tables = Illuminate\Support\Facades\DB::select('SHOW TABLES LIKE "%bom_job%"');
    
    echo "Tables with 'bom_job' prefix:\n";
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "- {$tableName}\n";
    }
    
    if (empty($tables)) {
        echo "✅ No old tables found - cleanup successful!\n";
    } else {
        echo "❌ Old tables still exist - cleanup incomplete!\n";
    }
    
    echo "\n=== CHECK COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}
