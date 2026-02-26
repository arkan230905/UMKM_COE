<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Bahan Data for All Products ===\n";

$products = DB::table('produks')->get();
foreach($products as $product) {
    echo "\n--- Product: {$product->nama_produk} (ID: {$product->id}) ---\n";
    
    // Check BomJobCosting
    $bomJobCosting = DB::table('bom_job_costings')->where('produk_id', $product->id)->first();
    if ($bomJobCosting) {
        echo "BomJobCosting ID: {$bomJobCosting->id}\n";
        echo "Total BBB: {$bomJobCosting->total_bbb}\n";
        echo "Total Bahan Pendukung: {$bomJobCosting->total_bahan_pendukung}\n";
        
        // Check BBB details
        $bbbDetails = DB::table('bom_job_bbb')->where('bom_job_costing_id', $bomJobCosting->id)->get();
        echo "BBB Details: {$bbbDetails->count()} records\n";
        foreach($bbbDetails as $bbb) {
            $bahanBaku = DB::table('bahan_bakus')->where('id', $bbb->bahan_baku_id)->first();
            if ($bahanBaku) {
                echo "  - {$bahanBaku->nama_bahan}: {$bbb->jumlah} @ Rp {$bbb->harga_satuan} = {$bbb->subtotal}\n";
            }
        }
        
        // Check Bahan Pendukung details
        $bpDetails = DB::table('bom_job_bahan_pendukung')->where('bom_job_costing_id', $bomJobCosting->id)->get();
        echo "Bahan Pendukung Details: {$bpDetails->count()} records\n";
        foreach($bpDetails as $bp) {
            $bahanPendukung = DB::table('bahan_pendukungs')->where('id', $bp->bahan_pendukung_id)->first();
            if ($bahanPendukung) {
                echo "  - {$bahanPendukung->nama_bahan}: {$bp->jumlah} @ Rp {$bp->harga_satuan} = {$bp->subtotal}\n";
            }
        }
    } else {
        echo "No BomJobCosting found\n";
    }
}

echo "\n=== Check Complete ===\n";
