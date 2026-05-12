<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING BOP COMPONENT DATA STRUCTURE ===\n\n";

// Get all BOP proses records
$bopProses = \App\Models\BopProses::all();

foreach ($bopProses as $bp) {
    echo "Processing BOP ID: " . $bp->id . "\n";
    
    // Check if komponen_bop exists and has rate_per_hour instead of rate_per_produk
    if ($bp->komponen_bop && is_array($bp->komponen_bop)) {
        $needsUpdate = false;
        $updatedKomponen = [];
        
        foreach ($bp->komponen_bop as $komponen) {
            // Check if component has rate_per_hour (old structure)
            if (isset($komponen['rate_per_hour'])) {
                $needsUpdate = true;
                $updatedKomponen[] = [
                    'component' => $komponen['component'],
                    'rate_per_produk' => $komponen['rate_per_hour'], // Convert to rate_per_produk
                    'description' => $komponen['description'] ?? $komponen['keterangan'] ?? ''
                ];
                echo "  - Converting component: " . $komponen['component'] . " (rate_per_hour: " . $komponen['rate_per_hour'] . " -> rate_per_produk: " . $komponen['rate_per_hour'] . ")\n";
            } else {
                // Already correct structure
                $updatedKomponen[] = $komponen;
            }
        }
        
        if ($needsUpdate) {
            // Update the record with corrected component structure
            $bp->komponen_bop = $updatedKomponen;
            $bp->save();
            
            echo "  ✓ Updated BOP ID " . $bp->id . " with correct component structure\n";
        } else {
            echo "  - BOP ID " . $bp->id . " already has correct structure\n";
        }
    } else {
        echo "  - BOP ID " . $bp->id . " has no components\n";
    }
    
    echo "\n";
}

echo "=== RECALCULATING TOTAL BOP PER PRODUK ===\n";

// Recalculate total BOP per produk for all records
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

echo "\n=== FIX COMPLETED! 🎉 ===\n";
