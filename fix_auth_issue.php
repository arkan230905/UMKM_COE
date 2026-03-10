<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking authentication setup...\n\n";

// Check if there are any authentication guards configured
echo "Authentication guards:\n";
$guards = config('auth.guards');
foreach ($guards as $key => $guard) {
    echo "- $key: " . ($guard['driver'] ?? 'unknown') . "\n";
}

echo "\nUser provider configuration:\n";
$providers = config('auth.providers');
foreach ($providers as $key => $provider) {
    echo "- $key: " . ($provider['driver'] ?? 'unknown') . "\n";
}

echo "\nChecking current session data...\n";
// This won't work in CLI context, but let's check if there are any session issues

echo "\nCreating a test owner user if needed...\n";
$ownerUser = \DB::table('users')->where('email', 'adminumkm@gmail.com')->first();
if (!$ownerUser) {
    echo "Creating owner user...\n";
    \DB::table('users')->insert([
        'name' => 'Admin UMKM',
        'email' => 'adminumkm@gmail.com',
        'username' => 'adminumkm',
        'password' => bcrypt('password'),
        'role' => 'owner',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ Owner user created\n";
} else {
    echo "✓ Owner user already exists\n";
}

echo "\nUpdating all admin users to have owner role...\n";
\DB::table('users')
    ->where('email', 'like', '%admin%')
    ->orWhere('email', 'like', '%owner%')
    ->update(['role' => 'owner']);

echo "✓ Admin users updated to owner role\n";

echo "\nFinal user roles:\n";
$users = \DB::table('users')->get();
foreach ($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
}

echo "\n=== Authentication Fix Complete ===\n";
echo "Try logging in again with:\n";
echo "Email: adminumkm@gmail.com\n";
echo "Password: password\n";
