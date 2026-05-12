<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAyamKampungStockNow extends Command
{
    protected $signature = 'fix:ayam-kampung-now';
    protected $description = 'Fix Ayam Kampung stock - IMMEDIATE FIX';

    public function handle()
    {
        $this->info('=== FIXING AYAM KAMPUNG STOCK NOW ===');
        $this->newLine();

        try {
            DB::beginTransaction();
            
            // Check current data
            $this->info('BEFORE FIX:');
            $movements = DB::select("SELECT id, tanggal, direction, qty, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
            $this->line('Stock Movements: ' . count($movements) . ' records');
            foreach ($movements as $m) {
                $this->line("  - {$m->tanggal} | {$m->direction} | {$m->qty} | {$m->ref_type}");
            }
            
            $total = DB::selectOne("SELECT SUM(remaining_qty) as total FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
            $this->line('Stock Layers Total: ' . ($total->total ?? 0) . ' (PROBLEM - should be 28)');
            $this->newLine();
            
            // DELETE ALL
            $this->info('Cleaning up...');
            DB::delete("DELETE FROM stock_movements WHERE item_type = 'material' AND item_id = 2");
            DB::delete("DELETE FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
            DB::update("UPDATE bahan_bakus SET stok = 0 WHERE id = 2");
            $this->line('✓ Deleted all old data');
            $this->newLine();
            
            // Insert correct data
            $this->info('Inserting correct data...');
            
            DB::insert("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 'in', 30.0000, 'Ekor', 45000.0000, 1350000.00, 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
            $this->line('✓ Initial stock: 30 Ekor');
            
            DB::insert("INSERT INTO stock_layers (item_type, item_id, tanggal, remaining_qty, unit_cost, satuan, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-01', 30.0000, 45000.0000, 'Ekor', 'initial_stock', 0, '2026-03-01 00:00:00', '2026-03-01 00:00:00')");
            $this->line('✓ Stock layer: 30 Ekor');
            
            DB::insert("INSERT INTO stock_movements (item_type, item_id, tanggal, direction, qty, satuan, unit_cost, total_cost, ref_type, ref_id, created_at, updated_at) VALUES ('material', 2, '2026-03-11', 'out', 2.0000, 'Ekor', 45000.0000, 90000.00, 'production', 1, '2026-03-11 22:09:05', '2026-03-11 22:09:05')");
            $this->line('✓ Production: 2 Ekor OUT');
            
            DB::update("UPDATE stock_layers SET remaining_qty = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE item_type = 'material' AND item_id = 2");
            $this->line('✓ Stock layer: 28 remaining');
            
            DB::update("UPDATE bahan_bakus SET stok = 28.0000, updated_at = '2026-03-11 22:09:05' WHERE id = 2");
            $this->line('✓ Master stock: 28');
            $this->newLine();
            
            // Verify
            $this->info('AFTER FIX:');
            $movements = DB::select("SELECT id, tanggal, direction, qty, ref_type FROM stock_movements WHERE item_type = 'material' AND item_id = 2 ORDER BY tanggal");
            $this->line('Stock Movements: ' . count($movements) . ' records');
            foreach ($movements as $m) {
                $this->line("  - {$m->tanggal} | {$m->direction} | {$m->qty} | {$m->ref_type}");
            }
            
            $total = DB::selectOne("SELECT SUM(remaining_qty) as total FROM stock_layers WHERE item_type = 'material' AND item_id = 2");
            $this->line('Stock Layers Total: ' . ($total->total ?? 0) . ' (SHOULD BE 28)');
            
            $master = DB::selectOne("SELECT stok FROM bahan_bakus WHERE id = 2");
            $this->line('Master Stock: ' . ($master->stok ?? 0) . ' (SHOULD BE 28)');
            $this->newLine();
            
            DB::commit();
            
            $this->info('✅ SUCCESS! Stock fixed to 28 Ekor.');
            $this->info('Refresh: http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2');
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ ERROR: ' . $e->getMessage());
            return 1;
        }
    }
}
