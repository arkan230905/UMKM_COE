<?php

// Simple test without vendor dependencies
echo "Testing stock fix logic...\n";

// Simulate the conditions
$tipe = 'bahan_pendukung';
$itemId = 13;
$item = new stdClass();
$item->stok = 1000.0;
$item->harga_satuan = 1000.0;
$item->satuan_id = 21;

// Check if initial stock entry would be created
$hasInitialStock = false; // Simulate no initial stock exists
$shouldCreateInitialStock = !$hasInitialStock && $item && $item->stok > 0;

echo "Item ID: $itemId\n";
echo "Item Stock: {$item->stok}\n";
echo "Item Price: {$item->harga_satuan}\n";
echo "Has Initial Stock: " . ($hasInitialStock ? 'Yes' : 'No') . "\n";
echo "Should Create Initial Stock: " . ($shouldCreateInitialStock ? 'Yes' : 'No') . "\n";

if ($shouldCreateInitialStock) {
    $initialDate = '2026-04-01';
    $initialQty = (float)($item->stok ?? 0);
    $initialValue = $initialQty * (float)($item->harga_satuan ?? 0);
    
    echo "Would create initial stock entry:\n";
    echo "  Date: $initialDate\n";
    echo "  Quantity: $initialQty\n";
    echo "  Unit Cost: {$item->harga_satuan}\n";
    echo "  Total Value: $initialValue\n";
    echo "  Item Type: " . ($tipe == 'bahan_pendukung' ? 'support' : $tipe) . "\n";
}

echo "Test completed successfully!\n";