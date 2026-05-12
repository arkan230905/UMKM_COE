<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAyamPotongData extends Command
{
    protected $signature = 'fix:ayam-potong';
    protected $description = 'Fix Ayam Potong data from 120 to 160 Potong';

    public function handle()
    {
        $this->info('🔥 FIXING AYAM POTONG ID=1: 120→160 POTONG');
        $this->info('=====================================');
        
        try {
            // Check current data
            $this->info('📋 CHECKING CURRENT DATA:');
            
            $item = DB::table('bahan_bakus')->where('id', 1)->first();
            if ($item) {
                $this->info("Item ID=1: {$item->nama_bahan}");
                $this->info("Satuan ID: {$item->satuan_id}");
                $this->info("Saldo Awal: {$item->saldo_awal}");
            }
            
            // Check production details
            $prodDetails = DB::table('produksi_details')
                ->where('bahan_baku_id', 1)
                ->get();
            
            $this->info('Production Details for ID=1:');
            foreach ($prodDetails as $detail) {
                $this->info("- Detail ID {$detail->id}: {$detail->qty_resep} {$detail->satuan_resep}");
            }
            
            // Check stock movements
            $stockMovements = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 1)
                ->where('ref_type', 'production')
                ->get();
            
            $this->info('Stock Movements for ID=1:');
            foreach ($stockMovements as $movement) {
                $this->info("- Movement ID {$movement->id}: {$movement->qty_as_input} {$movement->satuan_as_input}");
            }
            
            // Apply fixes
            $this->info('🔧 APPLYING FIXES:');
            
            // Update production details
            $affected1 = DB::table('produksi_details')
                ->where('bahan_baku_id', 1)
                ->update([
                    'qty_resep' => 160,
                    'satuan_resep' => 'Potong'
                ]);
            
            $this->info("✅ Updated {$affected1} production detail records");
            
            // Update stock movements
            $affected2 = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 1)
                ->where('ref_type', 'production')
                ->update([
                    'qty_as_input' => 160,
                    'satuan_as_input' => 'Potong'
                ]);
            
            $this->info("✅ Updated {$affected2} stock movement records");
            
            // Verify changes
            $this->info('✅ VERIFICATION:');
            
            $prodDetailsAfter = DB::table('produksi_details')
                ->where('bahan_baku_id', 1)
                ->get();
            
            $this->info('Production Details after fix:');
            foreach ($prodDetailsAfter as $detail) {
                $this->info("- Detail ID {$detail->id}: {$detail->qty_resep} {$detail->satuan_resep}");
            }
            
            $stockMovementsAfter = DB::table('stock_movements')
                ->where('item_type', 'material')
                ->where('item_id', 1)
                ->where('ref_type', 'production')
                ->get();
            
            $this->info('Stock Movements after fix:');
            foreach ($stockMovementsAfter as $movement) {
                $this->info("- Movement ID {$movement->id}: {$movement->qty_as_input} {$movement->satuan_as_input}");
            }
            
            // Check if all are correct
            $allCorrect = true;
            foreach ($prodDetailsAfter as $detail) {
                if ($detail->qty_resep != 160 || $detail->satuan_resep != 'Potong') {
                    $allCorrect = false;
                    break;
                }
            }
            foreach ($stockMovementsAfter as $movement) {
                if ($movement->qty_as_input != 160 || $movement->satuan_as_input != 'Potong') {
                    $allCorrect = false;
                    break;
                }
            }
            
            if ($allCorrect) {
                $this->info('🎉 SUCCESS! ALL DATA FIXED TO 160 POTONG!');
                $this->info('Test URL: laporan/stok?tipe=material&item_id=1&satuan_id=22');
            } else {
                $this->error('❌ SOME DATA STILL INCORRECT!');
            }
            
        } catch (\Exception $e) {
            $this->error('❌ ERROR: ' . $e->getMessage());
        }
    }
}