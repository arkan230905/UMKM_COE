<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Return Data ===" . PHP_EOL;

// Check if there are any returns in the database
try {
    $salesReturns = \App\Models\SalesReturn::with(['penjualan', 'items.produk'])->orderBy('return_date', 'desc')->get();
    echo 'Total SalesReturns found: ' . $salesReturns->count() . PHP_EOL;
    
    if ($salesReturns->count() > 0) {
        echo "Sample return data:" . PHP_EOL;
        foreach ($salesReturns->take(3) as $retur) {
            echo "- ID: {$retur->id}, Date: {$retur->return_date}, Penjualan ID: {$retur->penjualan_id}, Status: {$retur->status}" . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "Error checking SalesReturns: " . $e->getMessage() . PHP_EOL;
}

// Also check alternative models
try {
    $returPenjualan = \App\Models\ReturPenjualan::all();
    echo 'Total ReturPenjualan found: ' . $returPenjualan->count() . PHP_EOL;
} catch (Exception $e) {
    echo "Error checking ReturPenjualan: " . $e->getMessage() . PHP_EOL;
}

// Check if there are any return-related tables
$tables = ['sales_returns', 'retur_penjualans', 'retur_details'];
foreach ($tables as $table) {
    try {
        $count = \DB::table($table)->count();
        echo "Table $table: $count records" . PHP_EOL;
    } catch (Exception $e) {
        echo "Table $table: Not found or error" . PHP_EOL;
    }
}

// Check recent sales to see if any have returns
echo PHP_EOL . "=== Recent Sales ===" . PHP_EOL;
$recentSales = \App\Models\Penjualan::with('returs')->orderBy('tanggal', 'desc')->take(5)->get();
foreach ($recentSales as $sale) {
    $hasRetur = $sale->returs()->exists();
    echo "Sale ID {$sale->id} ({$sale->nomor_penjualan}): " . ($hasRetur ? "HAS RETURNS" : "No returns") . PHP_EOL;
}
