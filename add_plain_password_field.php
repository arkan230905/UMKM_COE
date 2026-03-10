<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding plain password field to users table...\n\n";

try {
    // Check if plain_password column exists
    $hasPlainPassword = \Schema::hasColumn('users', 'plain_password');
    
    if (!$hasPlainPassword) {
        echo "Adding plain_password column to users table...\n";
        
        // Add plain_password column
        \Schema::table('users', function ($table) {
            $table->string('plain_password')->nullable()->after('password');
        });
        
        echo "✅ plain_password column added successfully\n";
    } else {
        echo "ℹ️  plain_password column already exists\n";
    }
    
    // Update existing users with default plain password
    echo "\nUpdating existing pelanggan with plain passwords...\n";
    
    $pelanggans = \DB::table('users')->where('role', 'pelanggan')->get();
    $updatedCount = 0;
    
    foreach ($pelanggans as $pelanggan) {
        // If plain_password is null, set it to 'password123'
        if (!$pelanggan->plain_password) {
            \DB::table('users')
                ->where('id', $pelanggan->id)
                ->update(['plain_password' => 'password123']);
            
            echo "✅ Updated pelanggan ID {$pelanggan->id}: {$pelanggan->name}\n";
            $updatedCount++;
        }
    }
    
    echo "\n=== UPDATE COMPLETE ===\n";
    echo "Total pelanggan updated: $updatedCount\n";
    echo "Default plain password: password123\n";
    
    // Verify the update
    echo "\nVerification - checking plain passwords:\n";
    echo str_repeat("=", 50) . "\n";
    
    $updatedPelanggans = \DB::table('users')
        ->where('role', 'pelanggan')
        ->get(['id', 'name', 'email', 'plain_password']);
    
    foreach ($updatedPelanggans as $pelanggan) {
        echo sprintf("ID: %d | Name: %-20s | Plain Password: %s\n", 
            $pelanggan->id, 
            $pelanggan->name, 
            $pelanggan->plain_password ?? 'NULL'
        );
    }
    
    echo str_repeat("=", 50) . "\n";
    
    echo "\n🎯 Next Steps:\n";
    echo "1. Update PelangganController to save plain_password\n";
    echo "2. Update views to display plain_password instead of hashed password\n";
    echo "3. Add validation for plain_password field\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
