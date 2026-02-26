<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MANUAL UPDATE HARGA POKOK ===\n";

try {
    // Get all products with their BomJobCosting
    $products = DB::table('produks')->get();
    
    foreach($products as $product) {
        echo "\n--- Processing Product: {$product->nama_produk} (ID: {$product->id}) ---\n";
        
        $bomJobCosting = DB::table('bom_job_costings')->where('produk_id', $product->id)->first();
        if ($bomJobCosting) {
            // Calculate total HPP
            $totalHPP = $bomJobCosting->total_bbb + $bomJobCosting->total_bahan_pendukung + $bomJobCosting->total_btkl + $bomJobCosting->total_bop;
            
            echo "Calculated HPP: " . number_format($totalHPP, 2) . "\n";
            
            // Update harga_pokok in produks table
            DB::table('produks')->where('id', $product->id)->update([
                'harga_pokok' => $totalHPP,
                'updated_at' => now()
            ]);
            
            echo "✅ Updated harga_pokok to: " . number_format($totalHPP, 2) . "\n";
        } else {
            echo "❌ No BomJobCosting found\n";
        }
    }
    
    echo "\n=== UPDATE COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
