<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanPendukung;
use App\Models\StockMovement;

class EnsureInitialStockForAllBahanPendukung extends Command
{
    protected $signature = 'stock:ensure-initial-bahan-pendukung';
    protected $description = 'Ensure all bahan pendukung have initial stock movements for laporan stok';

    public function handle()
    {
        $this->info('Ensuring all bahan pendukung have initial stock movements...');
        
        $bahanPendukungs = BahanPendukung::all();
        $created = 0;
        $skipped = 0;
        
        foreach ($bahanPendukungs as $bahanPendukung) {
            // Check if initial stock movement exists
            $hasInitialStock = StockMovement::where('item_type', 'support')
                ->where('item_id', $bahanPendukung->id)
                ->where('ref_type', 'initial_stock')
                ->exists();
                
            if (!$hasInitialStock) {
                // Create initial stock movement even if stok is 0
                $stokAwal = $bahanPendukung->stok ?? 0;
                $hargaRataRata = $bahanPendukung->harga_rata_rata ?? 0;
                
                StockMovement::create([
                    'item_type' => 'support',
                    'item_id' => $bahanPendukung->id,
                    'tanggal' => '2026-04-01', // Set consistent initial date
                    'direction' => 'in',
                    'qty' => $stokAwal,
                    'satuan' => $bahanPendukung->satuan->nama ?? 'Unit',
                    'unit_cost' => $hargaRataRata,
                    'total_cost' => $stokAwal * $hargaRataRata,
                    'ref_type' => 'initial_stock',
                    'ref_id' => 0,
                    'keterangan' => 'Stok awal ' . $bahanPendukung->nama_bahan,
                ]);
                
                $this->info("✓ Created initial stock for: {$bahanPendukung->nama_bahan} (Stok: {$stokAwal})");
                $created++;
            } else {
                $this->line("- Already has initial stock: {$bahanPendukung->nama_bahan}");
                $skipped++;
            }
        }
        
        $this->info("\nSummary:");
        $this->info("Created: {$created} initial stock movements");
        $this->info("Skipped: {$skipped} (already exists)");
        $this->info("Total: " . ($created + $skipped) . " bahan pendukung processed");
        
        $this->info("\n✅ All bahan pendukung now have initial stock movements for laporan stok!");
        
        return 0;
    }
}