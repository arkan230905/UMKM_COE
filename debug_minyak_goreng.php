<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging Minyak Goreng conversion issue...\n\n";

try {
    // Get Minyak Goreng data
    $minyakGoreng = \App\Models\BahanPendukung::where('nama_bahan', 'LIKE', '%Minyak Goreng%')->first();
    
    if (!$minyakGoreng) {
        echo "❌ Minyak Goreng not found!\n";
        exit(1);
    }
    
    echo "=== MINYAK GORENG DATA ===\n";
    echo sprintf("ID: %d\n", $minyakGoreng->id);
    echo sprintf("Nama: %s\n", $minyakGoreng->nama_bahan);
    echo sprintf("Harga Satuan: Rp %s\n", number_format($minyakGoreng->harga_satuan, 2));
    echo sprintf("Harga Rata-rata: Rp %s\n", number_format($minyakGoreng->harga_rata_rata ?? 0, 2));
    echo sprintf("Satuan ID: %d\n", $minyakGoreng->satuan_id ?? 'NULL');
    
    // Load satuan relationships
    $minyakGoreng->load(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3']);
    
    echo "\n=== SATUAN BASE ===\n";
    if ($minyakGoreng->satuan) {
        echo sprintf("Nama: %s\n", $minyakGoreng->satuan->nama);
        echo sprintf("ID: %d\n", $minyakGoreng->satuan->id);
    } else {
        echo "❌ No base satuan found!\n";
    }
    
    echo "\n=== SUB SATUAN ===\n";
    
    if ($minyakGoreng->subSatuan1) {
        echo sprintf("Sub Satuan 1: %s (nilai: %s)\n", 
            $minyakGoreng->subSatuan1->nama, 
            $minyakGoreng->sub_satuan_1_nilai ?? 'NULL'
        );
    } else {
        echo "Sub Satuan 1: NULL\n";
    }
    
    if ($minyakGoreng->subSatuan2) {
        echo sprintf("Sub Satuan 2: %s (nilai: %s)\n", 
            $minyakGoreng->subSatuan2->nama, 
            $minyakGoreng->sub_satuan_2_nilai ?? 'NULL'
        );
    } else {
        echo "Sub Satuan 2: NULL\n";
    }
    
    if ($minyakGoreng->subSatuan3) {
        echo sprintf("Sub Satuan 3: %s (nilai: %s)\n", 
            $minyakGoreng->subSatuan3->nama, 
            $minyakGoreng->sub_satuan_3_nilai ?? 'NULL'
        );
    } else {
        echo "Sub Satuan 3: NULL\n";
    }
    
    // Test conversion
    echo "\n=== CONVERSION TEST ===\n";
    
    $service = new \App\Services\BiayaBahanConversionService();
    
    // Test case: 50 Mililiter
    $testJumlah = 50;
    $testSatuan = 'Mililiter';
    
    echo sprintf("Testing: %d %s\n", $testJumlah, $testSatuan);
    
    $result = $service->convertBahanPendukungToBase($minyakGoreng, $testJumlah, $testSatuan);
    
    echo sprintf("Qty Base: %s\n", $result['qty_base']);
    echo sprintf("Subtotal: Rp %s\n", number_format($result['subtotal'], 2));
    echo sprintf("Harga per Satuan: Rp %s\n", number_format($result['harga_per_satuan'], 2));
    
    // Manual calculation
    $harga = (float)($minyakGoreng->harga_rata_rata ?? $minyakGoreng->harga_satuan);
    $expectedSubtotal = 50 * 14; // 50ml x Rp 14/ml = Rp 700
    
    echo "\n=== MANUAL CALCULATION ===\n";
    echo sprintf("Harga base: Rp %s\n", number_format($harga, 2));
    echo sprintf("Expected subtotal (50ml x Rp 14): Rp %s\n", number_format($expectedSubtotal, 2));
    echo sprintf("Actual subtotal: Rp %s\n", number_format($result['subtotal'], 2));
    echo sprintf("Difference: Rp %s\n", number_format($result['subtotal'] - $expectedSubtotal, 2));
    
    // Check what's in the database for this specific BOM
    echo "\n=== CHECK DATABASE RECORDS ===\n";
    
    $ayamKetumbar = \App\Models\Produk::where('nama_produk', 'LIKE', '%Ayam Ketumbar%')->first();
    if ($ayamKetumbar) {
        $bomJobCosting = \App\Models\BomJobCosting::where('produk_id', $ayamKetumbar->id)->first();
        if ($bomJobCosting) {
            $pendukungDetail = \App\Models\BomJobBahanPendukung::where('bom_job_costing_id', $bomJobCosting->id)
                ->where('bahan_pendukung_id', $minyakGoreng->id)
                ->first();
            
            if ($pendukungDetail) {
                echo sprintf("Database Record:\n");
                echo sprintf("  Jumlah: %s\n", $pendukungDetail->jumlah);
                echo sprintf("  Satuan: %s\n", $pendukungDetail->satuan);
                echo sprintf("  Harga Satuan: Rp %s\n", number_format($pendukungDetail->harga_satuan, 2));
                echo sprintf("  Subtotal: Rp %s\n", number_format($pendukungDetail->subtotal, 2));
            } else {
                echo "❌ No database record found for this BOM\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
