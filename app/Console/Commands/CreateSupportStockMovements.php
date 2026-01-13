<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembelianDetail;
use App\Models\StockMovement;
use App\Models\BahanPendukung;
use App\Services\StockService;

class CreateSupportStockMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-support-stock-movements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create stock movements for existing support purchases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Stock Movements for Support Purchases...');
        
        $stock = new StockService();
        
        // Get all support purchase details that don't have stock movements
        $supportDetails = PembelianDetail::whereNotNull('bahan_pendukung_id')
            ->with(['bahanPendukung', 'pembelian'])
            ->get();
            
        foreach ($supportDetails as $detail) {
            $support = $detail->bahanPendukung;
            $pembelian = $detail->pembelian;
            
            if (!$support || !$pembelian) {
                continue;
            }
            
            // Check if stock movement already exists
            $existingMovement = StockMovement::where('ref_type', 'purchase')
                ->where('ref_id', $detail->pembelian_id)
                ->where('item_type', 'support')
                ->where('item_id', $support->id)
                ->first();
                
            if ($existingMovement) {
                $this->line('  Skipping ' . $support->nama_bahan . ' - movement already exists');
                continue;
            }
            
            try {
                // Create stock movement
                $unitStr = $support->satuanRelation->kode ?? $support->satuan ?? 'pcs';
                $stock->addLayer('support', $support->id, $detail->jumlah, $unitStr, $detail->harga_satuan, 'purchase', $pembelian->id, $pembelian->tanggal);
                
                $this->line('  Created movement for ' . $support->nama_bahan . ' - Qty: ' . $detail->jumlah . ' on ' . $pembelian->tanggal);
                
            } catch (\Exception $e) {
                $this->error('  Error creating movement for ' . $support->nama_bahan . ': ' . $e->getMessage());
            }
        }
        
        // Fix stock master after creating movements
        $this->info('');
        $this->info('Fixing stock master...');
        
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
            }
        }
        
        $this->info('');
        $this->info('Support stock movements created successfully!');
        
        return 0;
    }
}
