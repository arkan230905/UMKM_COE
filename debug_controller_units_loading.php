<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug Controller Units Loading ===" . PHP_EOL;

// Get Ayam Potong with relationships
$item = DB::table('bahan_bakus')->where('id', 1)->first();

echo "Ayam Potong Raw Data:" . PHP_EOL;
echo "sub_satuan_1_id: " . ($item->sub_satuan_1_id ?? 'NULL') . PHP_EOL;
echo "sub_satuan_2_id: " . ($item->sub_satuan_2_id ?? 'NULL') . PHP_EOL;
echo "sub_satuan_3_id: " . ($item->sub_satuan_3_id ?? 'NULL') . PHP_EOL;

// Check if the relationships exist in the model
echo PHP_EOL . "=== Checking Model Relationships ===" . PHP_EOL;

// Try to load the model with relationships
try {
    $bahanBaku = \App\Models\BahanBaku::with(['satuan', 'subSatuan1', 'subSatuan2', 'subSatuan3'])->find(1);
    
    echo "Model loaded successfully" . PHP_EOL;
    echo "Main Satuan: " . ($bahanBaku->satuan ? $bahanBaku->satuan->nama : 'NULL') . PHP_EOL;
    echo "Sub Satuan 1: " . ($bahanBaku->subSatuan1 ? $bahanBaku->subSatuan1->nama : 'NULL') . PHP_EOL;
    echo "Sub Satuan 2: " . ($bahanBaku->subSatuan2 ? $bahanBaku->subSatuan2->nama : 'NULL') . PHP_EOL;
    echo "Sub Satuan 3: " . ($bahanBaku->subSatuan3 ? $bahanBaku->subSatuan3->nama : 'NULL') . PHP_EOL;
    
    // Simulate controller logic exactly
    echo PHP_EOL . "=== Simulating Controller Logic ===" . PHP_EOL;
    
    $availableSatuans = [];
    $mainSatuan = $bahanBaku->satuan;
    
    if ($mainSatuan) {
        $availableSatuans[] = [
            'id' => $mainSatuan->id,
            'nama' => $mainSatuan->nama ?? $mainSatuan->nama_satuan ?? 'Unit',
            'is_primary' => true,
            'conversion_to_primary' => 1,
            'price_conversion' => 1
        ];
        
        // Add sub satuan 1
        if (isset($bahanBaku->sub_satuan_1_id) && $bahanBaku->sub_satuan_1_id && $bahanBaku->subSatuan1) {
            $quantityRatio = (float)($bahanBaku->sub_satuan_1_nilai ?? 1);
            $priceRatio = (float)($bahanBaku->sub_satuan_1_nilai ?? 1);
            $availableSatuans[] = [
                'id' => $bahanBaku->subSatuan1->id,
                'nama' => $bahanBaku->subSatuan1->nama ?? $bahanBaku->subSatuan1->nama_satuan ?? 'Sub Unit 1',
                'is_primary' => false,
                'conversion_to_primary' => $quantityRatio,
                'price_conversion' => $priceRatio
            ];
            echo "Added Sub Satuan 1: " . $bahanBaku->subSatuan1->nama . " (conversion: " . $quantityRatio . ")" . PHP_EOL;
        }
        
        // Add sub satuan 2
        if (isset($bahanBaku->sub_satuan_2_id) && $bahanBaku->sub_satuan_2_id && $bahanBaku->subSatuan2) {
            $quantityRatio = (float)($bahanBaku->sub_satuan_2_nilai ?? 1);
            $priceRatio = (float)($bahanBaku->sub_satuan_2_nilai ?? 1);
            $availableSatuans[] = [
                'id' => $bahanBaku->subSatuan2->id,
                'nama' => $bahanBaku->subSatuan2->nama ?? $bahanBaku->subSatuan2->nama_satuan ?? 'Sub Unit 2',
                'is_primary' => false,
                'conversion_to_primary' => $quantityRatio,
                'price_conversion' => $priceRatio
            ];
            echo "Added Sub Satuan 2: " . $bahanBaku->subSatuan2->nama . " (conversion: " . $quantityRatio . ")" . PHP_EOL;
        }
        
        // Add sub satuan 3
        if (isset($bahanBaku->sub_satuan_3_id) && $bahanBaku->sub_satuan_3_id && $bahanBaku->subSatuan3) {
            $quantityRatio = (float)($bahanBaku->sub_satuan_3_nilai ?? 1);
            $priceRatio = (float)($bahanBaku->sub_satuan_3_nilai ?? 1);
            $availableSatuans[] = [
                'id' => $bahanBaku->subSatuan3->id,
                'nama' => $bahanBaku->subSatuan3->nama ?? $bahanBaku->subSatuan3->nama_satuan ?? 'Sub Unit 3',
                'is_primary' => false,
                'conversion_to_primary' => $quantityRatio,
                'price_conversion' => $priceRatio
            ];
            echo "Added Sub Satuan 3: " . $bahanBaku->subSatuan3->nama . " (conversion: " . $quantityRatio . ")" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "Total Available Satuans: " . count($availableSatuans) . PHP_EOL;
    foreach ($availableSatuans as $satuan) {
        echo "- " . $satuan['nama'] . " (ID: " . $satuan['id'] . ", conversion: " . $satuan['conversion_to_primary'] . ")" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error loading model: " . $e->getMessage() . PHP_EOL;
}
