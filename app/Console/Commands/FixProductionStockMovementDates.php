<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockMovement;
use App\Models\Produksi;

class FixProductionStockMovementDates extends Command
{
    protected $signature = 'app:fix-production-stock-movement-dates';
    protected $description = 'Fix production stock movement dates to match production dates';

    public function handle()
    {
        $this->info('Fixing Production Stock Movement Dates...');
        
        // Get all production stock movements
        $movements = StockMovement::where('ref_type', 'production')
            ->orderBy('tanggal', 'asc')
            ->get();
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($movements as $movement) {
            // Get the production record
            $produksi = Produksi::find($movement->ref_id);
            
            if (!$produksi) {
                $this->warn("Production ID {$movement->ref_id} not found for movement ID {$movement->id}");
                $skipped++;
                continue;
            }
            
            $correctDate = $produksi->tanggal->format('Y-m-d');
            $currentDate = $movement->tanggal;
            
            if ($currentDate !== $correctDate) {
                $movement->update(['tanggal' => $correctDate]);
                $this->line("Updated movement ID {$movement->id}: {$currentDate} → {$correctDate}");
                $updated++;
            } else {
                $skipped++;
            }
        }
        
        $this->info('');
        $this->info("Stock movements updated: {$updated}, already correct: {$skipped}");
        
        return 0;
    }
}
