<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Debugging HPP Creation Process...\n\n";

// Simulate user login
\Illuminate\Support\Facades\Auth::loginUsingId(1);

// Get the sale
$sale = \App\Models\Penjualan::find(4);
if (!$sale) {
    echo "Sale ID 4 not found\n";
    exit;
}

echo "Sale Details:\n";
echo "ID: {$sale->id}\n";
echo "Nomor: {$sale->nomor_penjualan}\n";
echo "User ID: {$sale->user_id}\n";
echo "Total: " . number_format($sale->total ?? 0, 0, ',', '.') . "\n";
echo "Grand Total: " . number_format($sale->grand_total ?? 0, 0, ',', '.') . "\n\n";

// Check sale details
echo "Sale Details:\n";
if ($sale->details && $sale->details->count() > 0) {
    foreach ($sale->details as $detail) {
        $produk = $detail->produk;
        if ($produk) {
            echo "  Detail ID: {$detail->id}\n";
            echo "  Product: {$produk->nama_produk}\n";
            echo "  Product ID: {$produk->id}\n";
            echo "  Qty: {$detail->jumlah}\n";
            echo "  HPP: " . number_format($produk->hpp ?? 0, 0, ',', '.') . "\n";
            echo "  Harga Pokok: " . number_format($produk->harga_pokok ?? 0, 0, ',', '.') . "\n";
            echo "  Harga BOM: " . number_format($produk->harga_bom ?? 0, 0, ',', '.') . "\n";
            
            // Calculate HPP like the service does
            $hppPerUnit = (float)($produk->hpp ?? $produk->harga_pokok ?? $produk->harga_bom ?? 0);
            $totalHPP = round($hppPerUnit * $detail->jumlah);
            echo "  Calculated HPP per unit: " . number_format($hppPerUnit, 0, ',', '.') . "\n";
            echo "  Calculated Total HPP: " . number_format($totalHPP, 0, ',', '.') . "\n\n";
        }
    }
}

// Test the findCoaHpp method
echo "=== TESTING findCoaHpp METHOD ===\n";
$journalService = new \App\Services\JournalService();

if ($sale->details && $sale->details->count() > 0) {
    foreach ($sale->details as $detail) {
        $produk = $detail->produk;
        if ($produk && $detail->jumlah > 0) {
            echo "Testing findCoaHpp for: {$produk->nama_produk}\n";
            
            // Use reflection to call private method
            $reflection = new ReflectionClass($journalService);
            $method = $reflection->getMethod('findCoaHpp');
            $method->setAccessible(true);
            
            $coaHppKode = $method->invoke($journalService, $produk, 1);
            echo "  COA HPP Code: " . ($coaHppKode ?? 'NULL') . "\n";
            
            // Test findCoaPersediaan method
            $method2 = $reflection->getMethod('findCoaPersediaan');
            $method2->setAccessible(true);
            
            $coaPersediaanKode = $method2->invoke($journalService, $produk, 1);
            echo "  COA Persediaan Code: " . ($coaPersediaanKode ?? 'NULL') . "\n";
            
            if ($coaHppKode && $coaPersediaanKode) {
                echo "  Both COAs found - HPP journal should be created\n";
            } else {
                echo "  Missing COA(s) - HPP journal will be skipped\n";
            }
            echo "\n";
        }
    }
}

// Test createHPPLinesFromPenjualan method directly
echo "=== TESTING createHPPLinesFromPenjualan METHOD ===\n";

$reflection = new ReflectionClass($journalService);
$method = $reflection->getMethod('createHPPLinesFromPenjualan');
$method->setAccessible(true);

try {
    $hppLines = $method->invoke($journalService, $sale);
    echo "HPP Lines Created: " . count($hppLines) . "\n";
    
    foreach ($hppLines as $i => $line) {
        echo "  Line " . ($i + 1) . ":\n";
        echo "    Code: {$line['code']}\n";
        echo "    Debit: " . number_format($line['debit'], 0, ',', '.') . "\n";
        echo "    Credit: " . number_format($line['credit'], 0, ',', '.') . "\n";
        echo "    Memo: {$line['memo']}\n\n";
    }
} catch (Exception $e) {
    echo "Error in createHPPLinesFromPenjualan: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "HPP creation debug completed!\n";
