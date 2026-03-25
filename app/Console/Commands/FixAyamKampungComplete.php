<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAyamKampungComplete extends Command
{
    protected $signature = 'fix:ayam-kampung-complete';
    protected $description = 'Fix Ayam Kampung stock completely - FINAL FIX';

    public function handle()
    {
        $this->info('=== FIXING AYAM KAMPUNG STOCK - COMPLETE ===');
        $this->newLine();

        try {
            DB::beginTransaction();
            
            // STEP 1: Fix conversion ratios
            $this->info('STEP 1: Fixing conversion ratios...');
            
            $ekorId = DB::table('satuans')->where('nama', 'Ekor')->value('id');
            $potongId = DB::table('satuans')->where('nama', 'Potong')->value('id');
            $kgId = DB::table('satuans')->whereIn('nama', ['Kilogram', 'Kg'])->value('id');
            $gramId = DB::table('satuans')->where('nama', 'Gram')->value('id');
            
            DB::table('bahan_bakus')->where('id', 2)->update([
                'satuan_id' => $ekorId,
                'sub_satuan_1_id' => $potongId,
                'sub_satuan_1_konversi' => 6.0000,
                'sub_satuan_2_id' => $kgId,
                'sub_satuan_2_konversi' => 1.5000,
                'sub_satuan_3_id' => $gramId,
                'sub_satuan_3_konversi' => 1500.0000,
            ]);
            
            $this->line('✓ Conversion ratios updated:');
            $this->line('  - 1 Ekor = 6 Potong');
            $this->line('  - 1 Ekor = 1.5 Kilogram');
            $this->line('  - 1 Ekor = 1,500 Gram');
            $this->newLine();
            
            // STEP 2: Clean up old data
            $this->info('STEP 2: Cleaning up old data...');
            DB::table('stock_movements')->where('item_type', 'material')->where('item_id', 2)->delete();
            DB::table('stock_layers')->where('item_type', 'material')->where('item_id', 2)->delete();
            $this->line('✓ Old data deleted');
            $this->newLine();
            
            // STEP 3: Create correct stock data
            $this->info('STEP 3: Creating correct stock data...');
            
            // Initial stock: 30 Ekor
            DB::table('stock_movements')->insert([
                'item_type' => 'material',
                'item_id' => 2,
                'tanggal' => '2026-03-01',
                'direction' => 'in',
                'qty' => 30.0000,
                'satuan' => 'Ekor',
                'unit_cost' => 45000.0000,
                'total_cost' => 1350000.00,
                'ref_type' => 'initial_stock',
                'ref_id' => 0,
                'created_at' => '2026-03-01 00:00:00',
                'updated_at' => '2026-03-01 00:00:00',
            ]);
            $this->line('✓ Initial stock: 30 Ekor @ Rp 45,000');
            
            // Production: 1.6667 Ekor (10 Potong)
            $productionEkor = 10 / 6; // 1.6667
            $productionCost = $productionEkor * 45000; // 75,000
            
            DB::table('stock_movements')->insert([
                'item_type' => 'material',
                'item_id' => 2,
                'tanggal' => '2026-03-11',
                'direction' => 'out',
                'qty' => $productionEkor,
                'satuan' => 'Ekor',
                'unit_cost' => 45000.0000,
                'total_cost' => $productionCost,
                'ref_type' => 'production',
                'ref_id' => 1,
                'created_at' => '2026-03-11 22:09:05',
                'updated_at' => '2026-03-11 22:09:05',
            ]);
            $this->line('✓ Production: ' . number_format($productionEkor, 4) . ' Ekor (10 Potong) @ Rp 45,000');
            
            // Stock layer
            $remainingEkor = 30 - $productionEkor;
            DB::table('stock_layers')->insert([
                'item_type' => 'material',
                'item_id' => 2,
                'tanggal' => '2026-03-01',
                'remaining_qty' => $remainingEkor,
                'unit_cost' => 45000.0000,
                'satuan' => 'Ekor',
                'ref_type' => 'initial_stock',
                'ref_id' => 0,
                'created_at' => '2026-03-01 00:00:00',
                'updated_at' => '2026-03-01 00:00:00',
            ]);
            $this->line('✓ Stock layer: ' . number_format($remainingEkor, 4) . ' Ekor remaining');
            
            // Master data
            DB::table('bahan_bakus')->where('id', 2)->update([
                'stok' => $remainingEkor,
                'updated_at' => now(),
            ]);
            $this->line('✓ Master stock: ' . number_format($remainingEkor, 4) . ' Ekor');
            $this->newLine();
            
            // STEP 4: Verification
            $this->info('STEP 4: Verification...');
            
            $movements = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 2)
                ->orderBy('tanggal')
                ->get();
            
            $this->line('Stock Movements: ' . $movements->count() . ' records');
            foreach ($movements as $m) {
                $this->line("  - {$m->tanggal} | {$m->direction} | {$m->qty} {$m->satuan} @ Rp " . number_format($m->unit_cost, 2) . " = Rp " . number_format($m->total_cost, 2) . " | {$m->ref_type}");
            }
            $this->newLine();
            
            $this->info('EXPECTED DISPLAY:');
            $this->line('');
            $this->line('SATUAN EKOR:');
            $this->line('  01/03/2026 | Stok Awal: 30 Ekor @ Rp 45,000 = Rp 1,350,000');
            $this->line('  11/03/2026 | Produksi: 1.6667 Ekor @ Rp 45,000 = Rp 75,000');
            $this->line('  TOTAL: 28.3333 Ekor @ Rp 45,000 = Rp 1,275,000');
            $this->line('');
            $this->line('SATUAN POTONG (1 Ekor = 6 Potong):');
            $this->line('  01/03/2026 | Stok Awal: 180 Potong @ Rp 7,500 = Rp 1,350,000');
            $this->line('  11/03/2026 | Produksi: 10 Potong @ Rp 7,500 = Rp 75,000');
            $this->line('  TOTAL: 170 Potong @ Rp 7,500 = Rp 1,275,000');
            $this->line('');
            $this->line('SATUAN KILOGRAM (1 Ekor = 1.5 Kg):');
            $this->line('  01/03/2026 | Stok Awal: 45 Kg @ Rp 30,000 = Rp 1,350,000');
            $this->line('  11/03/2026 | Produksi: 2.5 Kg @ Rp 30,000 = Rp 75,000');
            $this->line('  TOTAL: 42.5 Kg @ Rp 30,000 = Rp 1,275,000');
            $this->line('');
            $this->line('SATUAN GRAM (1 Ekor = 1,500 Gram):');
            $this->line('  01/03/2026 | Stok Awal: 45,000 Gram @ Rp 30 = Rp 1,350,000');
            $this->line('  11/03/2026 | Produksi: 2,500 Gram @ Rp 30 = Rp 75,000');
            $this->line('  TOTAL: 42,500 Gram @ Rp 30 = Rp 1,275,000');
            $this->newLine();
            
            DB::commit();
            
            $this->info('✅ SUCCESS! All data fixed!');
            $this->newLine();
            $this->info('Now clearing cache...');
            
            // Clear cache
            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');
            \Artisan::call('config:clear');
            
            $this->line('✓ Cache cleared');
            $this->newLine();
            $this->info('Please refresh: http://127.0.0.1:8000/laporan/stok?tipe=material&item_id=2');
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('❌ ERROR: ' . $e->getMessage());
            return 1;
        }
    }
}
