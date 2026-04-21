<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test Kartu Stok Fix ===\n";

try {
    // Test StockService
    $stockService = new \App\Services\StockService();
    
    // Test untuk Ayam Potong (ID 2)
    echo "Testing Ayam Potong (ID 2)...\n";
    $report = $stockService->getStockReport(2, 'bahan_baku');
    
    echo "Entries found: " . count($report['entries']) . "\n";
    
    foreach ($report['entries'] as $entry) {
        if ($entry['ref_type'] === 'production') {
            echo "Production entry found:\n";
            echo "- Tanggal: " . $entry['tanggal'] . "\n";
            echo "- Keterangan: " . $entry['keterangan'] . "\n";
            echo "- Qty Keluar: " . $entry['qty_keluar'] . "\n";
            echo "- Qty as Input: " . ($entry['qty_as_input'] ?? 'null') . "\n";
            echo "- Satuan as Input: " . ($entry['satuan_as_input'] ?? 'null') . "\n";
            echo "\n";
        }
    }
    
    echo "✅ Test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}