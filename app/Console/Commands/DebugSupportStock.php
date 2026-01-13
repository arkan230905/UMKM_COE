<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

class DebugSupportStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-support-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug support material stock issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Debugging Support Material Stock...');
        
        // 1. Check all support materials
        $this->info('');
        $this->info('1. All Support Materials:');
        $supports = BahanPendukung::all();
        
        foreach ($supports as $support) {
            $this->line('  ' . $support->nama_bahan . ': ' . $support->stok . ' ' . ($support->satuanRelation->kode ?? 'pcs'));
        }
        
        // 2. Check stock movements for support materials
        $this->info('');
        $this->info('2. Stock Movements for Support Materials:');
        $movements = StockMovement::where('item_type', 'support')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        foreach ($movements as $movement) {
            $item = BahanPendukung::find($movement->item_id);
            $itemName = $item ? $item->nama_bahan : 'Support #' . $movement->item_id;
            
            $this->line('  ' . $movement->tanggal . ' - ' . $itemName . 
                ' - Qty: ' . $movement->qty . 
                ' Direction: ' . $movement->direction . 
                ' Ref: ' . $movement->ref_type . '#' . $movement->ref_id);
        }
        
        // 3. Calculate expected stock for each support material
        $this->info('');
        $this->info('3. Expected vs Actual Stock:');
        
        foreach ($supports as $support) {
            $inQty = StockMovement::where('item_type', 'support')
                ->where('item_id', $support->id)
                ->where('direction', 'in')
                ->sum('qty');
                
            $outQty = StockMovement::where('item_type', 'support')
                ->where('item_id', $support->id)
                ->where('direction', 'out')
                ->sum('qty');
                
            $expectedStock = $inQty - $outQty;
            
            $this->line('  ' . $support->nama_bahan . ': In=' . $inQty . ' Out=' . $outQty . ' Expected=' . $expectedStock . ' Actual=' . $support->stok);
            
            if (abs($expectedStock - $support->stok) > 0.01) {
                $this->error('    MISMATCH! Expected and actual stock differ!');
            }
        }
        
        return 0;
    }
}
