<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING BOP CALCULATION LOGIC ===\n\n";

// Get all BOP proses records
$bopProses = \App\Models\BopProses::all();

foreach ($bopProses as $bp) {
    echo "Processing BOP ID: " . $bp->id . "\n";
    
    // Check if komponen_bop exists and has rate_per_hour instead of rate_per_produk
    if ($bp->komponen_bop && is_array($bp->komponen_bop)) {
        $needsUpdate = false;
        $updatedKomponen = [];
        $totalBopPerProduk = 0;
        
        foreach ($bp->komponen_bop as $komponen) {
            // Check if component has rate_per_hour (old structure)
            if (isset($komponen['rate_per_hour'])) {
                $needsUpdate = true;
                $ratePerHour = floatval($komponen['rate_per_hour']);
                $kapasitas = $bp->kapasitas_per_jam ?? 120; // Default or from BOP
                
                // Convert rate_per_hour to rate_per_produk
                $ratePerProduk = $kapasitas > 0 ? $ratePerHour / $kapasitas : 0;
                
                $updatedKomponen[] = [
                    'component' => $komponen['component'],
                    'rate_per_produk' => $ratePerProduk,
                    'rate_per_hour' => $ratePerHour, // Keep both for compatibility
                    'description' => $komponen['description'] ?? ''
                ];
                
                $totalBopPerProduk += $ratePerProduk;
                
                echo "  - Converting component: " . $komponen['component'] . "\n";
                echo "    rate_per_hour: " . $ratePerHour . " -> rate_per_produk: " . $ratePerProduk . "\n";
                echo "    (kapasitas: " . $kapasitas . " pcs/jam)\n";
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

echo "=== RECALCULATING ALL BOP TOTALS ===\n";

// Recalculate all BOP totals to ensure consistency
foreach ($bopProses as $bp) {
    echo "Recalculating BOP ID: " . $bp->id . "\n";
    
    $totalBopPerProduk = 0;
    
    if ($bp->komponen_bop && is_array($bp->komponen_bop)) {
        foreach ($bp->komponen_bop as $komponen) {
            if (isset($komponen['rate_per_produk'])) {
                $totalBopPerProduk += floatval($komponen['rate_per_produk']);
                echo "  - Adding " . $komponen['component'] . ": " . $komponen['rate_per_produk'] . "\n";
            }
        }
    }
    
    // Calculate BTKL per produk
    $btklPerProduk = 0;
    if ($bp->prosesProduksi && $bp->prosesProduksi->kapasitas_per_jam > 0) {
        $btklPerProduk = floatval($bp->prosesProduksi->tarif_btkl) / floatval($bp->prosesProduksi->kapasitas_per_jam);
    }
    
    $totalBiayaPerProduk = $btklPerProduk + $totalBopPerProduk;
    
    // Update the record
    $bp->total_bop_per_produk = $totalBopPerProduk;
    $bp->total_biaya_per_produk = $totalBiayaPerProduk;
    $bp->bop_per_unit = $totalBopPerProduk;
    $bp->save();
    
    echo "  ✓ Total BOP per produk: " . $totalBopPerProduk . "\n";
    echo "  ✓ Total biaya per produk: " . number_format($totalBiayaPerProduk, 2) . "\n";
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

echo "\n=== CALCULATION FIX COMPLETED! 🎉 ===\n";
