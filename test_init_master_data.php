<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\InitMasterDataService;
use Illuminate\Support\Facades\DB;

echo "=== TEST INIT MASTER DATA SERVICE ===\n";

// Create a test user ID (simulate new user)
$testUserId = 999; // Use a test ID that doesn't exist

try {
    $service = new InitMasterDataService();
    
    echo "Testing InitMasterDataService with user ID: {$testUserId}\n\n";
    
    // Test initialization
    $result = $service->initializeForUser($testUserId);
    
    if ($result && $result['success']) {
        echo "✅ Initialization successful!\n";
        echo "   - Bahan Baku created: {$result['bahan_baku_count']}\n";
        echo "   - Bahan Pendukung created: {$result['bahan_pendukung_count']}\n";
        
        // Verify data was created
        $bahanBakuCount = DB::table('bahan_bakus')->where('user_id', $testUserId)->count();
        $bahanPendukungCount = DB::table('bahan_pendukungs')->where('user_id', $testUserId)->count();
        $konversiCount = DB::table('bahan_konversi')
            ->join('bahan_pendukungs', 'bahan_konversi.bahan_id', '=', 'bahan_pendukungs.id')
            ->where('bahan_pendukungs.user_id', $testUserId)
            ->count();
        
        echo "\n=== VERIFICATION ===\n";
        echo "✅ Bahan Baku in DB: {$bahanBakuCount}\n";
        echo "✅ Bahan Pendukung in DB: {$bahanPendukungCount}\n";
        echo "✅ Konversi records in DB: {$konversiCount}\n";
        
        // Test duplicate prevention
        echo "\n=== TESTING DUPLICATE PREVENTION ===\n";
        $result2 = $service->initializeForUser($testUserId);
        
        if ($result2 === false) {
            echo "✅ Duplicate prevention works - no data created on second run\n";
        } else {
            echo "❌ Duplicate prevention failed\n";
        }
        
    } else {
        echo "❌ Initialization failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} finally {
    // Cleanup test data
    echo "\n=== CLEANUP ===\n";
    
    // Delete konversi first (foreign key constraint)
    $deletedKonversi = DB::table('bahan_konversi')
        ->join('bahan_pendukungs', 'bahan_konversi.bahan_id', '=', 'bahan_pendukungs.id')
        ->where('bahan_pendukungs.user_id', $testUserId)
        ->delete();
    
    $deletedBahanBaku = DB::table('bahan_bakus')->where('user_id', $testUserId)->delete();
    $deletedBahanPendukung = DB::table('bahan_pendukungs')->where('user_id', $testUserId)->delete();
    
    echo "Cleaned up: {$deletedBahanBaku} bahan baku, {$deletedBahanPendukung} bahan pendukung, {$deletedKonversi} konversi\n";
}

echo "\n✅ TEST COMPLETED\n";