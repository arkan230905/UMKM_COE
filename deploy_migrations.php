<?php
/**
 * Deployment script to run migrations on production
 * 
 * Usage: 
 * 1. Upload this file to your hosting root directory
 * 2. Access it via browser: http://yourdomain.com/deploy_migrations.php
 * 3. Delete this file after successful deployment for security
 */

// Security: Only allow execution from specific IP or with a secret key
$SECRET_KEY = 'your-secret-key-here'; // Change this!
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $SECRET_KEY) {
    die('Unauthorized access');
}

echo "<h1>Migration Deployment</h1>";
echo "<pre>";

// Change to your Laravel root directory if needed
chdir(__DIR__);

echo "Current directory: " . getcwd() . "\n\n";

// Check if artisan exists
if (!file_exists('artisan')) {
    die("Error: artisan file not found. Make sure you're in the Laravel root directory.\n");
}

echo "=== Checking migration files ===\n";
$migrations = [
    '2026_05_19_000001_rename_coa_period_id_to_period_id_in_coa_period_balances.php',
    '2026_05_19_000002_add_bank_id_to_pembelians_table.php',
    '2026_05_19_000003_add_missing_financial_columns_to_pembelians_table.php',
    '2026_05_19_000004_fix_bank_id_foreign_key_in_pembelians.php',
    '2026_05_19_000005_force_add_financial_columns_to_pembelians.php',
    '2026_05_19_000006_add_missing_columns_to_stock_movements.php',
];

foreach ($migrations as $migration) {
    $path = "database/migrations/$migration";
    if (file_exists($path)) {
        echo "✓ Found: $migration\n";
    } else {
        echo "✗ Missing: $migration\n";
    }
}

echo "\n=== Running migrations ===\n";
$output = [];
$return_var = 0;

// Run migration command
exec('php artisan migrate --force 2>&1', $output, $return_var);

foreach ($output as $line) {
    echo $line . "\n";
}

if ($return_var === 0) {
    echo "\n✓ Migrations completed successfully!\n";
} else {
    echo "\n✗ Migration failed with exit code: $return_var\n";
}

echo "\n=== Verifying pembelians table structure ===\n";
exec('php artisan tinker --execute="echo json_encode(Schema::getColumnListing(\'pembelians\'));" 2>&1', $output2, $return_var2);

if (!empty($output2)) {
    $columns = json_decode(end($output2), true);
    if ($columns) {
        echo "Pembelians table columns:\n";
        foreach ($columns as $column) {
            echo "  - $column\n";
        }
        
        // Check for required columns
        $required = ['bank_id', 'subtotal', 'total_harga', 'status'];
        $missing = array_diff($required, $columns);
        
        if (empty($missing)) {
            echo "\n✓ All required columns are present!\n";
        } else {
            echo "\n✗ Missing columns: " . implode(', ', $missing) . "\n";
        }
    }
}

echo "\n=== IMPORTANT ===\n";
echo "Delete this file (deploy_migrations.php) after successful deployment for security!\n";

echo "</pre>";
?>
