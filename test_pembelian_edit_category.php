<?php

// Test pembelian edit category logic
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pembelian;

echo "Testing Pembelian Edit Category Logic\n";
echo "====================================\n\n";

// Get all pembelians to test
$pembelians = Pembelian::with(['details'])->take(5)->get();

foreach ($pembelians as $pembelian) {
    echo "Pembelian ID: {$pembelian->id}\n";
    echo "Nomor: {$pembelian->nomor_pembelian}\n";
    
    // Check what types of items this pembelian has
    $hasBahanBaku = $pembelian->details->where('bahan_baku_id', '!=', null)->count() > 0;
    $hasBahanPendukung = $pembelian->details->where('bahan_pendukung_id', '!=', null)->count() > 0;
    
    // Determine category
    $kategoriPembelian = 'mixed';
    if ($hasBahanBaku && !$hasBahanPendukung) {
        $kategoriPembelian = 'bahan_baku';
    } elseif ($hasBahanPendukung && !$hasBahanBaku) {
        $kategoriPembelian = 'bahan_pendukung';
    }
    
    echo "Has Bahan Baku: " . ($hasBahanBaku ? 'Yes' : 'No') . "\n";
    echo "Has Bahan Pendukung: " . ($hasBahanPendukung ? 'Yes' : 'No') . "\n";
    echo "Category: {$kategoriPembelian}\n";
    
    // Show what sections should be visible
    echo "Sections to show:\n";
    if ($kategoriPembelian === 'bahan_baku') {
        echo "  ✅ Bahan Baku section only\n";
        echo "  ❌ Bahan Pendukung section hidden\n";
    } elseif ($kategoriPembelian === 'bahan_pendukung') {
        echo "  ❌ Bahan Baku section hidden\n";
        echo "  ✅ Bahan Pendukung section only\n";
    } else {
        echo "  ✅ Both Bahan Baku and Bahan Pendukung sections\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

echo "EXPECTED BEHAVIOR:\n";
echo "==================\n";
echo "• If pembelian only has bahan_baku items → Show only Bahan Baku section\n";
echo "• If pembelian only has bahan_pendukung items → Show only Bahan Pendukung section\n";
echo "• If pembelian has both types → Show both sections\n";
echo "• If pembelian has no items → Show both sections (default)\n";