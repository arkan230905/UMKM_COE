<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TESTING MULTI-TENANT STOCK SYSTEM ===\n\n";

// Test dengan user yang berbeda
$users = User::take(2)->get();

foreach ($users as $user) {
    echo "=== TESTING AS USER: {$user->name} (ID: {$user->id}) ===\n";
    
    // Login sebagai user ini
    auth()->loginUsingId($user->id);
    
    // Test 1: Cek berapa bahan baku yang terlihat
    $bahanBakuCount = BahanBaku::count();
    echo "1. BahanBaku visible to this user: {$bahanBakuCount}\n";
    
    // Test 2: Cek berapa stock movement yang terlihat
    $stockMovementCount = StockMovement::count();
    echo "2. StockMovement visible to this user: {$stockMovementCount}\n";
    
    // Test 3: Ambil sample bahan baku dan cek stoknya
    $sampleBahan = BahanBaku::first();
    if ($sampleBahan) {
        echo "3. Sample bahan: {$sampleBahan->nama_bahan}\n";
        echo "   - Stok real-time: {$sampleBahan->stok_real_time}\n";
        echo "   - Stok accessor: {$sampleBahan->stok}\n";
        
        // Cek stock movements untuk bahan ini
        $movements = StockMovement::where('item_type', 'material')
            ->where('item_id', $sampleBahan->id)
            ->get();
        echo "   - Stock movements: " . $movements->count() . "\n";
    } else {
        echo "3. No bahan baku found for this user\n";
    }
    
    // Test 4: Buat bahan baku baru untuk test
    echo "4. Creating new test bahan baku...\n";
    $testBahan = BahanBaku::create([
        'nama_bahan' => 'Test Bahan ' . $user->name,
        'kode_bahan' => 'TEST-' . $user->id,
        'satuan_id' => 1, // Assuming KG exists
        'harga_satuan' => 10000,
        'saldo_awal' => 5,
        'stok_minimum' => 1
    ]);
    
    echo "   - Created: {$testBahan->nama_bahan} (ID: {$testBahan->id})\n";
    echo "   - User ID: {$testBahan->user_id}\n";
    echo "   - Initial stock: {$testBahan->stok_real_time}\n";
    
    // Test 5: Buat stock movement
    echo "5. Creating stock movement...\n";
    $stockMovement = StockMovement::create([
        'item_type' => 'material',
        'item_id' => $testBahan->id,
        'direction' => 'in',
        'qty' => 10,
        'unit' => 'KG',
        'unit_cost' => 10000,
        'total_cost' => 100000,
        'ref_type' => 'purchase',
        'tanggal' => now()->format('Y-m-d'),
        'keterangan' => 'Test purchase for ' . $user->name
    ]);
    
    echo "   - Created stock movement ID: {$stockMovement->id}\n";
    echo "   - User ID: {$stockMovement->user_id}\n";
    
    // Refresh dan cek stok lagi
    $testBahan->refresh();
    echo "   - Stock after movement: {$testBahan->stok_real_time}\n";
    
    echo "\n";
}

// Test 6: Cross-user visibility test
echo "=== CROSS-USER VISIBILITY TEST ===\n";

$user1 = User::find(1);
$user2 = User::find(2);

if ($user1 && $user2) {
    // Login sebagai user 1
    auth()->loginUsingId($user1->id);
    $user1BahanCount = BahanBaku::count();
    $user1StockCount = StockMovement::count();
    
    // Login sebagai user 2
    auth()->loginUsingId($user2->id);
    $user2BahanCount = BahanBaku::count();
    $user2StockCount = StockMovement::count();
    
    echo "User 1 ({$user1->name}) sees:\n";
    echo "  - BahanBaku: {$user1BahanCount}\n";
    echo "  - StockMovement: {$user1StockCount}\n";
    
    echo "User 2 ({$user2->name}) sees:\n";
    echo "  - BahanBaku: {$user2BahanCount}\n";
    echo "  - StockMovement: {$user2StockCount}\n";
    
    if ($user1BahanCount != $user2BahanCount || $user1StockCount != $user2StockCount) {
        echo "✅ GOOD: Users see different data (multi-tenant working)\n";
    } else {
        echo "❌ PROBLEM: Users see same data (multi-tenant not working)\n";
    }
} else {
    echo "Cannot test - need at least 2 users\n";
}

// Cleanup test data
echo "\n=== CLEANUP TEST DATA ===\n";
$deletedBahan = BahanBaku::where('nama_bahan', 'like', 'Test Bahan %')->delete();
$deletedMovements = StockMovement::where('keterangan', 'like', 'Test purchase for %')->delete();

echo "Deleted {$deletedBahan} test bahan baku\n";
echo "Deleted {$deletedMovements} test stock movements\n";

echo "\n=== MULTI-TENANT TEST COMPLETED ===\n";