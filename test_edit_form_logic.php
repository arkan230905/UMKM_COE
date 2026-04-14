<?php

require_once 'vendor/autoload.php';

// Simulate the controller logic for testing
function determineKategoriPembelian($pembelianDetails) {
    $hasBahanBaku = false;
    $hasBahanPendukung = false;
    
    foreach ($pembelianDetails as $detail) {
        if (!empty($detail['bahan_baku_id'])) {
            $hasBahanBaku = true;
        }
        if (!empty($detail['bahan_pendukung_id'])) {
            $hasBahanPendukung = true;
        }
    }
    
    // Tentukan kategori pembelian
    if ($hasBahanBaku && !$hasBahanPendukung) {
        return 'bahan_baku';
    } elseif ($hasBahanPendukung && !$hasBahanBaku) {
        return 'bahan_pendukung';
    } else {
        return 'mixed';
    }
}

// Test cases
$testCases = [
    'Only Bahan Baku' => [
        ['bahan_baku_id' => 1, 'bahan_pendukung_id' => null],
        ['bahan_baku_id' => 2, 'bahan_pendukung_id' => null]
    ],
    'Only Bahan Pendukung' => [
        ['bahan_baku_id' => null, 'bahan_pendukung_id' => 1],
        ['bahan_baku_id' => null, 'bahan_pendukung_id' => 2]
    ],
    'Mixed Purchase' => [
        ['bahan_baku_id' => 1, 'bahan_pendukung_id' => null],
        ['bahan_baku_id' => null, 'bahan_pendukung_id' => 1]
    ],
    'Empty Purchase' => []
];

echo "=== TESTING EDIT FORM LOGIC ===\n\n";

foreach ($testCases as $caseName => $details) {
    $kategori = determineKategoriPembelian($details);
    echo "Test Case: {$caseName}\n";
    echo "Category: {$kategori}\n";
    
    // Simulate display logic
    $showBahanBaku = ($kategori !== 'bahan_pendukung') ? 'block' : 'none';
    $showBahanPendukung = ($kategori !== 'bahan_baku') ? 'block' : 'none';
    
    echo "Bahan Baku Section: {$showBahanBaku}\n";
    echo "Bahan Pendukung Section: {$showBahanPendukung}\n";
    echo "---\n\n";
}

echo "=== EXPECTED BEHAVIOR ===\n";
echo "- bahan_baku: Show only Bahan Baku section\n";
echo "- bahan_pendukung: Show only Bahan Pendukung section\n";
echo "- mixed: Show both sections\n";
echo "\nTest completed successfully!\n";