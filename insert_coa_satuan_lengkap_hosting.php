<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Inserting COA & Satuan Jasuke for all users...\n\n";

// Hapus semua COA dan Satuan dulu
echo "Deleting old data...\n";
$deletedCoa = DB::table('coas')->delete();
$deletedSatuan = DB::table('satuans')->delete();
echo "✅ Deleted {$deletedCoa} COA and {$deletedSatuan} Satuan\n\n";

// Get all users
$users = DB::table('users')->get();
echo "Found " . count($users) . " users\n\n";

foreach ($users as $user) {
    echo "Processing user: {$user->name} (ID: {$user->id})\n";
    
    // Insert COA
    echo "  📋 Inserting COA...\n";
    $coaSeeder = new \Database\Seeders\DefaultCoaSeederBaru();
    $coaSeeder->run($user->id);
    echo "  ✅ Created 50 COA\n";
    
    // Insert Satuan
    echo "  📋 Inserting Satuan...\n";
    $satuanSeeder = new \Database\Seeders\DefaultSatuanSeeder();
    $satuanSeeder->run($user->id);
    echo "  ✅ Created 16 Satuan\n\n";
}

echo "🎉 Done!\n";
echo "\nSummary:\n";
echo "- COA: 50 accounts per user\n";
echo "- Satuan: 16 units per user\n";
echo "- Total users: " . count($users) . "\n";
