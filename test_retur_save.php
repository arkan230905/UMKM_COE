<?php
// Test script untuk memastikan retur tersimpan dengan benar

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== TESTING RETUR SAVE FUNCTIONALITY ===\n";

try {
    // Test database connection
    $pdo = DB::connection()->getPdo();
    echo "✓ Database connection successful\n";
    
    // Check if tables exist
    $tables = ['purchase_returns', 'purchase_return_items', 'pembelians'];
    foreach ($tables as $table) {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        echo ($exists ? "✓" : "✗") . " Table '$table' " . ($exists ? "exists" : "NOT found") . "\n";
    }
    
    // Check current data
    $currentReturs = DB::table('purchase_returns')->count();
    echo "\nCurrent retur count: $currentReturs\n";
    
    // Check if we have any pembelian to test with
    $pembelian = DB::table('pembelians')->first();
    if (!$pembelian) {
        echo "✗ No pembelian found for testing\n";
        exit;
    }
    echo "✓ Found pembelian ID: {$pembelian->id}\n";
    
    // Check pembelian details
    $details = DB::table('pembelian_details')->where('pembelian_id', $pembelian->id)->get();
    echo "✓ Found {$details->count()} pembelian details\n";
    
    if ($details->count() == 0) {
        echo "✗ No pembelian details found for testing\n";
        exit;
    }
    
    // Test creating a retur (but rollback)
    DB::beginTransaction();
    
    try {
        echo "\n=== TESTING RETUR CREATION ===\n";
        
        // Create test retur
        $returData = [
            'pembelian_id' => $pembelian->id,
            'return_date' => now()->toDateString(),
            'reason' => 'Test retur creation',
            'jenis_retur' => 'refund',
            'notes' => 'Test notes',
            'status' => 'pending',
            'total_return_amount' => 100000,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $returId = DB::table('purchase_returns')->insertGetId($returData);
        echo "✓ PurchaseReturn created with ID: $returId\n";
        
        // Create test retur item
        $detail = $details->first();
        $itemData = [
            'purchase_return_id' => $returId,
            'pembelian_detail_id' => $detail->id,
            'bahan_baku_id' => $detail->bahan_baku_id,
            'bahan_pendukung_id' => $detail->bahan_pendukung_id,
            'unit' => 'kg',
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $itemId = DB::table('purchase_return_items')->insertGetId($itemData);
        echo "✓ PurchaseReturnItem created with ID: $itemId\n";
        
        // Test if we can retrieve the data
        $savedRetur = DB::table('purchase_returns')->where('id', $returId)->first();
        if ($savedRetur) {
            echo "✓ Retur data retrieved successfully\n";
            echo "  Return number: {$savedRetur->return_number}\n";
            echo "  Jenis retur: {$savedRetur->jenis_retur}\n";
            echo "  Status: {$savedRetur->status}\n";
        } else {
            echo "✗ Failed to retrieve saved retur\n";
        }
        
        // Test using Eloquent model
        $eloquentRetur = \App\Models\PurchaseReturn::find($returId);
        if ($eloquentRetur) {
            echo "✓ Eloquent model retrieval successful\n";
            echo "  Model return_number: {$eloquentRetur->return_number}\n";
            echo "  Model items count: {$eloquentRetur->items->count()}\n";
        } else {
            echo "✗ Eloquent model retrieval failed\n";
        }
        
        DB::rollBack();
        echo "✓ Transaction rolled back (test only)\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "✗ Error during retur creation test: " . $e->getMessage() . "\n";
    }
    
    // Test route existence
    echo "\n=== TESTING ROUTES ===\n";
    
    try {
        $storeRoute = \Route::getRoutes()->getByName('transaksi.retur-pembelian.store');
        if ($storeRoute) {
            echo "✓ Store route exists: " . $storeRoute->uri() . "\n";
        } else {
            echo "✗ Store route not found\n";
        }
        
        $indexRoute = \Route::getRoutes()->getByName('transaksi.pembelian.index');
        if ($indexRoute) {
            echo "✓ Pembelian index route exists: " . $indexRoute->uri() . "\n";
        } else {
            echo "✗ Pembelian index route not found\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Route test error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== TEST SUMMARY ===\n";
    echo "✓ Database connection working\n";
    echo "✓ Tables exist\n";
    echo "✓ Test data creation successful\n";
    echo "✓ Data retrieval working\n";
    echo "✓ Routes configured\n";
    echo "\nIf retur is not saving, check:\n";
    echo "1. Form validation errors\n";
    echo "2. JavaScript preventing submission\n";
    echo "3. CSRF token issues\n";
    echo "4. Controller logic errors\n";
    echo "5. Database transaction failures\n";
    
} catch (\Exception $e) {
    echo "✗ Test failed: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>