<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Inserting Satuan Jasuke for all users...\n\n";

// Hapus semua Satuan dulu
echo "Deleting old Satuan...\n";
$deletedCount = DB::table('satuans')->delete();
echo "✅ Deleted {$deletedCount} Satuan\n\n";

// Get all users
$users = DB::table('users')->get();
echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "Processing user: {$user->name} (ID: {$user->id})\n";
    
    // Run seeder
    $seeder = new \Database\Seeders\DefaultSatuanSeeder();
    $seeder->run($user->id);
    
    echo "✅ Created 16 Satuan for user {$user->id}\n\n";
}

echo "🎉 Done!\n";
