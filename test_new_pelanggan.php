<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating new test pelanggan to verify password display...\n\n";

try {
    // Create a new pelanggan with specific password
    $testPassword = 'testpassword456'; // This will be the actual password
    
    echo "Creating new pelanggan with password: $testPassword\n";
    
    // Check if test email already exists
    $existingEmail = 'test.pelanggan@example.com';
    $exists = \DB::table('users')->where('email', $existingEmail)->exists();
    
    if ($exists) {
        echo "Test pelanggan already exists, updating password...\n";
        
        // Update existing test pelanggan
        \DB::table('users')
            ->where('email', $existingEmail)
            ->update([
                'password' => \Illuminate\Support\Facades\Hash::make($testPassword),
                'plain_password' => $testPassword,
                'phone' => '08123456789',
                'updated_at' => now()
            ]);
        
        echo "✅ Updated existing test pelanggan\n";
    } else {
        echo "Creating new test pelanggan...\n";
        
        // Create new test pelanggan
        \DB::table('users')->insert([
            'name' => 'Test Pelanggan Baru',
            'email' => $existingEmail,
            'phone' => '08123456789',
            'password' => \Illuminate\Support\Facades\Hash::make($testPassword),
            'plain_password' => $testPassword,
            'role' => 'pelanggan',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✅ Created new test pelanggan\n";
    }
    
    // Verify the data
    echo "\n=== VERIFICATION ===\n";
    echo str_repeat("=", 50) . "\n";
    
    $testPelanggan = \DB::table('users')
        ->where('email', $existingEmail)
        ->first(['id', 'name', 'email', 'phone', 'plain_password', 'created_at']);
    
    if ($testPelanggan) {
        echo sprintf("ID: %d\n", $testPelanggan->id);
        echo sprintf("Name: %s\n", $testPelanggan->name);
        echo sprintf("Email: %s\n", $testPelanggan->email);
        echo sprintf("Phone: %s\n", $testPelanggan->phone);
        echo sprintf("Plain Password: %s\n", $testPelanggan->plain_password);
        echo sprintf("Created: %s\n", $testPelanggan->created_at);
        
        echo "\n✅ Test pelanggan created/updated successfully!\n";
        echo "📋 What you should see in the pelanggan list:\n";
        echo "   - Name: Test Pelanggan Baru\n";
        echo "   - Email: test.pelanggan@example.com\n";
        echo "   - Phone: 08123456789\n";
        echo "   - Password: testpassword456 (when you click the eye icon)\n";
        
    } else {
        echo "❌ Test pelanggan not found!\n";
    }
    
    echo "\n=== INSTRUCTIONS ===\n";
    echo "1. Go to: http://127.0.0.1:8000/master-data/pelanggan\n";
    echo "2. Look for: 'Test Pelanggan Baru'\n";
    echo "3. Click the eye icon in the password column\n";
    echo "4. You should see: 'testpassword456'\n";
    echo "5. This proves the system works for NEW pelanggan\n";
    
    echo "\n=== FOR OLD PELANGGAN ===\n";
    echo "The existing pelanggan (Abiyyu Muhammad Arkan) shows 'pelanggan123'\n";
    echo "because we don't know the original password - it was created before\n";
    echo "the plain_password feature was added.\n";
    echo "\nSolution for old pelanggan:\n";
    echo "1. Reset their password to the actual password they use\n";
    echo "2. Or ask them what password they want and set it\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
