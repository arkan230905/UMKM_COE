<?php
/**
 * CREATE FIRST USER
 * 
 * This script creates a default user account for testing
 * Run: php create_first_user.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== CREATE FIRST USER ===\n\n";

try {
    // Check if users table exists
    $tableExists = \DB::select("SHOW TABLES LIKE 'users'");
    
    if (empty($tableExists)) {
        echo "❌ ERROR: users table does not exist!\n";
        echo "Please run migrations first: php artisan migrate\n";
        exit(1);
    }
    
    echo "✅ users table exists\n\n";
    
    // Check if any users exist
    $userCount = User::count();
    echo "Current users in database: $userCount\n\n";
    
    // Create default admin user
    $email = 'admin@umkm.test';
    
    $existingUser = User::where('email', $email)->first();
    
    if ($existingUser) {
        echo "⚠️  User already exists!\n";
        echo "Email: $email\n";
        echo "Password: password123\n\n";
        echo "You can login now!\n";
    } else {
        $user = User::create([
            'name' => 'Admin UMKM',
            'email' => $email,
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        
        echo "✅ User created successfully!\n\n";
        echo "=== LOGIN CREDENTIALS ===\n";
        echo "Email: $email\n";
        echo "Password: password123\n\n";
        echo "User ID: {$user->id}\n";
        echo "Name: {$user->name}\n\n";
    }
    
    // List all users
    echo "=== ALL USERS IN DATABASE ===\n";
    $users = User::all(['id', 'name', 'email']);
    foreach ($users as $u) {
        echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email}\n";
    }
    
    echo "\n=== NEXT STEPS ===\n";
    echo "1. Start server: php artisan serve\n";
    echo "2. Open browser: http://127.0.0.1:8000/login\n";
    echo "3. Login with: $email / password123\n";
    echo "4. Test pages:\n";
    echo "   - Dashboard: http://127.0.0.1:8000/dashboard\n";
    echo "   - Biaya Bahan: http://127.0.0.1:8000/master-data/biaya-bahan\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
