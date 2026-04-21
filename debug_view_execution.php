<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debug View Execution for Gram Unit ===" . PHP_EOL;

// Get actual data that LaporanController sends
echo "1. Getting actual controller data..." . PHP_EOL;

// Simulate the LaporanController stok method logic
$item = DB::table('bahan_bakus')->where('id', 1)->first();
$tipe = 'material';

// Get stock movements
$movements = DB::table('stock_movements')
    ->where('item_type', $tipe)
    ->where('item_id', 1)
    ->orderBy('tanggal')
    ->get();

// Process movements like controller does
$dailyStock = [];
$runningQty = 0;
$runningNilai = 0;

foreach ($movements as $m) {
    $dateStr = is_string($m->tanggal) ? $m->tanggal : $m->tanggal->format('Y-m-d');
    
    // Process like controller does
    if ($m->direction === 'out') {
        if ($m->ref_type === 'production' && $m->qty_as_input && $m->satuan_as_input) {
            $dailyOutQty = (float)$m->qty; // Use converted qty (FIXED)
        } else {
            $dailyOutQty = (float)$m->qty;
        }
        $dailyOutNilai = (float)($m->total_cost ?? 0);
    } else {
        $dailyOutQty = 0;
        $dailyOutNilai = 0;
    }
    
    // Update running totals
    $runningQty += 0 - $dailyOutQty; // Production reduces stock
    $runningNilai += 0 - $dailyOutNilai;
    
    // Add to dailyStock like controller
    $dailyStock[] = [
        'tanggal' => $dateStr,
        'saldo_awal_qty' => 0,
        'saldo_awal_nilai' => 0,
        'pembelian_qty' => 0,
        'pembelian_nilai' => 0,
        'penjualan_qty' => 0,
        'penjualan_nilai' => 0,
        'produksi_qty' => $dailyOutQty,
        'produksi_nilai' => $dailyOutNilai,
        'saldo_akhir_qty' => $runningQty,
        'saldo_akhir_nilai' => $runningNilai,
        'ref_type' => $m->ref_type,
        'ref_id' => $m->ref_id,
        'qty_as_input' => $m->qty_as_input,
        'satuan_as_input' => $m->satuan_as_input
    ];
}

echo "Controller processed " . count($dailyStock) . " transactions" . PHP_EOL;

// Get available units like controller does
$availableSatuans = [];
$mainSatuan = DB::table('satuans')->where('id', $item->satuan_id)->first();

if ($mainSatuan) {
    $availableSatuans[] = [
        'id' => $mainSatuan->id,
        'nama' => $mainSatuan->nama,
        'is_primary' => true,
        'conversion_to_primary' => 1,
        'price_conversion' => 1
    ];
    
    // Add sub satuan 2 (Gram)
    if ($item->sub_satuan_2_id) {
        $subSatuan = DB::table('satuans')->where('id', $item->sub_satuan_2_id)->first();
        if ($subSatuan) {
            $quantityRatio = (float)($item->sub_satuan_2_nilai ?? 1);
            $availableSatuans[] = [
                'id' => $subSatuan->id,
                'nama' => $subSatuan->nama,
                'is_primary' => false,
                'conversion_to_primary' => $quantityRatio,
                'price_conversion' => $quantityRatio
            ];
        }
    }
}

echo "Available units: " . count($availableSatuans) . PHP_EOL;
foreach ($availableSatuans as $satuan) {
    echo "- " . $satuan['nama'] . " (conversion: " . $satuan['conversion_to_primary'] . ")" . PHP_EOL;
}

// Now simulate the exact view logic for Gram unit
echo PHP_EOL . "2. Simulating exact view logic for Gram..." . PHP_EOL;

// Find Gram unit
$gramUnit = null;
foreach ($availableSatuans as $satuan) {
    if ($satuan['nama'] === 'Potong') {
        $gramUnit = [
            'id' => $satuan['id'],
            'name' => $satuan['nama'],
            'is_primary' => $satuan['is_primary'],
            'conversion' => $satuan['conversion_to_primary'],
            'price_conversion' => $satuan['price_conversion']
        ];
        break;
    }
}

if (!$gramUnit) {
    echo "ERROR: Gram unit not found!" . PHP_EOL;
    exit;
}

echo "Processing for unit: " . $gramUnit['name'] . PHP_EOL;

// Process each transaction like the view does
$stockData = [];
foreach ($dailyStock as $transaction) {
    echo PHP_EOL . "Processing transaction: " . $transaction['ref_type'] . " on " . $transaction['tanggal'] . PHP_EOL;
    echo "  produksi_qty: " . $transaction['produksi_qty'] . PHP_EOL;
    echo "  qty_as_input: " . ($transaction['qty_as_input'] ?? 'NULL') . PHP_EOL;
    echo "  satuan_as_input: " . ($transaction['satuan_as_input'] ?? 'NULL') . PHP_EOL;
    
    // Initialize production variables (like the fix)
    $convertedProduksiQty = 0;
    $convertedProduksiHarga = 0;
    
    // Apply view logic
    if ($transaction['ref_type'] === 'production' && isset($transaction['qty_as_input']) && isset($transaction['satuan_as_input'])) {
        $originalQty = (float)$transaction['qty_as_input'];
        $originalSatuan = $transaction['satuan_as_input'];
        
        echo "  Original Qty: " . $originalQty . " " . $originalSatuan . PHP_EOL;
        echo "  Target Unit: " . $gramUnit['name'] . PHP_EOL;
        
        if (strtolower($originalSatuan) === strtolower($gramUnit['name'])) {
            $convertedProduksiQty = $originalQty;
            echo "  Logic: Original unit match - Result: " . $convertedProduksiQty . PHP_EOL;
        } elseif ($gramUnit['is_primary']) {
            $convertedProduksiQty = $transaction['produksi_qty'];
            echo "  Logic: Primary unit - Result: " . $convertedProduksiQty . PHP_EOL;
        } else {
            $conversionMultiplier = 1 / $gramUnit['conversion'];
            $convertedProduksiQty = $transaction['produksi_qty'] * $conversionMultiplier;
            echo "  Logic: Convert from kg - " . $transaction['produksi_qty'] . " × " . $conversionMultiplier . PHP_EOL;
            echo "  Result: " . $convertedProduksiQty . PHP_EOL;
        }
    } else {
        $convertedProduksiQty = $transaction['produksi_qty'] * $gramUnit['conversion'];
        echo "  Logic: Standard conversion - Result: " . $convertedProduksiQty . PHP_EOL;
    }
    
    echo "  Final convertedProduksiQty: " . $convertedProduksiQty . PHP_EOL;
    
    // Add to stockData
    $stockData[] = [
        'produksi_qty' => $convertedProduksiQty,
        'produksi_harga' => $convertedProduksiHarga,
        'produksi_total' => $transaction['produksi_nilai']
    ];
}

echo PHP_EOL . "3. Final Results:" . PHP_EOL;
foreach ($stockData as $row) {
    echo "Production Qty: " . $row['produksi_qty'] . PHP_EOL;
    echo "Condition (qty != 0): " . ($row['produksi_qty'] != 0 ? "PASS" : "FAIL") . PHP_EOL;
    echo "---" . PHP_EOL;
}
