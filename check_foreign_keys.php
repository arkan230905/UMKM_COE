<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECK FOREIGN KEY CONSTRAINTS ===\n\n";

// Check foreign keys on bops table
try {
    $foreignKeys = \Illuminate\Support\Facades\DB::select("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME IN ('bops', 'komponen_bops')
    ");
    
    echo "Foreign Key Constraints:\n";
    foreach ($foreignKeys as $fk) {
        echo "- Table: {$fk->TABLE_NAME}\n";
        echo "  Column: {$fk->COLUMN_NAME}\n";
        echo "  Constraint: {$fk->CONSTRAINT_NAME}\n";
        echo "  References: {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error checking foreign keys: " . $e->getMessage() . "\n";
}

// Check if there are any references to these tables
echo "=== CHECK REFERENCES TO BOPS TABLE ===\n";
try {
    $references = \Illuminate\Support\Facades\DB::select("
        SELECT 
            TABLE_NAME,
            COLUMN_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = DATABASE() 
            AND REFERENCED_TABLE_NAME IN ('bops', 'komponen_bops')
    ");
    
    echo "Tables referencing bops/komponen_bops:\n";
    foreach ($references as $ref) {
        echo "- Table: {$ref->TABLE_NAME}\n";
        echo "  Column: {$ref->COLUMN_NAME}\n";
        echo "  References: {$ref->REFERENCED_TABLE_NAME}.{$ref->REFERENCED_COLUMN_NAME}\n";
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error checking references: " . $e->getMessage() . "\n";
}

echo "\n=== SAFE CLEANUP STRATEGY ===\n";
echo "1. Disable foreign key checks temporarily\n";
echo "2. Drop tables\n";
echo "3. Re-enable foreign key checks\n";
echo "\n";
