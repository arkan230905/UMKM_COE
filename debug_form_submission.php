<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUGGING FORM SUBMISSION ===\n\n";

// Simulate the exact form data that would be sent
$formData = [
    'vendor_id' => '1',
    'tanggal' => '2026-03-26',
    'bank_id' => '1',
    'item_id' => ['2'], // Array with one item
    'tipe_item' => ['bahan_baku'], // Array with one type
    'jumlah' => ['10'], // Array with one quantity
    'satuan_pembelian' => ['6'], // Array with one unit
    'harga_satuan' => ['15000'], // Array with one price
    'subtotal' => ['150000'], // Array with one subtotal
    'faktor_konversi' => ['0.1667'], // Array with one conversion
    'biaya_kirim' => '0',
    'ppn_persen' => '0',
    'keterangan' => 'Test'
];

echo "Simulated form data:\n";
foreach ($formData as $key => $value) {
    if (is_array($value)) {
        echo "- {$key}: [" . implode(', ', $value) . "] (array with " . count($value) . " elements)\n";
    } else {
        echo "- {$key}: {$value}\n";
    }
}

echo "\n=== VALIDATION TEST ===\n";

// Test the validation logic from controller
$hasItems = false;
$hasBahanBaku = false;
$hasBahanPendukung = false;

if (is_array($formData['item_id']) && is_array($formData['tipe_item'])) {
    foreach ($formData['item_id'] as $index => $itemId) {
        if (!empty($itemId) && !empty($formData['tipe_item'][$index])) {
            $hasItems = true;
            if ($formData['tipe_item'][$index] === 'bahan_baku') {
                $hasBahanBaku = true;
            } elseif ($formData['tipe_item'][$index] === 'bahan_pendukung') {
                $hasBahanPendukung = true;
            }
            echo "✅ Item detected: ID {$itemId}, Type: {$formData['tipe_item'][$index]}\n";
        }
    }
}

echo "hasItems: " . ($hasItems ? 'true' : 'false') . "\n";
echo "hasBahanBaku: " . ($hasBahanBaku ? 'true' : 'false') . "\n";
echo "hasBahanPendukung: " . ($hasBahanPendukung ? 'true' : 'false') . "\n";

if ($hasItems) {
    echo "\n✅ Validation should pass\n";
    
    // Test validation rules
    $rules = [
        'vendor_id' => 'required|exists:vendors,id',
        'tanggal' => 'required|date',
        'bank_id' => 'required',
        'item_id' => 'required|array|min:1',
        'item_id.*' => 'required|integer|min:1',
        'tipe_item' => 'required|array|min:1',
        'tipe_item.*' => 'required|string|in:bahan_baku,bahan_pendukung',
        'jumlah' => 'required|array|min:1',
        'jumlah.*' => 'required|numeric|min:0.01',
        'satuan_pembelian' => 'required|array|min:1',
        'satuan_pembelian.*' => 'required|integer|min:1',
        'subtotal' => 'required|array|min:1',
        'subtotal.*' => 'required|numeric|min:0',
        'faktor_konversi' => 'required|array|min:1',
        'faktor_konversi.*' => 'required|numeric|min:0.0001',
        'harga_satuan' => 'required|array|min:1',
        'harga_satuan.*' => 'required|numeric|min:0',
    ];
    
    echo "\nTesting validation rules:\n";
    
    // Create a mock request
    $request = new \Illuminate\Http\Request();
    $request->merge($formData);
    
    try {
        $validator = \Illuminate\Support\Facades\Validator::make($formData, $rules);
        
        if ($validator->fails()) {
            echo "❌ Validation failed:\n";
            foreach ($validator->errors()->all() as $error) {
                echo "  - {$error}\n";
            }
        } else {
            echo "✅ All validation rules passed!\n";
        }
    } catch (\Exception $e) {
        echo "❌ Validation error: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "\n❌ No items detected - validation would fail\n";
}

echo "\n=== COMMON ISSUES ===\n";
echo "1. Make sure vendor is selected first\n";
echo "2. Make sure item is selected from dropdown\n";
echo "3. Make sure jumlah > 0\n";
echo "4. Make sure satuan_pembelian is selected\n";
echo "5. Make sure harga_satuan > 0\n";
echo "6. Make sure JavaScript calculates subtotal correctly\n";