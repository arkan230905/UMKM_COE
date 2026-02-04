<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DATABASE RESET SCRIPT ===\n";
echo "This will clean all data from your database\n";
echo "WARNING: This action cannot be undone!\n\n";

// List of tables to truncate (in order of dependencies)
$tables = [
    // Transaction tables (children first)
    'purchase_returns',
    'purchase_details', 
    'pembelians',
    'penjualan_details',
    'penjualans',
    'production_details',
    'produksis',
    'presensis',
    'penggajians',
    'pembayaran_bebans',
    'pelunasan_utangs',
    'retur_pembelians',
    'returs',
    'expense_payments',
    'ap_settlements',
    
    // Master data tables
    'bahan_bakus',
    'bahan_pendukungs', 
    'produks',
    'vendors',
    'pegawais',
    'satuans',
    'biaya_bahans',
    'btkls',
    'bops',
    'boms',
    'asets',
    'coas',
    
    // User and company tables
    'users',
    'perusahaans',
    
    // System tables
    'migrations',
    'failed_jobs',
    'password_resets',
    'personal_access_tokens',
    'sessions',
    'cache',
    'cache_locks',
    'job_batches',
    'telescope_entries',
    'telescope_entries_tags',
    'telescope_monitoring'
];

echo "Tables to be cleaned:\n";
foreach ($tables as $table) {
    echo "- $table\n";
}

echo "\nStarting database cleanup...\n";

try {
    // Disable foreign key checks
    \DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    foreach ($tables as $table) {
        try {
            // Check if table exists
            $tableExists = \Schema::hasTable($table);
            
            if ($tableExists) {
                // Get count before truncating
                $count = \DB::table($table)->count();
                echo "Cleaning table: $table ($count records)... ";
                
                // Truncate table
                \DB::table($table)->truncate();
                
                echo "✓ Done\n";
            } else {
                echo "Skipping table: $table (does not exist)\n";
            }
        } catch (Exception $e) {
            echo "Error cleaning table $table: " . $e->getMessage() . "\n";
        }
    }
    
    // Re-enable foreign key checks
    \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n=== DATABASE CLEANUP COMPLETED ===\n";
    echo "All tables have been truncated successfully!\n";
    
    // Reset auto-increment values
    echo "\nResetting auto-increment values...\n";
    foreach ($tables as $table) {
        try {
            if (\Schema::hasTable($table)) {
                \DB::statement("ALTER TABLE $table AUTO_INCREMENT = 1");
                echo "Reset auto-increment for: $table ✓\n";
            }
        } catch (Exception $e) {
            echo "Could not reset auto-increment for $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== AUTO-INCREMENT RESET COMPLETED ===\n";
    
    // Run migrations to recreate basic structure
    echo "\nRunning migrations to recreate basic structure...\n";
    try {
        \Artisan::call('migrate:fresh', ['--force' => true]);
        echo "Migrations completed successfully! ✓\n";
    } catch (Exception $e) {
        echo "Migration error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== DATABASE RESET COMPLETED SUCCESSFULLY ===\n";
    echo "Your database is now clean and ready for fresh testing!\n";
    echo "\nNext steps:\n";
    echo "1. Run: php artisan serve\n";
    echo "2. Register a new user\n";
    echo "3. Start testing your application\n";
    
} catch (Exception $e) {
    echo "Error during database cleanup: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>
