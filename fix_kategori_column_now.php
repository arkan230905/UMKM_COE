<?php
/**
 * Direct fix for kategori column - make it nullable
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING KATEGORI COLUMN ===\n\n";

try {
    // Direct SQL to alter the table
    DB::statement('ALTER TABLE `beban_operasional` MODIFY COLUMN `kategori` VARCHAR(255) NULL');
    
    echo "✓ SUCCESS: Column 'kategori' is now nullable\n\n";
    
    // Verify the change
    $columns = DB::select("SHOW COLUMNS FROM beban_operasional WHERE Field = 'kategori'");
    
    if (!empty($columns)) {
        $column = $columns[0];
        echo "Column details:\n";
        echo "- Field: " . $column->Field . "\n";
        echo "- Type: " . $column->Type . "\n";
        echo "- Null: " . $column->Null . "\n";
        echo "- Default: " . ($column->Default ?? 'NULL') . "\n";
    }
    
    echo "\n✓ You can now save Beban Operasional without kategori field!\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nPlease run this SQL manually in phpMyAdmin:\n";
    echo "ALTER TABLE `beban_operasional` MODIFY COLUMN `kategori` VARCHAR(255) NULL;\n";
}
