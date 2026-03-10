<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging pelanggan creation process...\n\n";

try {
    // Check if plain_password column exists
    $hasPlainPassword = \Schema::hasColumn('users', 'plain_password');
    echo "plain_password column exists: " . ($hasPlainPassword ? 'YES' : 'NO') . "\n";
    
    if (!$hasPlainPassword) {
        echo "❌ plain_password column doesn't exist!\n";
        exit(1);
    }
    
    // Check recent pelanggan creation
    echo "\n=== RECENT PELANGGAN CREATIONS ===\n";
    $recentPelanggans = \DB::table('users')
        ->where('role', 'pelanggan')
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get(['id', 'name', 'email', 'plain_password', 'created_at']);
    
    foreach ($recentPelanggans as $pelanggan) {
        echo sprintf("ID: %d | Name: %-20s | Email: %-25s | Plain Password: %-15s | Created: %s\n", 
            $pelanggan->id, 
            $pelanggan->name, 
            $pelanggan->email, 
            $pelanggan->plain_password ?? 'NULL',
            $pelanggan->created_at
        );
    }
    
    // Test manual creation
    echo "\n=== TESTING MANUAL CREATION ===\n";
    $testData = [
        'name' => 'Debug Test User',
        'email' => 'debug@test.com',
        'phone' => '0800000000',
        'password' => 'test123456',
        'plain_password' => 'test123456',
        'role' => 'pelanggan',
        'email_verified_at' => now(),
    ];
    
    echo "Creating test user with password: {$testData['password']}\n";
    
    // Check if email exists
    if (\DB::table('users')->where('email', $testData['email'])->exists()) {
        echo "Email exists, updating...\n";
        \DB::table('users')
            ->where('email', $testData['email'])
            ->update([
                'password' => \Illuminate\Support\Facades\Hash::make($testData['password']),
                'plain_password' => $testData['plain_password'],
                'updated_at' => now()
            ]);
    } else {
        echo "Creating new user...\n";
        $testData['created_at'] = now();
        $testData['updated_at'] = now();
        \DB::table('users')->insert($testData);
    }
    
    // Verify the creation
    $createdUser = \DB::table('users')
        ->where('email', $testData['email'])
        ->first(['id', 'name', 'email', 'plain_password']);
    
    if ($createdUser) {
        echo "✅ User created/updated successfully\n";
        echo "   ID: {$createdUser->id}\n";
        echo "   Name: {$createdUser->name}\n";
        echo "   Email: {$createdUser->email}\n";
        echo "   Plain Password: {$createdUser->plain_password}\n";
        
        if ($createdUser->plain_password === $testData['password']) {
            echo "✅ Plain password stored correctly!\n";
        } else {
            echo "❌ Plain password mismatch!\n";
            echo "   Expected: {$testData['password']}\n";
            echo "   Got: {$createdUser->plain_password}\n";
        }
    } else {
        echo "❌ Failed to create/update user\n";
    }
    
    // Check controller code
    echo "\n=== CHECKING CONTROLLER ===\n";
    $controllerFile = app_path('Http/Controllers/MasterData/PelangganController.php');
    if (file_exists($controllerFile)) {
        $controllerContent = file_get_contents($controllerFile);
        
        if (strpos($controllerContent, 'plain_password') !== false) {
            echo "✅ Controller contains plain_password reference\n";
        } else {
            echo "❌ Controller missing plain_password reference\n";
        }
        
        if (strpos($controllerContent, '$request->password') !== false) {
            echo "✅ Controller uses request password\n";
        } else {
            echo "❌ Controller not using request password\n";
        }
    } else {
        echo "❌ Controller file not found\n";
    }
    
    echo "\n=== NEXT STEPS ===\n";
    echo "1. Check if the form is actually submitting to the correct controller\n";
    echo "2. Verify the controller is being executed\n";
    echo "3. Check for any validation errors that might prevent saving\n";
    echo "4. Look at Laravel logs for any errors\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
