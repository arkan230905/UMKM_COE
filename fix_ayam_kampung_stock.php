<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting Ayam Kampung stock fix...\n";

try {
    DB::beginTransaction();
    
    // Step 1: Check current data before cleanup
    echo "\n=== BEFORE CLEANUP ===\n";
    echo "Stock Movements:\n";
    $movements = DB::select("SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    foreach ($movements as $movement) {
        echo "ID: {$movement->id}, Date: {$movement->tanggal}, Direction: {$movement->direction}, Qty: {$movement->qty}, Cost: {$movement->unit_cost}, Total: {$movement->total_cost}, Type: {$movement->ref_type}, Ref: {$movement->ref_id}\n";
    }
    
    echo "\nStock Layers:\n";
    $layers = DB::select("SELECT id, remaining_qty, unit_cost, ref_type FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    foreach ($layers as $layer) {
        echo "ID: {$layer->id}, Remaining: {$layer->remaining_qty}, Cost: {$layer->unit_cost}, Type: {$layer->ref_type}\n";
    }
    
    echo "\nMaster Data:\n";
    $master = DB::select("SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2");
    foreach ($master as $m) {
        echo "ID: {$m->id}, Name: {$m->nama_bahan}, Stock: {$m->stok}\n";
    }
    
    // Step 2: COMPLETE CLEANUP - Remove ALL related data
    echo "\n=== PERFORMING CLEANUP ===\n";
    $deletedMovements = DB::delete("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
    echo "Deleted {$deletedMovements} stock movements\n";
    
    $deletedLayers = DB::delete("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    echo "Deleted {$deletedLayers} stock layers\n";
    
    // Step 3: Reset master data first
    DB::update("UPDATE bahan_bakus SET stok = 0 WHERE id = 2");
    echo "Reset master stock to 0\n";
    
    // Step 4: Create ONLY correct initial stock - 30 Ekor at 45,000 (March 1st)
    DB::insert("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    echo "Created initial stock movement: 30 Ekor at 45,000\n";
    
    // Step 5: Create ONLY correct stock layer - 30 Ekor at 45,000
    DB::insert("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
    echo "Created initial stock layer: 30 Ekor at 45,000\n";
    
    // Step 6: Add ONLY production consumption - OUT 2 Ekor at 45,000 (March 11th)
    DB::insert("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
    echo "Created production consumption: 2 Ekor at 45,000\n";
    
    // Step 7: Update stock layer to remaining 28 Ekor
    DB::update("UPDATE stock_layers SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE item_type = 'material' AND item_id = 2");
    echo "Updated stock layer to 28 remaining\n";
    
    // Step 8: Update master data to final stock 28 Ekor
    DB::update("UPDATE bahan_bakus SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE id = 2");
    echo "Updated master stock to 28\n";
    
    // Step 9: Verification - Check final results
    echo "\n=== AFTER CLEANUP ===\n";
    echo "Stock Movements:\n";
    $movements = DB::select("SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
    foreach ($movements as $movement) {
        echo "ID: {$movement->id}, Date: {$movement->tanggal}, Direction: {$movement->direction}, Qty: {$movement->qty}, Cost: {$movement->unit_cost}, Total: {$movement->total_cost}, Type: {$movement->ref_type}, Ref: {$movement->ref_id}\n";
    }
    
    echo "\nStock Layers:\n";
    $layers = DB::select("SELECT id, remaining_qty, unit_cost, ref_type FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
    foreach ($layers as $layer) {
        echo "ID: {$layer->id}, Remaining: {$layer->remaining_qty}, Cost: {$layer->unit_cost}, Type: {$layer->ref_type}\n";
    }
    
    echo "\nMaster Data:\n";
    $master = DB::select("SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2");
    foreach ($master as $m) {
        echo "ID: {$m->id}, Name: {$m->nama_bahan}, Stock: {$m->stok}\n";
    }
    
    DB::commit();
    echo "\n✅ SUCCESS: Ayam Kampung stock has been fixed!\n";
    echo "\nExpected Results:\n";
    echo "- Initial stock: 30 Ekor at Rp 45,000 = Rp 1,350,000\n";
    echo "- Production consumption: 2 Ekor at Rp 45,000 = Rp 90,000\n";
    echo "- Final stock: 28 Ekor at Rp 45,000 = Rp 1,260,000\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Transaction rolled back.\n";
}