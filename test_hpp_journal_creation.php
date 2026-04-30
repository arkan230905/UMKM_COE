<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing HPP Journal Creation...\n";

// Create a test penjualan to verify HPP journal creation
$pelanggan = \App\Models\Pelanggan::where('user_id', 1)->first();
$produk = \App\Models\Produk::where('user_id', 1)->first();

if (!$pelanggan || !$produk) {
    echo "Creating test data first...\n";
    
    // Create test pelanggan
    if (!$pelanggan) {
        $pelanggan = \App\Models\Pelanggan::create([
            'nama_pelanggan' => 'Test Customer',
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created test pelanggan\n";
    }
    
    // Create test produk
    if (!$produk) {
        $produk = \App\Models\Produk::create([
            'nama_produk' => 'Test Product',
            'harga' => 10000,
            'hpp' => 6000,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created test produk\n";
    }
}

echo "\nCreating test penjualan...\n";

// Create test penjualan
$testPenjualan = \App\Models\Penjualan::create([
    'tanggal' => now()->format('Y-m-d'),
    'pelanggan_id' => $pelanggan->id,
    'user_id' => 1,
    'subtotal' => 500000,
    'total_hpp' => 268600,
    'total' => 555000,
    'status' => 'completed',
    'created_at' => now(),
    'updated_at' => now(),
]);

// Create penjualan detail
$testDetail = \App\Models\PenjualanDetail::create([
    'penjualan_id' => $testPenjualan->id,
    'produk_id' => $produk->id,
    'jumlah' => 10,
    'harga_satuan' => $produk->harga,
    'subtotal' => 100000,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Test penjualan created with ID: " . $testPenjualan->id . "\n";

// Test HPP journal creation
echo "\nTesting HPP journal creation...\n";

try {
    $journalService = new \App\Services\JournalService();
    
    // Call the createJournalFromPenjualan method
    \App\Services\JournalService::createJournalFromPenjualan($testPenjualan);
    
    echo "Journal creation completed successfully!\n";
    
    // Check the created journal entries
    $jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $testPenjualan->id)->get();
    echo "Journal entries created: " . $jurnalEntries->count() . "\n";
    
    $hppEntries = 0;
    foreach ($jurnalEntries as $jurnal) {
        echo "  - " . $jurnal->coa->nama_akun . " | ";
        echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
        echo "\n";
        
        // Check if this is an HPP entry
        if (strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
            strpos($jurnal->coa->nama_akun, 'HPP') !== false ||
            strpos($jurnal->coa->kode_akun, '56') !== false) {
            $hppEntries++;
        }
    }
    
    echo "\nHPP Journal Entries Found: " . $hppEntries . "\n";
    
    if ($hppEntries > 0) {
        echo "SUCCESS: HPP journal entries are now being created!\n";
    } else {
        echo "ISSUE: HPP journal entries still not found\n";
    }
    
} catch (\Exception $e) {
    echo "Error creating journal: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Clean up test data
echo "\nCleaning up test data...\n";
\App\Models\JurnalUmum::where('referensi', 'penjualan#' . $testPenjualan->id)->delete();
$testDetail->delete();
$testPenjualan->delete();
echo "Test data cleaned up\n";

echo "\nHPP Journal Creation test completed!\n";
