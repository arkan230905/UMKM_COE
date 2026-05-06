<?php

require __DIR__ . '/vendor/autoload.php';

echo "=== CHECKING OLD TABLES WITH LARAVEL ===\n\n";

use Illuminate\Support\Facades\Schema;

try {
    $tablesToCheck = [
        'bom_job_costings',
        'bom_job_bbb',
        'bom_job_bahan_pendukung',
        'bom_job_btkl',
        'bom_job_bop'
    ];
    
    $existingTables = [];
    $droppedTables = [];
    
    foreach ($tablesToCheck as $table) {
        if (Schema::hasTable($table)) {
            $existingTables[] = $table;
            echo "❌ Table '{$table}' still exists\n";
        } else {
            $droppedTables[] = $table;
            echo "✅ Table '{$table}' successfully dropped\n";
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "Total tables checked: " . count($tablesToCheck) . "\n";
    echo "Tables dropped: " . count($droppedTables) . "\n";
    echo "Tables still exist: " . count($existingTables) . "\n\n";
    
    if (count($existingTables) === 0) {
        echo "🎉 SUCCESS: All old tables have been successfully removed!\n";
        echo "✅ Database cleanup is complete!\n";
        echo "✅ New HPP system can now work without conflicts!\n";
    } else {
        echo "⚠️  WARNING: Some old tables still exist:\n";
        foreach ($existingTables as $table) {
            echo "   - {$table}\n";
        }
        echo "❌ Manual cleanup may be required\n";
    }
    
} catch (Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
