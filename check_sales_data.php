<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Sales Data...\n\n";

// Check all sales (not just user 1)
$sales = \App\Models\Penjualan::orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

echo "All Sales Found: {$sales->count()}\n";

foreach ($sales as $sale) {
    echo "Sale ID: {$sale->id}\n";
    echo "User ID: {$sale->user_id}\n";
    echo "Nomor: {$sale->nomor_penjualan}\n";
    echo "Tanggal: {$sale->tanggal}\n";
    echo "Total: " . number_format($sale->total ?? 0, 0, ',', '.') . "\n";
    echo "Grand Total: " . number_format($sale->grand_total ?? 0, 0, ',', '.') . "\n";
    
    // Check sale details
    if ($sale->details && $sale->details->count() > 0) {
        echo "Details:\n";
        foreach ($sale->details as $detail) {
            $produk = $detail->produk;
            if ($produk) {
                $hppPerUnit = (float)($produk->hpp ?? $produk->harga_pokok ?? $produk->harga_bom ?? 0);
                $totalHPP = round($hppPerUnit * $detail->jumlah);
                
                echo "  Product: {$produk->nama_produk}\n";
                echo "  Qty: {$detail->jumlah}\n";
                echo "  HPP per unit: " . number_format($hppPerUnit, 0, ',', '.') . "\n";
                echo "  Total HPP: " . number_format($totalHPP, 0, ',', '.') . "\n";
            } else {
                echo "  Product not found for detail ID: {$detail->id}\n";
            }
        }
    }
    
    // Check if any journal exists for this sale
    echo "\nJournal Entries for this sale:\n";
    
    $allJournals = \Illuminate\Support\Facades\DB::table('jurnal_umum')
        ->where('referensi', 'sale#' . $sale->id)
        ->get();
    
    echo "Total Journal Entries: {$allJournals->count()}\n";
    foreach ($allJournals as $journal) {
        $coa = \App\Models\Coa::find($journal->coa_id);
        echo "  {$coa->kode_akun} - {$coa->nama_akun}\n";
        echo "    Debit: " . number_format($journal->debit, 0, ',', '.') . "\n";
        echo "    Kredit: " . number_format($journal->kredit, 0, ',', '.') . "\n";
        echo "    Keterangan: {$journal->keterangan}\n";
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

// Check if there are any products with HPP data
echo "\n=== CHECKING PRODUCTS WITH HPP ===\n";

$products = \App\Models\Produk::whereNotNull('hpp')
    ->orWhereNotNull('harga_pokok')
    ->orWhereNotNull('harga_bom')
    ->limit(5)
    ->get();

echo "Products with HPP data: {$products->count()}\n";
foreach ($products as $product) {
    echo "  {$product->nama_produk}\n";
    echo "    HPP: " . number_format($product->hpp ?? 0, 0, ',', '.') . "\n";
    echo "    Harga Pokok: " . number_format($product->harga_pokok ?? 0, 0, ',', '.') . "\n";
    echo "    Harga BOM: " . number_format($product->harga_bom ?? 0, 0, ',', '.') . "\n";
}

echo "\nSales data check completed!\n";
