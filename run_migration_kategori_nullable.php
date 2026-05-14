<?php
/**
 * Script to make kategori column nullable in beban_operasional table
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Making kategori column nullable in beban_operasional table...\n\n";

try {
    // Run the migration
    Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_04_27_make_kategori_nullable_in_beban_operasional.php',
        '--force' => true
    ]);
    
    echo Artisan::output();
    echo "\n✓ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
