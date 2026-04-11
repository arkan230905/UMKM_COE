<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING PURCHASE STOCK FIX ===\n\n";

// Get Ayam Potong before test
$ayamPotong = \App\Models\BahanBaku::find(1);
$stockBefore = $ayamPotong->stok;
echo "Ayam Potong stock BEFORE test: {$stockBefore} KG\n";

// Create a test purchase
echo "\nCreating test purchase: 50 ekor Ayam Potong...\n";

try {
    \DB::beginTransaction();
    
    // Create vendor if not exists
    $vendor = \App\Models\Vendor::first();
    if (!$vendor) {
        $vendor = \App\Models\Vendor::create([
            'nama_vendor' => 'Test Vendor',
            'kategori' => 'Bahan Baku',
            'alamat' => 'Test Address',
            'telepon' => '123456789'
        ]);
    }
    
    // Create pembelian
    $pembelian = \App\Models\Pembelian::create([
        'vendor_id' => $vendor->id,
        'nomor_faktur' => 'TEST-001',
        'tanggal' => now()->format('Y-m-d'),
        'subtotal' => 1600000, // 50 * 32000
        'biaya_kirim' => 0,
        'ppn_persen' => 0,
        'ppn_nominal' => 0,
        'total_harga' => 1600000,
        'terbayar' => 1600000,
        'sisa_pembayaran' => 0,
        'status' => 'lunas',
        'payment_method' => 'cash',
        'keterangan' => 'Test purchase for stock fix verification'
    ]);
    
    echo "Created pembelian ID: {$pembelian->id}\n";
    
    // Create pembelian detail
    $detail = \App\Models\PembelianDetail::create([
        'pembelian_id' => $pembelian->id,
        'bahan_baku_id' => 1, // Ayam Potong
        'jumlah' => 50, // 50 ekor
        'satuan' => 'ekor',
        'harga_satuan' => 32000,
        'subtotal' => 1600000,
        'faktor_konversi' => 0.8, // 1 ekor = 0.8 kg
        'jumlah_satuan_utama' => 40 // 50 * 0.8 = 40 kg
    ]);
    
    echo "Created pembelian detail ID: {$detail->id}\n";
    
    // Manually update stock using the model method (simulating controller behavior)
    echo "\nUpdating stock manually (simulating PembelianController)...\n";
    $updateSuccess = $ayamPotong->updateStok(40, 'in', 'Test purchase');
    echo "Stock update success: " . ($updateSuccess ? 'YES' : 'NO') . "\n";
    
    // Check stock after update
    $ayamPotong->refresh();
    $stockAfter = $ayamPotong->stok;
    echo "Ayam Potong stock AFTER manual update: {$stockAfter} KG\n";
    
    // Check if observer would have been triggered (it should be disabled now)
    echo "\nChecking if PembelianDetailObserver is disabled...\n";
    
    // The observer should NOT update stock anymore
    // Stock should remain at 40 KG (not 80 KG which would indicate double counting)
    
    $expectedStock = $stockBefore + 40;
    $actualStock = $stockAfter;
    
    echo "\nRESULTS:\n";
    echo "Expected stock: {$expectedStock} KG\n";
    echo "Actual stock: {$actualStock} KG\n";
    
    if (abs($actualStock - $expectedStock) < 0.0001) {
        echo "✅ SUCCESS: Stock updated correctly (no double counting)\n";
    } else {
        echo "❌ FAILED: Stock update incorrect (possible double counting)\n";
    }
    
    \DB::rollback(); // Don't save the test data
    echo "\nTest data rolled back.\n";
    
} catch (\Exception $e) {
    \DB::rollback();
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// Final stock check
$ayamPotong->refresh();
$finalStock = $ayamPotong->stok;
echo "\nFinal stock after rollback: {$finalStock} KG\n";

echo "\n=== TEST COMPLETE ===\n";