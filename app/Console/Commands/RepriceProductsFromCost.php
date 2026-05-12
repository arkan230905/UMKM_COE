<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\StockLayer;

class RepriceProductsFromCost extends Command
{
    protected $signature = 'pricing:reprice-from-cost {--markup=1.6 : Markup multiplier, e.g., 1.6 for 60%} {--dry-run : Show what would change without saving}';
    protected $description = 'Set produk.harga_jual = latest unit cost (from production stock layer) x markup';

    public function handle(): int
    {
        $markup = (float)$this->option('markup');
        $dry = (bool)$this->option('dry-run');
        $updated = 0; $skipped = 0;

        $produks = Produk::orderBy('id')->get();
        foreach ($produks as $p) {
            $layer = StockLayer::where('item_type','product')
                ->where('item_id',$p->id)
                ->orderBy('tanggal','desc')
                ->orderBy('id','desc')
                ->first();
            if (!$layer) { $skipped++; continue; }
            $unitCost = (float)($layer->unit_cost ?? 0);
            if ($unitCost <= 0) { $skipped++; continue; }
            $newPrice = (int) round($unitCost * $markup);
            if ($dry) {
                $this->line("DRY: produk#{$p->id} {$p->nama_produk} unit_cost={$unitCost} markup={$markup} new_harga_jual={$newPrice}");
            } else {
                $p->harga_jual = $newPrice;
                $p->save();
            }
            $updated++;
        }

        $this->info("Reprice selesai. Diupdate: {$updated}, dilewati: {$skipped}." . ($dry ? ' (DRY RUN)' : ''));
        return self::SUCCESS;
    }
}
