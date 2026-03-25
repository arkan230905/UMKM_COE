<?php

// Simulate form data to test which path is taken
$formData = [
    'tanggal' => '2026-03-26',
    'payment_method' => 'cash',
    'sumber_dana' => '1101',
    'produk_id' => [1], // Array with one item
    'jumlah' => [5],    // Array with one item
    'harga_satuan' => [50000],
    'diskon_persen' => [0]
];

echo "Testing form data path...\n";
echo "produk_id is array: " . (is_array($formData['produk_id']) ? 'YES' : 'NO') . "\n";
echo "produk_id value: " . json_encode($formData['produk_id']) . "\n";
echo "jumlah value: " . json_encode($formData['jumlah']) . "\n";

// This should go to multi-item path
if (is_array($formData['produk_id'])) {
    echo "Should use MULTI-ITEM path (stock movements validation)\n";
} else {
    echo "Should use SINGLE-ITEM path (product stok field validation)\n";
}