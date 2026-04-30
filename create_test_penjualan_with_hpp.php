<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating test penjualan with HPP verification...\n";

// Get necessary data
$pelanggan = \App\Models\Pelanggan::where('user_id', 1)->first();
$produk = \App\Models\Produk::where('user_id', 1)->first();

if (!$pelanggan) {
    echo "Creating test pelanggan...\n";
    $pelanggan = \App\Models\Pelanggan::create([
        'kode_pelanggan' => 'CUS' . date('ymd') . '001',
        'nama_pelanggan' => 'Test Customer',
        'user_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created test pelanggan: {$pelanggan->nama_pelanggan}\n";
}

if (!$produk) {
    echo "Creating test produk...\n";
    $produk = \App\Models\Produk::create([
        'nama_produk' => 'Test Product',
        'harga' => 10000,
        'hpp' => 6000,
        'user_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created test produk: {$produk->nama_produk}\n";
}

echo "\n=== Test Data Ready ===\n";
echo "Pelanggan: {$pelanggan->nama_pelanggan}\n";
echo "Produk: {$produk->nama_produk}\n";
echo "Harga: Rp " . number_format($produk->harga, 0, ',', '.') . "\n";
echo "HPP: Rp " . number_format($produk->hpp, 0, ',', '.') . "\n";

// Create test penjualan
echo "\n=== Creating Test Penjualan ===\n";

try {
    $testPenjualan = \App\Models\Penjualan::create([
        'tanggal' => now()->format('Y-m-d'),
        'pelanggan_id' => $pelanggan->id,
        'user_id' => 1,
        'subtotal' => 50000,
        'total_hpp' => 30000,
        'total_ppn' => 5500,
        'total' => 55500,
        'status' => 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "Test penjualan created with ID: {$testPenjualan->id}\n";

    // Create penjualan detail
    $testDetail = \App\Models\PenjualanDetail::create([
        'penjualan_id' => $testPenjualan->id,
        'produk_id' => $produk->id,
        'jumlah' => 5,
        'harga_satuan' => $produk->harga,
        'subtotal' => 50000,
        'hpp' => $produk->hpp,
        'total_hpp' => $produk->hpp * 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "Penjualan detail created\n";

    // Create journal entries
    echo "\n=== Creating Journal Entries ===\n";
    \App\Services\JournalService::createJournalFromPenjualan($testPenjualan);
    echo "Journal creation completed\n";

    // Check the created journal entries
    echo "\n=== Journal Entries Created ===\n";
    $jurnalEntries = \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $testPenjualan->id)->get();
    echo "Total journal entries: " . $jurnalEntries->count() . "\n\n";

    $hasHpp = false;
    $hasPersediaan = false;

    foreach ($jurnalEntries as $jurnal) {
        echo "COA: " . $jurnal->coa->nama_akun . " (" . $jurnal->coa->kode_akun . ")\n";
        echo ($jurnal->debit > 0 ? "Debit: Rp " . number_format($jurnal->debit, 0, ',', '.') : "Kredit: Rp " . number_format($jurnal->kredit, 0, ',', '.'));
        echo "\n";
        echo "Keterangan: " . $jurnal->keterangan . "\n";
        echo "---\n";
        
        if (strpos($jurnal->coa->nama_akun, 'Harga Pokok Penjualan') !== false || 
            strpos($jurnal->coa->kode_akun, '56') !== false) {
            $hasHpp = true;
        }
        
        if (strpos($jurnal->coa->nama_akun, 'Persediaan Barang Jadi') !== false ||
            strpos($jurnal->coa->kode_akun, '116') !== false) {
            $hasPersediaan = true;
        }
    }

    echo "\n=== HPP Verification ===\n";
    echo "HPP entries found: " . ($hasHpp ? "YES" : "NO") . "\n";
    echo "Persediaan entries found: " . ($hasPersediaan ? "YES" : "NO") . "\n";

    if ($hasHpp && $hasPersediaan) {
        echo "SUCCESS: HPP journal entries are working!\n";
        echo "The issue might be with existing penjualan records.\n";
        echo "New penjualan records will have HPP entries.\n";
    } else {
        echo "ISSUE: HPP journal entries still not working.\n";
        echo "Need further investigation.\n";
    }

    // Clean up test data
    echo "\n=== Cleaning Up Test Data ===\n";
    \App\Models\JurnalUmum::where('referensi', 'penjualan#' . $testPenjualan->id)->delete();
    $testDetail->delete();
    $testPenjualan->delete();
    echo "Test data cleaned up\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest penjualan with HPP verification completed!\n";
