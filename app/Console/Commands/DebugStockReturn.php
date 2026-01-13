<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;
use App\Models\Retur;
use App\Models\ReturDetail;

class DebugStockReturn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-stock-return';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug stock reduction during returns';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Debugging Stock Return Process...');
        
        // 1. Check recent returns
        $this->info('');
        $this->info('1. Recent Returns:');
        $returns = Retur::with('details')->orderBy('created_at', 'desc')->limit(3)->get();
        
        foreach ($returns as $retur) {
            $this->line('  Retur #' . $retur->id . ' - Status: ' . $retur->status . ' - Total: ' . $retur->jumlah);
            
            foreach ($retur->details as $detail) {
                if ($detail->bahan_baku_id) {
                    $item = BahanBaku::find($detail->bahan_baku_id);
                    $this->line('    - Material: ' . ($item ? $item->nama_bahan : 'Unknown') . ' Qty: ' . $detail->qty);
                } elseif ($detail->bahan_pendukung_id) {
                    $item = BahanPendukung::find($detail->bahan_pendukung_id);
                    $this->line('    - Support: ' . ($item ? $item->nama_bahan : 'Unknown') . ' Qty: ' . $detail->qty);
                }
            }
        }
        
        // 2. Check stock movements for returns
        $this->info('');
        $this->info('2. Stock Movements for Returns:');
        $movements = StockMovement::where('ref_type', 'return')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($movements as $movement) {
            $itemName = 'Unknown';
            if ($movement->item_type === 'material') {
                $item = BahanBaku::find($movement->item_id);
                $itemName = $item ? $item->nama_bahan : 'Material #' . $movement->item_id;
            } elseif ($movement->item_type === 'support') {
                $item = BahanPendukung::find($movement->item_id);
                $itemName = $item ? $item->nama_bahan : 'Support #' . $movement->item_id;
            }
            
            $this->line('  ' . $movement->tanggal . ' - ' . $itemName . 
                ' (' . $movement->item_type . ') - Qty: ' . $movement->qty . 
                ' Direction: ' . $movement->direction . 
                ' Ref: ' . $movement->ref_id);
        }
        
        // 3. Check current stock levels vs expected
        $this->info('');
        $this->info('3. Current Stock Levels:');
        
        $materials = BahanBaku::take(5)->get();
        foreach ($materials as $material) {
            $this->line('  ' . $material->nama_bahan . ': ' . $material->stok . ' ' . ($material->satuanRelation->kode ?? 'pcs'));
        }
        
        // 4. Calculate expected stock based on movements
        $this->info('');
        $this->info('4. Expected Stock from Movements:');
        
        foreach ($materials as $material) {
            $inQty = StockMovement::where('item_type', 'material')
                ->where('item_id', $material->id)
                ->where('direction', 'in')
                ->sum('qty');
                
            $outQty = StockMovement::where('item_type', 'material')
                ->where('item_id', $material->id)
                ->where('direction', 'out')
                ->sum('qty');
                
            $expectedStock = $inQty - $outQty;
            
            $this->line('  ' . $material->nama_bahan . ': In=' . $inQty . ' Out=' . $outQty . ' Expected=' . $expectedStock . ' Actual=' . $material->stok);
            
            if (abs($expectedStock - $material->stok) > 0.01) {
                $this->error('    MISMATCH! Expected and actual stock differ!');
            }
        }
        
        return 0;
    }
}
