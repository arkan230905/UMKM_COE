<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING BOP WITH DIRECT PER PRODUK VALUES ===\n\n";

// Get all BOP proses records
$bopProses = \App\Models\BopProses::all();

foreach ($bopProses as $bp) {
    echo "Processing BOP ID: " . $bp->id . "\n";
    
    // Check if komponen_bop exists and has rate_per_hour
    if ($bp->komponen_bop && is_array($bp->komponen_bop)) {
        $needsUpdate = false;
        $updatedKomponen = [];
        $totalBopPerProduk = 0;
        
        foreach ($bp->komponen_bop as $komponen) {
            // Check if component has rate_per_hour (old structure)
            if (isset($komponen['rate_per_hour'])) {
                $needsUpdate = true;
                $ratePerHour = floatval($komponen['rate_per_hour']);
                
                // Based on your example, it seems the values should be direct per produk
                // So we'll treat rate_per_hour as actually rate_per_produk
                $ratePerProduk = $ratePerHour; // Direct assignment, no division
                
                $updatedKomponen[] = [
                    'component' => $komponen['component'],
                    'rate_per_produk' => $ratePerProduk,
                    'description' => $komponen['description'] ?? ''
                ];
                
                $totalBopPerProduk += $ratePerProduk;
                
                echo "  - Converting component: " . $komponen['component'] . "\n";
                echo "    rate_per_hour: " . $ratePerHour . " -> rate_per_produk: " . $ratePerProduk . "\n";
            } elseif (isset($komponen['rate_per_produk'])) {
                // Already correct structure
                $updatedKomponen[] = $komponen;
                $totalBopPerProduk += floatval($komponen['rate_per_produk']);
                echo "  - Component already correct: " . $komponen['component'] . " -> " . $komponen['rate_per_produk'] . "\n";
            }
        }
        
        if ($needsUpdate) {
            // Calculate BTKL per produk
            $btklPerProduk = 0;
            if ($bp->prosesProduksi && $bp->prosesProduksi->kapasitas_per_jam > 0) {
                $btklPerProduk = floatval($bp->prosesProduksi->tarif_btkl) / floatval($bp->prosesProduksi->kapasitas_per_jam);
            }
            
            // Calculate total biaya per produk
            $totalBiayaPerProduk = $btklPerProduk + $totalBopPerProduk;
            
            // Update the record with corrected component structure and calculated totals
            $bp->komponen_bop = $updatedKomponen;
            $bp->total_bop_per_produk = $totalBopPerProduk;
            $bp->total_biaya_per_produk = $totalBiayaPerProduk;
            $bp->bop_per_unit = $totalBopPerProduk;
            $bp->save();
            
            echo "  ✓ Updated BOP ID " . $bp->id . "\n";
            echo "    Total BOP / produk: " . $totalBopPerProduk . "\n";
            echo "    Total Biaya / produk: " . number_format($totalBiayaPerProduk, 2) . "\n";
        } else {
            echo "  - BOP ID " . $bp->id . " already has correct structure\n";
        }
    } else {
        echo "  - BOP ID " . $bp->id . " has no components\n";
    }
    
    echo "\n";
}

echo "=== VERIFICATION ===\n";
$updatedBopProses = \App\Models\BopProses::all();

foreach ($updatedBopProses as $bp) {
    echo "BOP ID " . $bp->id . ":\n";
    echo "  Komponen: " . json_encode($bp->komponen_bop) . "\n";
    echo "  Total BOP / produk: " . $bp->total_bop_per_produk . "\n";
    echo "  Total Biaya / produk: " . $bp->total_biaya_per_produk . "\n";
    echo "  BOP / unit: " . $bp->bop_per_unit . "\n";
    echo "---\n";
}

echo "\n=== DIRECT VALUES FIX COMPLETED! 🎉 ===\n";
