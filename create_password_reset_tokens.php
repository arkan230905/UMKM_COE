<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Database\Schema\Blueprint;

echo "Creating password_reset_tokens table...\n";

try {
    // Check if table already exists
    if (\Schema::hasTable('password_reset_tokens')) {
        echo "✓ Table password_reset_tokens already exists\n";
    } else {
        // Create the table
        \Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        
        echo "✓ Table password_reset_tokens created successfully\n";
    }
    
    // Check if there are any password reset related tables
    echo "\nChecking password reset tables:\n";
    $tables = \DB::select('SHOW TABLES LIKE "%password%"');
    foreach ($tables as $table) {
        foreach ($table as $key => $value) {
            echo "- $value\n";
        }
    }
    
    echo "\n✅ Password reset functionality is now ready!\n";
    
} catch (\Exception $e) {
    echo "Error creating password_reset_tokens table: " . $e->getMessage() . "\n";
}
