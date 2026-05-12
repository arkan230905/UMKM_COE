<?php

/**
 * TEST SATUAN SEEDER
 * 
 * Test if Satuan seeder works correctly for a user
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TEST SATUAN SEEDER ===\n\n";

// Test with User 2
$userId = 2;

$user = DB::table('users')->where('id', $userId)->first();

if (!$user) {
    echo "❌ User {$userId} not found\n";
    exit(1);
}

echo "Testing for User: {$user->name} (ID: {$userId})\n\n";

// Check current satuan count
$currentCount = DB::table('satuans')->where('user_id', $userId)->count();
echo "Current Satuan count: {$currentCount}\n\n";

if ($currentCount > 0) {
    echo "User already has Satuan. Delete them first? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if ($line === 'yes') {
        DB::table('satuans')->where('user_id', $userId)->delete();
        echo "✅ Deleted existing Satuan\n\n";
    } else {
        echo "Keeping existing Satuan\n\n";
    }
}

echo "Running DefaultSatuanSeeder...\n";

try {
    $seeder = new \Database\Seeders\DefaultSatuanSeeder();
    $seeder->run($userId);
    
    echo "✅ Seeder executed\n\n";
    
    // Check result
    $newCount = DB::table('satuans')->where('user_id', $userId)->count();
    echo "New Satuan count: {$newCount}\n\n";
    
    if ($newCount > 0) {
        echo "✅ SUCCESS! Created {$newCount} Satuan\n\n";
        
        // Show sample
        echo "Sample Satuan:\n";
        $satuans = DB::table('satuans')
            ->where('user_id', $userId)
            ->orderBy('kode')
            ->limit(10)
            ->get(['kode', 'nama', 'tipe', 'kategori', 'is_dasar']);
        
        foreach ($satuans as $satuan) {
            $dasar = $satuan->is_dasar ? '(DASAR)' : '';
            echo "  {$satuan->kode} - {$satuan->nama} | Tipe: {$satuan->tipe} | Kategori: {$satuan->kategori} {$dasar}\n";
        }
        
        if ($newCount > 10) {
            echo "  ... and " . ($newCount - 10) . " more\n";
        }
    } else {
        echo "❌ FAILED! No Satuan created\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n✅ Test complete!\n";
