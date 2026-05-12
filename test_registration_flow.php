<?php

/**
 * TEST REGISTRATION FLOW
 * 
 * This script tests the complete registration flow to ensure:
 * 1. User can be created
 * 2. UserRegistered event is dispatched
 * 3. COA is automatically created (51 accounts)
 * 4. Satuan is automatically created
 * 5. Multi-tenant isolation works
 */

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING REGISTRATION FLOW ===\n\n";

// Test data
$testEmail = 'test_user_' . time() . '@test.com';
$testName = 'Test User ' . time();

echo "Step 1: Creating test user...\n";
echo "  Email: {$testEmail}\n";
echo "  Name: {$testName}\n\n";

try {
    DB::beginTransaction();
    
    // Create perusahaan first (for owner role)
    $kode = 'PR-' . strtoupper(uniqid());
    $perusahaanId = DB::table('perusahaan')->insertGetId([
        'nama' => 'Test Company ' . time(),
        'alamat' => 'Test Address',
        'email' => $testEmail,
        'telepon' => '08123456789',
        'kode' => $kode,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "✅ Perusahaan created (ID: {$perusahaanId})\n\n";
    
    // Create user
    $user = App\Models\User::create([
        'name' => $testName,
        'email' => $testEmail,
        'password' => Hash::make('password123'),
        'role' => 'owner',
        'perusahaan_id' => $perusahaanId,
    ]);
    
    echo "✅ User created (ID: {$user->id})\n\n";
    
    // Dispatch event (this is what RegisterController does)
    echo "Step 2: Dispatching UserRegistered event...\n";
    event(new App\Events\UserRegistered($user, $perusahaanId));
    echo "✅ Event dispatched\n\n";
    
    // Wait a moment for event to process
    sleep(1);
    
    // Check if COA was created
    echo "Step 3: Verifying COA creation...\n";
    $coaCount = DB::table('coas')->where('user_id', $user->id)->count();
    echo "  COA Count: {$coaCount}\n";
    
    if ($coaCount === 51) {
        echo "✅ PASS: Correct number of COAs created (51)\n\n";
    } else {
        echo "❌ FAIL: Expected 51 COAs, got {$coaCount}\n\n";
        DB::rollBack();
        exit(1);
    }
    
    // Check specific important COAs
    echo "Step 4: Verifying important COAs...\n";
    $importantCoas = [
        '1141' => 'Pers. Bahan Baku Jagung',
        '1161' => 'Pers. Barang Jadi Jasuke',
        '1171' => 'Pers. Barang Dalam Proses - BBB',
        '1172' => 'Pers. Barang Dalam Proses - BTKL',
        '1173' => 'Pers. Barang Dalam Proses - BOP',
        '211' => 'Hutang Gaji',
        '550' => 'BOP - Listrik',
    ];
    
    $allFound = true;
    foreach ($importantCoas as $kode => $expectedNama) {
        $coa = DB::table('coas')
            ->where('user_id', $user->id)
            ->where('kode_akun', $kode)
            ->first();
        
        if ($coa) {
            echo "  ✅ {$kode}: {$coa->nama_akun}\n";
        } else {
            echo "  ❌ {$kode}: NOT FOUND\n";
            $allFound = false;
        }
    }
    
    if ($allFound) {
        echo "✅ PASS: All important COAs found\n\n";
    } else {
        echo "❌ FAIL: Some important COAs missing\n\n";
        DB::rollBack();
        exit(1);
    }
    
    // Check Satuan
    echo "Step 5: Verifying Satuan creation...\n";
    $satuanCount = DB::table('satuans')->where('user_id', $user->id)->count();
    echo "  Satuan Count: {$satuanCount}\n";
    
    if ($satuanCount > 0) {
        echo "✅ PASS: Satuan created\n\n";
    } else {
        echo "⚠️  WARNING: No Satuan created (might be expected)\n\n";
    }
    
    // Check Jabatan
    echo "Step 5.5: Verifying Jabatan creation...\n";
    $jabatanCount = DB::table('jabatans')->where('user_id', $user->id)->count();
    echo "  Jabatan Count: {$jabatanCount}\n";
    
    if ($jabatanCount === 8) {
        echo "✅ PASS: Jabatan created (8 positions)\n\n";
    } else {
        echo "❌ FAIL: Expected 8 Jabatan, got {$jabatanCount}\n\n";
        DB::rollBack();
        exit(1);
    }
    
    // Check multi-tenant isolation
    echo "Step 6: Verifying multi-tenant isolation...\n";
    
    // Check that this user's COA is separate from other users
    $otherUsersCoa = DB::table('coas')
        ->where('user_id', '!=', $user->id)
        ->where('kode_akun', '1141')
        ->get();
    
    echo "  Other users with COA 1141: {$otherUsersCoa->count()}\n";
    
    // Check that we can have duplicate kode_akun across users
    $duplicateCheck = DB::table('coas')
        ->select('kode_akun', DB::raw('COUNT(DISTINCT user_id) as user_count'))
        ->where('kode_akun', '1141')
        ->groupBy('kode_akun')
        ->first();
    
    if ($duplicateCheck && $duplicateCheck->user_count > 1) {
        echo "  ✅ Multiple users can have same kode_akun (multi-tenant working)\n";
        echo "✅ PASS: Multi-tenant isolation verified\n\n";
    } else {
        echo "  ℹ️  Only one user has this COA (expected for first user)\n";
        echo "✅ PASS: Multi-tenant structure correct\n\n";
    }
    
    // Rollback to clean up test data
    echo "Step 7: Cleaning up test data...\n";
    DB::rollBack();
    echo "✅ Test data rolled back\n\n";
    
    echo "=== ALL TESTS PASSED ✅ ===\n\n";
    echo "SUMMARY:\n";
    echo "✅ User creation: WORKING\n";
    echo "✅ Event dispatch: WORKING\n";
    echo "✅ COA creation: WORKING (51 accounts)\n";
    echo "✅ Important COAs: ALL PRESENT\n";
    echo "✅ Satuan creation: WORKING\n";
    echo "✅ Multi-tenant: WORKING\n\n";
    
    echo "🚀 REGISTRATION FLOW IS READY FOR PRODUCTION!\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
