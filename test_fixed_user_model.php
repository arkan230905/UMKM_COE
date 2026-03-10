<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing fixed User model...\n\n";

try {
    // Check User model fillable
    $userModel = new \App\Models\User();
    $fillable = $userModel->getFillable();
    echo "Fillable fields: " . implode(', ', $fillable) . "\n";
    
    if (in_array('plain_password', $fillable)) {
        echo "✅ plain_password is in fillable array\n";
    } else {
        echo "❌ plain_password is NOT in fillable array\n";
    }
    
    // Test mass assignment
    echo "\n=== TESTING MASS ASSIGNMENT ===\n";
    
    $testData = [
        'name' => 'Final Test User',
        'email' => 'final@test.com',
        'phone' => '0800000002',
        'password' => 'finaltest123',
        'plain_password' => 'finaltest123',
        'role' => 'pelanggan',
        'email_verified_at' => now(),
    ];
    
    // Check if email exists
    if (\DB::table('users')->where('email', $testData['email'])->exists()) {
        echo "Email exists, deleting first...\n";
        \DB::table('users')->where('email', $testData['email'])->delete();
    }
    
    try {
        $user = \App\Models\User::create($testData);
        echo "✅ Mass assignment successful\n";
        echo "   User ID: {$user->id}\n";
        echo "   Name: {$user->name}\n";
        echo "   Email: {$user->email}\n";
        echo "   Plain Password: " . ($user->plain_password ?? 'NULL') . "\n";
        
        if ($user->plain_password === $testData['plain_password']) {
            echo "✅ Plain password saved correctly via mass assignment!\n";
            
            // Test what will be displayed in the view
            echo "\n=== VIEW DISPLAY TEST ===\n";
            echo "What will be shown in index.blade.php:\n";
            echo "data-password attribute: {$user->plain_password}\n";
            echo "When clicked: {$user->plain_password}\n";
            
        } else {
            echo "❌ Plain password not saved via mass assignment\n";
            echo "   Expected: {$testData['plain_password']}\n";
            echo "   Got: " . ($user->plain_password ?? 'NULL') . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Mass assignment failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // Clear Laravel cache
    echo "\n=== CLEARING CACHE ===\n";
    \Artisan::call('config:clear');
    echo "✅ Config cache cleared\n";
    
    \Artisan::call('cache:clear');
    echo "✅ Application cache cleared\n";
    
    echo "\n=== INSTRUCTIONS ===\n";
    echo "1. Try creating a new pelanggan via the web interface\n";
    echo "2. Use email: new@test.com\n";
    echo "3. Use password: mypassword123\n";
    echo "4. Check if plain_password is saved correctly\n";
    echo "5. Click the eye icon to see if it shows 'mypassword123'\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
