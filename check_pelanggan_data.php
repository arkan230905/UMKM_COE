<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking pelanggan data structure and issues...\n\n";

try {
    // Check users table structure
    echo "=== USERS TABLE STRUCTURE ===\n";
    $columns = \DB::select("DESCRIBE users");
    foreach ($columns as $column) {
        echo sprintf("%-20s %-20s %-10s %-10s %-10s\n", 
            $column->Field, 
            $column->Type, 
            $column->Null, 
            $column->Key, 
            $column->Default
        );
    }
    
    echo "\n=== PELANGGAN DATA ===\n";
    $pelanggans = \DB::table('users')->where('role', 'pelanggan')->get();
    
    if ($pelanggans->count() === 0) {
        echo "No pelanggan data found\n";
    } else {
        echo "Found {$pelanggans->count()} pelanggan(s):\n";
        echo str_repeat("=", 80) . "\n";
        
        foreach ($pelanggans as $pelanggan) {
            echo sprintf("ID: %d | Name: %-20s | Email: %-25s | Phone: %-15s | Created: %s\n", 
                $pelanggan->id, 
                $pelanggan->name ?? 'N/A', 
                $pelanggan->email ?? 'N/A', 
                $pelanggan->no_telepon ?? $pelanggan->phone ?? 'N/A', 
                $pelanggan->created_at ?? 'N/A'
            );
        }
    }
    
    echo "\n=== TESTING PHONE FIELD STORAGE ===\n";
    
    // Test phone number storage
    $testPhone = '08123456789';
    $testEmail = 'test.phone@example.com';
    
    // Check if test user exists
    $existingUser = \DB::table('users')->where('email', $testEmail)->first();
    if ($existingUser) {
        echo "Test user found, updating phone number...\n";
        \DB::table('users')
            ->where('email', $testEmail)
            ->update(['no_telepon' => $testPhone]);
        
        $updatedUser = \DB::table('users')->where('email', $testEmail)->first();
        echo "Phone number updated: " . ($updatedUser->no_telepon ?? 'NULL') . "\n";
    } else {
        echo "Creating test user with phone number...\n";
        \DB::table('users')->insert([
            'name' => 'Test Phone User',
            'email' => $testEmail,
            'no_telepon' => $testPhone,
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'pelanggan',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "Test user created with phone: $testPhone\n";
    }
    
    // Verify storage
    $testUser = \DB::table('users')->where('email', $testEmail)->first();
    echo "Verified phone in database: " . ($testUser->no_telepon ?? 'NULL') . "\n";
    
    echo "\n=== ISSUES FOUND ===\n";
    
    // Check for phone vs no_telepon inconsistency
    $phoneIssues = \DB::table('users')
        ->where('role', 'pelanggan')
        ->where(function($query) {
            $query->whereNull('no_telepon')
                  ->orWhere('no_telepon', '');
        })
        ->count();
    
    if ($phoneIssues > 0) {
        echo "⚠️  $phoneIssues pelanggan(s) have empty/NULL phone numbers\n";
    } else {
        echo "✅ All pelanggan have phone numbers\n";
    }
    
    // Check if 'phone' field exists and is being used
    $hasPhoneField = \Schema::hasColumn('users', 'phone');
    echo "Database has 'phone' field: " . ($hasPhoneField ? 'YES' : 'NO') . "\n";
    
    if ($hasPhoneField) {
        $phoneFieldData = \DB::table('users')
            ->where('role', 'pelanggan')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();
        echo "Data in 'phone' field: $phoneFieldData records\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
