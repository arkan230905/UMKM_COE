<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING ACTUAL STOCK REPORT FINAL PRICES ===\n\n";

// Function to get actual stock report data
function getActualStockReportData($itemType, $itemId, $itemName) {
    try {
        echo "🔍 Checking {$itemName} (ID: {$itemId}, Type: {$itemType})\n";
        
        $request = new \Illuminate\Http\Request([
            'tipe' => $itemType === 'support' ? 'bahan_pendukung' : 'material',
            'item_id' => $itemId
        ]);

        $controller = new \App\Http\Controllers\LaporanController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('stok');
        $method->setAccessible(true);
        
        $response = $method->invoke($controller, $request);
        $viewData = $response->getData();
        
        if (!isset($viewData['dailyStock']) || empty($viewData['dailyStock'])) {
            echo "   ❌ No stock data found\n\n";
            return null;
        }

        $dailyStock = $viewData['dailyStock'];
        
        echo "   📊 Stock Transactions Found: " . count($dailyStock) . "\n";
        
        // Show all transactions
        foreach ($dailyStock as $index => $transaction) {
            echo "   Transaction " . ($index + 1) . ":\n";
            echo "     Date: {$transaction['tanggal']}\n";
            echo "     Ref Type: {$transaction['ref_type']}\n";
            echo "     Saldo Awal: Qty={$transaction['saldo_awal_qty']}, Nilai=Rp" . number_format($transaction['saldo_awal_nilai'], 2) . "\n";
            echo "     Pembelian: Qty={$transaction['pembelian_qty']}, Nilai=Rp" . number_format($transaction['pembelian_nilai'], 2) . "\n";
            echo "     Produksi: Qty={$transaction['produksi_qty']}, Nilai=Rp" . number_format($transaction['produksi_nilai'], 2) . "\n";
            echo "     Saldo Akhir: Qty={$transaction['saldo_akhir_qty']}, Nilai=Rp" . number_format($transaction['saldo_akhir_nilai'], 2) . "\n";
            
            if ($transaction['saldo_akhir_qty'] > 0) {
                $finalPrice = $transaction['saldo_akhir_nilai'] / $transaction['saldo_akhir_qty'];
                echo "     💰 Final Unit Price: Rp " . number_format($finalPrice, 2) . "\n";
            }
            echo "     ---\n";
        }
        
        // Get final price from last transaction
        $lastTransaction = end($dailyStock);
        
        if ($lastTransaction && $lastTransaction['saldo_akhir_qty'] > 0) {
            $finalPrice = $lastTransaction['saldo_akhir_nilai'] / $lastTransaction['saldo_akhir_qty'];
            echo "   🎯 FINAL CALCULATED PRICE: Rp " . number_format($finalPrice, 2) . " per unit\n";
            return $finalPrice;
        } else {
            echo "   ❌ No final stock available\n";
            return null;
        }

    } catch (\Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
        return null;
    }
}

// Check all items used in BOM
$items = [
    // Bahan Pendukung
    ['type' => 'support', 'id' => 16, 'name' => 'Tepung Terigu'],
    ['type' => 'support', 'id' => 17, 'name' => 'Tepung Maizena'],
    ['type' => 'support', 'id' => 18, 'name' => 'Lada'],
    ['type' => 'support', 'id' => 21, 'name' => 'Bubuk Bawang Putih'],
    ['type' => 'support', 'id' => 13, 'name' => 'Air'],
    ['type' => 'support', 'id' => 14, 'name' => 'Minyak Goreng'],
    ['type' => 'support', 'id' => 22, 'name' => 'Kemasan'],
    
    // Bahan Baku
    ['type' => 'material', 'id' => 6, 'name' => 'Ayam Kampung'],
];

foreach ($items as $item) {
    echo str_repeat("=", 80) . "\n";
    $finalPrice = getActualStockReportData($item['type'], $item['id'], $item['name']);
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "SUMMARY: Check the final calculated prices above and compare with BOM prices\n";