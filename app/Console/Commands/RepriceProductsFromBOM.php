<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use App\Models\Bom;
use App\Support\UnitConverter;

class RepriceProductsFromBOM extends Command
{
    protected $signature = 'pricing:reprice-from-bom {--markup=1.6 : Markup multiplier, e.g., 1.6 for 60%} {--dry-run : Show what would change without saving}';
    protected $description = 'Set produk.harga_jual = (biaya BOM per unit + BTKL + BOP) x markup';

    public function handle(): int
    {
        $markup = (float)$this->option('markup');
        $dry = (bool)$this->option('dry-run');
        $updated = 0; $skipped = 0;
        $conv = new UnitConverter();

        $produks = Produk::orderBy('id')->get();
        foreach ($produks as $p) {
            // Hitung biaya bahan per unit dari BOM
            $bomItems = Bom::with('bahanBaku')->where('produk_id', $p->id)->get();
            if ($bomItems->isEmpty()) { $skipped++; continue; }

            $bahanCostPerUnit = 0.0;
            foreach ($bomItems as $it) {
                $bahan = $it->bahanBaku;
                if (!$bahan) { continue; }
                $qtyResep = (float)($it->jumlah ?? 0);
                $satuanResep = $it->satuan_resep ?: (string)$bahan->satuan;
                $qtyBase = $conv->convert($qtyResep, (string)$satuanResep, (string)$bahan->satuan);
                $unitCostBahan = (float)($bahan->harga_satuan ?? 0);
                $bahanCostPerUnit += $qtyBase * $unitCostBahan;
            }

            // BTKL & BOP per unit (gunakan default produk jika ada, jika tidak pakai persen dari bahan)
            $btklPerUnit = (float)($p->btkl_default ?? 0);
            $bopPerUnit  = (float)($p->bop_default ?? 0);
            if ($btklPerUnit <= 0 || $bopPerUnit <= 0) {
                $btklRate = (float) (config('app.btkl_percent') ?? 0.2);
                $bopRate  = (float) (config('app.bop_percent') ?? 0.1);
                if ($btklPerUnit <= 0) $btklPerUnit = $bahanCostPerUnit * $btklRate;
                if ($bopPerUnit  <= 0) $bopPerUnit  = $bahanCostPerUnit * $bopRate;
            }

            $unitCost = $bahanCostPerUnit + $btklPerUnit + $bopPerUnit;
            $newPrice = (int) round($unitCost * max($markup, 1.0));

            if ($dry) {
                $this->line("DRY: produk#{$p->id} {$p->nama_produk} bahan={$bahanCostPerUnit} btkl={$btklPerUnit} bop={$bopPerUnit} unit_cost={$unitCost} -> harga_jual={$newPrice}");
            } else {
                $p->harga_jual = $newPrice;
                $p->save();
            }
            $updated++;
        }

        $this->info("Reprice dari BOM selesai. Diupdate: {$updated}, dilewati: {$skipped}." . ($dry ? ' (DRY RUN)' : ''));
        return self::SUCCESS;
    }
}
