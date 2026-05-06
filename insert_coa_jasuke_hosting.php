<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Inserting COA Jasuke for all users...\n\n";

// Hapus semua COA dulu
echo "Deleting old COA...\n";
$deletedCount = DB::table('coas')->delete();
echo "✅ Deleted {$deletedCount} COA\n\n";

// Get all users
$users = DB::table('users')->get();
echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "Processing user: {$user->name} (ID: {$user->id})\n";
    
    // Run seeder
    $seeder = new \Database\Seeders\DefaultCoaSeederBaru();
    $seeder->run($user->id);
    
    echo "✅ Created 50 COA for user {$user->id}\n\n";
}

echo "🎉 Done!\n";
