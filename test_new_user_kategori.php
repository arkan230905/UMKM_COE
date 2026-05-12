<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing automatic kategori creation for new user...\n\n";

// Create a test user to simulate registration
$testUser = new \App\Models\User();
$testUser->name = 'Test User Kategori';
$testUser->email = 'testkategori' . time() . '@example.com';
$testUser->password = \Hash::make('password123');
$testUser->role = 'owner'; // Non-admin role
$testUser->save();

echo "Created test user: " . $testUser->name . " (ID: " . $testUser->id . ")\n";

// Check if kategoris were created automatically
$kategoris = \App\Models\KategoriPegawai::where('user_id', $testUser->id)->get();

echo "\n=== KATEGORIS CREATED FOR USER " . $testUser->id . " ===\n";
foreach ($kategoris as $kategori) {
    echo "Kategori: " . $kategori->nama . " - " . $kategori->deskripsi . " (ID: " . $kategori->id . ")\n";
}

if ($kategoris->count() === 2) {
    echo "\n✅ SUCCESS: User automatically got 2 kategoris (BTKL & BTKTL)\n";
} else {
    echo "\n❌ FAILED: Expected 2 kategoris, got " . $kategoris->count() . "\n";
}

// Test the InitMasterDataService directly
echo "\n=== TESTING InitMasterDataService DIRECTLY ===\n";
$anotherTestUser = new \App\Models\User();
$anotherTestUser->name = 'Another Test User';
$anotherTestUser->email = 'anothertest' . time() . '@example.com';
$anotherTestUser->password = \Hash::make('password123');
$anotherTestUser->role = 'owner';
$anotherTestUser->save();

echo "Created another test user: " . $anotherTestUser->name . " (ID: " . $anotherTestUser->id . ")\n";

$initService = new \App\Services\InitMasterDataService();
$result = $initService->initializeForUser($anotherTestUser->id);

if ($result && $result['success']) {
    echo "✅ SUCCESS: Master data initialized\n";
    echo "  - Bahan Baku: " . $result['bahan_baku_count'] . "\n";
    echo "  - Bahan Pendukung: " . $result['bahan_pendukung_count'] . "\n";
    echo "  - Kategori: " . $result['kategori_count'] . "\n";
    
    // Check kategoris
    $kategoris2 = \App\Models\KategoriPegawai::where('user_id', $anotherTestUser->id)->get();
    echo "\nKategoris for user " . $anotherTestUser->id . ":\n";
    foreach ($kategoris2 as $kategori) {
        echo "  - " . $kategori->nama . ": " . $kategori->deskripsi . "\n";
    }
} else {
    echo "❌ FAILED: Master data initialization failed\n";
}

// Clean up test users
echo "\n=== CLEANUP ===\n";
\Illuminate\Support\Facades\DB::table('kategori_pegawai')->where('user_id', $testUser->id)->delete();
\Illuminate\Support\Facades\DB::table('kategori_pegawai')->where('user_id', $anotherTestUser->id)->delete();
$testUser->delete();
$anotherTestUser->delete();

echo "Test users and their kategoris have been cleaned up.\n";

echo "\n=== FINAL VERIFICATION ===\n";
$allKategoris = \App\Models\KategoriPegawai::orderBy('user_id')->orderBy('nama')->get();
echo "Current kategoris in database:\n";
foreach ($allKategoris as $kategori) {
    $userName = $kategori->user ? $kategori->user->name : 'Unknown';
    echo "  - " . $kategori->nama . " (User: " . $userName . " - ID: " . $kategori->user_id . ")\n";
}

echo "\nTest completed!\n";
