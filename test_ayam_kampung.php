<?php

// Test script to verify Ayam Kampung data
echo "=== AYAM KAMPUNG STOCK TEST ===\n";

// Database values from user's query
$ayamKampungData = [
    'id' => 6,
    'kode_bahan' => 'BB006',
    'nama_bahan' => 'Ayam Kampung',
    'satuan_id' => 23,
    'stok' => 1140.0,
    'harga_satuan' => 45000.00
];

echo "Database Values:\n";
echo "ID: {$ayamKampungData['id']}\n";
echo "Code: {$ayamKampungData['kode_bahan']}\n";
echo "Name: {$ayamKampungData['nama_bahan']}\n";
echo "Stock: {$ayamKampungData['stok']}\n";
echo "Unit Price: Rp " . number_format($ayamKampungData['harga_satuan'], 2) . "\n";

// Calculate what the initial stock entry should be
$correctQty = (float)$ayamKampungData['stok'];
$correctUnitCost = (float)$ayamKampungData['harga_satuan'];
$correctTotalCost = $correctQty * $correctUnitCost;

echo "\nExpected Initial Stock Entry:\n";
echo "Date: 2026-04-01\n";
echo "Quantity: {$correctQty}\n";
echo "Unit Cost: Rp " . number_format($correctUnitCost, 2) . "\n";
echo "Total Value: Rp " . number_format($correctTotalCost, 2) . "\n";
echo "Item Type: material\n";

echo "\nThe fix will:\n";
echo "1. Check if initial_stock entry exists for item_id=6, item_type='material'\n";
echo "2. If exists but incorrect, update with correct values\n";
echo "3. If doesn't exist, create new entry with correct values\n";
echo "4. Ensure stock_layers table is also updated\n";

echo "\nTest completed!\n";