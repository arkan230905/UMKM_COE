<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAyamKampungStock extends Command
{
    protected $signature = 'stock:fix-ayam-kampung';
    protected $description = 'Fix Ayam Kampung stock calculation issue';

    public function handle()
    {
        $this->info('Starting Ayam Kampung stock fix...');

        try {
            DB::beginTransaction();
            
            // Step 1: Check current data before cleanup
            $this->info("\n=== BEFORE CLEANUP ===");
            $this->info("Stock Movements:");
            $movements = DB::select("SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
            foreach ($movements as $movement) {
                $this->line("ID: {$movement->id}, Date: {$movement->tanggal}, Direction: {$movement->direction}, Qty: {$movement->qty}, Cost: {$movement->unit_cost}, Total: {$movement->total_cost}, Type: {$movement->ref_type}, Ref: {$movement->ref_id}");
            }
            
            $this->info("\nStock Layers:");
            $layers = DB::select("SELECT id, remaining_qty, unit_cost, ref_type FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
            foreach ($layers as $layer) {
                $this->line("ID: {$layer->id}, Remaining: {$layer->remaining_qty}, Cost: {$layer->unit_cost}, Type: {$layer->ref_type}");
            }
            
            $this->info("\nMaster Data:");
            $master = DB::select("SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2");
            foreach ($master as $m) {
                $this->line("ID: {$m->id}, Name: {$m->nama_bahan}, Stock: {$m->stok}");
            }
            
            // Step 2: COMPLETE CLEANUP - Remove ALL related data
            $this->info("\n=== PERFORMING CLEANUP ===");
            $deletedMovements = DB::delete("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
            $this->info("Deleted {$deletedMovements} stock movements");
            
            $deletedLayers = DB::delete("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
            $this->info("Deleted {$deletedLayers} stock layers");
            
            // Step 3: Reset master data first
            DB::update("UPDATE bahan_bakus SET stok = 0 WHERE id = 2");
            $this->info("Reset master stock to 0");
            
            // Step 4: Create ONLY correct initial stock - 30 Ekor at 45,000 (March 1st)
            DB::insert("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
            $this->info("Created initial stock movement: 30 Ekor at 45,000");
            
            // Step 5: Create ONLY correct stock layer - 30 Ekor at 45,000
            DB::insert("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
            $this->info("Created initial stock layer: 30 Ekor at 45,000");
            
            // Step 6: Add ONLY production consumption - OUT 2 Ekor at 45,000 (March 11th)
            DB::insert("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
            $this->info("Created production consumption: 2 Ekor at 45,000");
            
            // Step 7: Update stock layer to remaining 28 Ekor
            DB::update("UPDATE stock_layers SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE item_type = 'material' AND item_id = 2");
            $this->info("Updated stock layer to 28 remaining");
            
            // Step 8: Update master data to final stock 28 Ekor
            DB::update("UPDATE bahan_bakus SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE id = 2");
            $this->info("Updated master stock to 28");
            
            // Step 9: Verification - Check final results
            $this->info("\n=== AFTER CLEANUP ===");
            $this->info("Stock Movements:");
            $movements = DB::select("SELECT id, tanggal, direction, qty, unit_cost, total_cost, ref_type, ref_id FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
            foreach ($movements as $movement) {
                $this->line("ID: {$movement->id}, Date: {$movement->tanggal}, Direction: {$movement->direction}, Qty: {$movement->qty}, Cost: {$movement->unit_cost}, Total: {$movement->total_cost}, Type: {$movement->ref_type}, Ref: {$movement->ref_id}");
            }
            
            $this->info("\nStock Layers:");
            $layers = DB::select("SELECT id, remaining_qty, unit_cost, ref_type FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
            foreach ($layers as $layer) {
                $this->line("ID: {$layer->id}, Remaining: {$layer->remaining_qty}, Cost: {$layer->unit_cost}, Type: {$layer->ref_type}");
            }
            
            $this->info("\nMaster Data:");
            $master = DB::select("SELECT id, nama_bahan, stok FROM bahan_bakus WHERE id = 2");
            foreach ($master as $m) {
                $this->line("ID: {$m->id}, Name: {$m->nama_bahan}, Stock: {$m->stok}");
            }
            
            DB::commit();
            $this->info("\n✅ SUCCESS: Ayam Kampung stock has been fixed!");
            $this->info("\nExpected Results:");
            $this->info("- Initial stock: 30 Ekor at Rp 45,000 = Rp 1,350,000");
            $this->info("- Production consumption: 2 Ekor at Rp 45,000 = Rp 90,000");
            $this->info("- Final stock: 28 Ekor at Rp 45,000 = Rp 1,260,000");
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("\n❌ ERROR: " . $e->getMessage());
            $this->error("Transaction rolled back.");
            return 1;
        }
    }
}