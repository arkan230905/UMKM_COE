<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking user data and role field...\n\n";

// Check users table structure
echo "Users table structure:\n";
$columns = \Schema::getColumnListing('users');
foreach ($columns as $column) {
    echo "- $column\n";
}

echo "\nCurrent users in database:\n";
$users = \DB::table('users')->get();
foreach ($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Role: " . (isset($user->role) ? $user->role : 'NULL') . "\n";
}

echo "\nChecking if role field exists in users table...\n";
if (\Schema::hasColumn('users', 'role')) {
    echo "✓ Role field exists in users table\n";
} else {
    echo "✗ Role field does NOT exist in users table\n";
    echo "Adding role field to users table...\n";
    
    try {
        \Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
        });
        echo "✓ Role field added successfully\n";
        
        // Update existing users with default role
        \DB::table('users')->whereNull('role')->update(['role' => 'owner']);
        echo "✓ Existing users updated with 'owner' role\n";
        
    } catch (\Exception $e) {
        echo "✗ Error adding role field: " . $e->getMessage() . "\n";
    }
}

echo "\nFinal user data:\n";
$finalUsers = \DB::table('users')->get();
foreach ($finalUsers as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
}
