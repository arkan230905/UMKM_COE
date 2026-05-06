<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing bop_proses table - removing proses_produksi_id\n\n";

try {
    // Get all foreign keys
    $fks = DB::select("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'bop_proses' 
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ");
    
    echo "Found " . count($fks) . " foreign keys\n";
    
    foreach ($fks as $fk) {
        echo "Dropping FK: {$fk->CONSTRAINT_NAME}\n";
        DB::statement("ALTER TABLE bop_proses DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
    }
    
    // Drop the column
    echo "\nDropping column proses_produksi_id...\n";
    DB::statement("ALTER TABLE bop_proses DROP COLUMN proses_produksi_id");
    
    echo "\n✓ SUCCESS! Column removed.\n";
    
} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
}
