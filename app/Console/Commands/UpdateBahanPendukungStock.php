<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateBahanPendukungStock extends Command
{
    protected $signature = 'update:bahan-pendukung-stock';
    protected $description = 'Update bahan pendukung stock from 50 to 200 units';

    public function handle()
    {
        $this->info('Starting bahan pendukung stock update...');
        
        try {
            // Update all bahan pendukung records that currently have stock = 50 to stock = 200
            $updated = DB::table('bahan_pendukungs')
                ->where('stok', 50)
                ->update(['stok' => 200]);
            
            $this->info("Updated {$updated} bahan pendukung records from 50 to 200 stock.");
            
            // Show current stock levels for verification
            $bahanPendukungs = DB::table('bahan_pendukungs')
                ->select('id', 'nama_bahan', 'stok')
                ->get();
            
            $this->info('Current bahan pendukung stock levels:');
            $this->table(['ID', 'Nama Bahan', 'Stok'], $bahanPendukungs->map(function($item) {
                return [$item->id, $item->nama_bahan, $item->stok];
            })->toArray());
            
            $this->info('Stock update completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error updating stock: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}