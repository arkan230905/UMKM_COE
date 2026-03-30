<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing All Conversion Data ===\n";

try {
    DB::beginTransaction();
    
    // Fix Ayam Kampung (Base: Ekor)
    $ayamKampung = App\Models\BahanBaku::where('nama_bahan', 'Ayam Kampung')->first();
    if ($ayamKampung) {
        echo "Fixing Ayam Kampung (Base: Ekor)...\n";
        
        // 1 Ekor = 8 Potong, 1.5 Kg, 1500 Gram
        $ayamKampung->sub_satuan_1_konversi = 8;    // 1 Ekor = 8 Potong
        $ayamKampung->sub_satuan_2_konversi = 1.5;  // 1 Ekor = 1.5 Kg  
        $ayamKampung->sub_satuan_3_konversi = 1500; // 1 Ekor = 1500 Gram
        
        // Price conversions (already correct from nilai field)
        // sub_satuan_1_nilai = 8 (price per potong = price per ekor / 8)
        // sub_satuan_2_nilai = 1.5 (price per kg = price per ekor / 1.5)
        // sub_satuan_3_nilai = 1500 (price per gram = price per ekor / 1500)
        
        $ayamKampung->save();
        echo "✅ Ayam Kampung: 1 Ekor = 8 Potong = 1.5 Kg = 1500 Gram\n";
    }
    
    // Fix Ayam Potong (Base: Kilogram) - already done but let's verify
    $ayamPotong = App\Models\BahanBaku::where('nama_bahan', 'Ayam Potong')->first();
    if ($ayamPotong) {
        echo "Verifying Ayam Potong (Base: Kilogram)...\n";
        
        // Should be: 1 Kg = 1000 Gram, 4 Potong
        if ($ayamPotong->sub_satuan_1_konversi != 1000) {
            $ayamPotong->sub_satuan_1_konversi = 1000; // 1 Kg = 1000 Gram
        }
        if ($ayamPotong->sub_satuan_2_konversi != 4) {
            $ayamPotong->sub_satuan_2_konversi = 4;    // 1 Kg = 4 Potong
        }
        
        $ayamPotong->save();
        echo "✅ Ayam Potong: 1 Kg = 1000 Gram = 4 Potong\n";
    }
    
    // Fix Bebek if exists
    $bebek = App\Models\BahanBaku::where('nama_bahan', 'Bebek')->first();
    if ($bebek) {
        echo "Fixing Bebek...\n";
        
        // Check what's the base unit
        $baseUnit = $bebek->satuan->nama ?? 'Unknown';
        echo "Bebek base unit: {$baseUnit}\n";
        
        if ($baseUnit == 'Ekor') {
            // 1 Ekor Bebek = 10 Potong, 2 Kg, 2000 Gram (bebek lebih besar dari ayam)
            $bebek->sub_satuan_1_konversi = 10;   // Potong
            $bebek->sub_satuan_2_konversi = 2;    // Kg
            $bebek->sub_satuan_3_konversi = 2000; // Gram
            echo "✅ Bebek: 1 Ekor = 10 Potong = 2 Kg = 2000 Gram\n";
        } elseif ($baseUnit == 'Kilogram') {
            // 1 Kg Bebek = 1000 Gram, 5 Potong
            $bebek->sub_satuan_1_konversi = 1000; // Gram
            $bebek->sub_satuan_2_konversi = 5;    // Potong
            echo "✅ Bebek: 1 Kg = 1000 Gram = 5 Potong\n";
        }
        
        $bebek->save();
    }
    
    // Check other bahan baku
    $otherBahan = App\Models\BahanBaku::whereNotIn('nama_bahan', ['Ayam Kampung', 'Ayam Potong', 'Bebek'])->get();
    
    foreach ($otherBahan as $bahan) {
        echo "\nChecking {$bahan->nama_bahan}...\n";
        $baseUnit = $bahan->satuan->nama ?? 'Unknown';
        echo "Base unit: {$baseUnit}\n";
        
        $needsUpdate = false;
        
        // Fix common conversion patterns
        if ($bahan->subSatuan1) {
            $subUnit1 = $bahan->subSatuan1->nama;
            echo "Sub unit 1: {$subUnit1}\n";
            
            // Common conversions
            if ($baseUnit == 'Kilogram' && $subUnit1 == 'Gram') {
                $bahan->sub_satuan_1_konversi = 1000;
                $needsUpdate = true;
                echo "- Fixed: 1 Kg = 1000 Gram\n";
            } elseif ($baseUnit == 'Liter' && $subUnit1 == 'Mililiter') {
                $bahan->sub_satuan_1_konversi = 1000;
                $needsUpdate = true;
                echo "- Fixed: 1 Liter = 1000 ml\n";
            } elseif ($baseUnit == 'Ekor' && $subUnit1 == 'Potong') {
                $bahan->sub_satuan_1_konversi = 6; // Default: 1 ekor = 6 potong
                $needsUpdate = true;
                echo "- Fixed: 1 Ekor = 6 Potong (default)\n";
            }
        }
        
        if ($bahan->subSatuan2) {
            $subUnit2 = $bahan->subSatuan2->nama;
            echo "Sub unit 2: {$subUnit2}\n";
            
            if ($baseUnit == 'Ekor' && $subUnit2 == 'Kilogram') {
                $bahan->sub_satuan_2_konversi = 1.2; // Default: 1 ekor = 1.2 kg
                $needsUpdate = true;
                echo "- Fixed: 1 Ekor = 1.2 Kg (default)\n";
            }
        }
        
        if ($needsUpdate) {
            $bahan->save();
            echo "✅ Updated {$bahan->nama_bahan}\n";
        } else {
            echo "- No standard conversions to fix\n";
        }
    }
    
    DB::commit();
    
    echo "\n🎉 All conversions fixed!\n";
    echo "\nNow the stock report should show:\n";
    echo "- 40 Ekor Ayam Kampung = 320 Potong = 60 Kg = 60,000 Gram\n";
    echo "- Quantities will make physical sense!\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Fix completed ===\n";