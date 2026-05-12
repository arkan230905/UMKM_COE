<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

class FixStockMaster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-stock-master';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix stock master based on stock movements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing Stock Master based on Stock Movements...');
        
        // Fix materials
        $this->info('');
        $this->info('Fixing Materials Stock:');
        
        $materials = BahanBaku::all();
        foreach ($materials as $material) {
            $inQty = StockMovement::where('item_type', 'material')
                ->where('item_id', $material->id)
                ->where('direction', 'in')
                ->sum('qty');
                
            $outQty = StockMovement::where('item_type', 'material')
                ->where('item_id', $material->id)
                ->where('direction', 'out')
                ->sum('qty');
                
            $correctStock = $inQty - $outQty;
            
            if (abs($correctStock - $material->stok) > 0.01) {
                $this->line('  ' . $material->nama_bahan . ': ' . $material->stok . ' -> ' . $correctStock);
                $material->stok = $correctStock;
                $material->save();
            } else {
                $this->line('  ' . $material->nama_bahan . ': OK (' . $material->stok . ')');
            }
        }
        
        // Fix support materials
        $this->info('');
        $this->info('Fixing Support Materials Stock:');
        
        $supports = BahanPendukung::all();
        foreach ($supports as $support) {
            $inQty = StockMovement::where('item_type', 'support')
                ->where('item_id', $support->id)
                ->where('direction', 'in')
                ->sum('qty');
                
            $outQty = StockMovement::where('item_type', 'support')
                ->where('item_id', $support->id)
                ->where('direction', 'out')
                ->sum('qty');
                
            $correctStock = $inQty - $outQty;
            
            if (abs($correctStock - $support->stok) > 0.01) {
                $this->line('  ' . $support->nama_bahan . ': ' . $support->stok . ' -> ' . $correctStock);
                $support->stok = $correctStock;
                $support->save();
            } else {
                $this->line('  ' . $support->nama_bahan . ': OK (' . $support->stok . ')');
            }
        }
        
        $this->info('');
        $this->info('Stock master fixed successfully!');
        
        return 0;
    }
}
