<?php

/**
 * SEED SATUAN FOR ALL USERS
 * 
 * Seed Satuan for all existing users who don't have Satuan yet
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SEED SATUAN FOR ALL USERS ===\n\n";

$users = DB::table('users')->get(['id', 'name', 'email']);

if ($users->isEmpty()) {
    echo "❌ No users found\n";
    exit(1);
}

echo "Found " . $users->count() . " users\n\n";

$seeded = 0;
$skipped = 0;

foreach ($users as $user) {
    echo "User {$user->id} ({$user->name}):\n";
    
    $satuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    
    if ($satuanCount > 0) {
        echo "  ⏭️  Already has {$satuanCount} Satuan - SKIPPED\n\n";
        $skipped++;
        continue;
    }
    
    try {
        $seeder = new \Database\Seeders\DefaultSatuanSeeder();
        $seeder->run($user->id);
        
        $newCount = DB::table('satuans')->where('user_id', $user->id)->count();
        echo "  ✅ Created {$newCount} Satuan\n\n";
        $seeded++;
        
    } catch (\Exception $e) {
        echo "  ❌ ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "=== SUMMARY ===\n";
echo "Total users: " . $users->count() . "\n";
echo "Seeded: {$seeded}\n";
echo "Skipped: {$skipped}\n\n";

// Verify all users
echo "=== VERIFICATION ===\n";
foreach ($users as $user) {
    $satuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    
    if ($satuanCount > 0) {
        echo "✅ User {$user->id} ({$user->name}): {$satuanCount} Satuan\n";
    } else {
        echo "❌ User {$user->id} ({$user->name}): NO Satuan\n";
    }
}

echo "\n✅ Complete!\n";
