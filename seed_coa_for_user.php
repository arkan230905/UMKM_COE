<?php
/**
 * SEED COA FOR SPECIFIC USER
 * 
 * Script untuk manually seed COA untuk user tertentu
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SEED COA FOR USER ===\n\n";

$userId = 1; // CHANGE THIS: User yang perlu di-seed COA-nya

echo "Seeding COA for User ID: $userId\n\n";

// Check if user exists
$user = DB::table('users')->where('id', $userId)->first();

if (!$user) {
    echo "❌ User ID $userId not found!\n";
    exit(1);
}

echo "User: {$user->name} ({$user->email})\n\n";

// Check if COA already exists
$existingCoa = DB::table('coas')->where('user_id', $userId)->count();

if ($existingCoa > 0) {
    echo "⚠️  User already has $existingCoa COA records\n";
    echo "Do you want to delete and recreate? (This will delete existing COA!)\n";
    echo "Type 'yes' to continue: ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim($line) !== 'yes') {
        echo "Aborted.\n";
        exit(0);
    }
    
    // Delete existing COA
    DB::table('coas')->where('user_id', $userId)->delete();
    echo "✅ Deleted $existingCoa existing COA records\n\n";
}

// Run seeder
echo "Running DefaultCoaSeeder...\n";

try {
    $seeder = new \Database\Seeders\DefaultCoaSeeder();
    $seeder->run($userId);
    
    $newCoaCount = DB::table('coas')->where('user_id', $userId)->count();
    
    echo "✅ SUCCESS! Created $newCoaCount COA records for User ID $userId\n\n";
    
    // Show sample COA
    echo "Sample COA:\n";
    $sampleCoa = DB::table('coas')
        ->where('user_id', $userId)
        ->orderBy('kode_akun')
        ->limit(10)
        ->get(['kode_akun', 'nama_akun', 'tipe_akun']);
    
    foreach ($sampleCoa as $coa) {
        echo "  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})\n";
    }
    
    echo "\n... and " . ($newCoaCount - 10) . " more\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== COMPLETED ===\n";
echo "User can now login and see their COA at: /master-data/coa\n";
