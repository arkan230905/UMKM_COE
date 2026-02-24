<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get Ayam Kampung data
$ayam = \App\Models\BahanBaku::where('nama_bahan', 'like', '%Ayam Kampung%')->first();

// Get ALL stock movements for Ayam Kampung (no date filter)
echo "=== ALL Stock Movements for Ayam Kampung ===\n";
$allMovements = \App\Models\StockMovement::where('item_type', 'material')
    ->where('item_id', $ayam->id)
    ->orderBy('tanggal', 'asc')
    ->orderBy('id', 'asc')
    ->get();

if ($allMovements->isEmpty()) {
    echo "No movements found at all\n";
    
    // Check if there's initial stock in master data
    echo "\n=== Master Stock Data ===\n";
    echo "Stok in master: " . ($ayam->stok ?? 0) . "\n";
    echo "Harga satuan: " . ($ayam->harga_satuan ?? 0) . "\n";
} else {
    foreach ($allMovements as $m) {
        echo "Date: {$m->tanggal}, Direction: {$m->direction}, Qty: {$m->qty}, Total Cost: " . ($m->total_cost ?? 0) . ", Ref: {$m->ref_type}#{$m->ref_id}\n";
    }
    
    // Calculate running total
    $runningQty = 0;
    $runningNilai = 0;
    echo "\n=== Running Balance ===\n";
    foreach ($allMovements as $m) {
        if ($m->direction === 'in') {
            $runningQty += (float)$m->qty;
            $runningNilai += (float)($m->total_cost ?? 0);
        } else {
            $runningQty -= (float)$m->qty;
            $runningNilai -= (float)($m->total_cost ?? 0);
        }
        echo "After {$m->tanggal}: Qty=$runningQty, Nilai=$runningNilai\n";
    }
}

// Also check if we need to create some test data
echo "\n=== Creating Test Data (if needed) ===\n";
if ($allMovements->isEmpty()) {
    echo "No movements found. You need to:\n";
    echo "1. Create initial stock entry for 01/02/2026 (30 Ekor = 180 Potong = Rp 1,350,000)\n";
    echo "2. Create purchase entry for 02/02/2026 (8 Ekor = 48 Potong = Rp 480,000)\n";
    echo "3. Create purchase entry for 03/02/2026 (10 Ekor = 60 Potong = Rp 480,000)\n";
}
