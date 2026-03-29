<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Ayam Kampung Conversion ===\n";

try {
    // Get Ayam Kampung data
    $ayamKampung = App\Models\BahanBaku::where('nama_bahan', 'Ayam Kampung')->first();
    
    if (!$ayamKampung) {
        echo "❌ Ayam Kampung not found\n";
        exit(1);
    }
    
    echo "Checking: {$ayamKampung->nama_bahan}\n";
    echo "Main unit: " . ($ayamKampung->satuan->nama ?? 'N/A') . "\n";
    echo "Current stock: {$ayamKampung->stok}\n\n";
    
    echo "CURRENT CONVERSION DATA:\n";
    if ($ayamKampung->subSatuan1) {
        echo "Sub Unit 1: {$ayamKampung->subSatuan1->nama}\n";
        echo "- Quantity conversion: {$ayamKampung->sub_satuan_1_konversi}\n";
        echo "- Price conversion: {$ayamKampung->sub_satuan_1_nilai}\n";
    }
    
    if ($ayamKampung->subSatuan2) {
        echo "Sub Unit 2: {$ayamKampung->subSatuan2->nama}\n";
        echo "- Quantity conversion: {$ayamKampung->sub_satuan_2_konversi}\n";
        echo "- Price conversion: {$ayamKampung->sub_satuan_2_nilai}\n";
    }
    
    if ($ayamKampung->subSatuan3) {
        echo "Sub Unit 3: {$ayamKampung->subSatuan3->nama}\n";
        echo "- Quantity conversion: {$ayamKampung->sub_satuan_3_konversi}\n";
        echo "- Price conversion: {$ayamKampung->sub_satuan_3_nilai}\n";
    }
    
    echo "\nPROBLEM ANALYSIS:\n";
    echo "From screenshot: 40 Ekor = 40 Potong (WRONG!)\n";
    echo "Should be: 40 Ekor = 320 Potong (if 1 Ekor = 8 Potong)\n\n";
    
    echo "EXPECTED LOGIC:\n";
    echo "Base unit: Ekor\n";
    echo "1 Ekor Ayam Kampung should equal:\n";
    echo "- 8 Potong (cut into 8 pieces)\n";
    echo "- 1500 Gram (average weight 1.5 kg)\n";
    echo "- 1.5 Kilogram\n\n";
    
    echo "CURRENT PROBLEM:\n";
    echo "❌ Conversion ratios are 1:1 for all units\n";
    echo "❌ This makes no physical sense\n";
    echo "❌ 1 Ekor ≠ 1 Potong ≠ 1 Gram ≠ 1 Kg\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check completed ===\n";