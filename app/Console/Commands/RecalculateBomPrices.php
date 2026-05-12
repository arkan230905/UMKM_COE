<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bom;
use Illuminate\Support\Facades\DB;

class RecalculateBomPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bom:recalculate {--all : Recalculate all BOMs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate BOM prices using Process Costing method (BTKL 60%, BOP 40%)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting BOM recalculation...');
        $this->newLine();

        // Ambil semua BOM
        $boms = Bom::with(['details', 'produk'])->get();
        
        if ($boms->isEmpty()) {
            $this->warn('⚠️  No BOMs found in database.');
            return 0;
        }

        $this->info("Found {$boms->count()} BOMs to recalculate");
        $this->newLine();

        $bar = $this->output->createProgressBar($boms->count());
        $bar->start();

        $updated = 0;
        $errors = 0;

        foreach ($boms as $bom) {
            try {
                DB::beginTransaction();

                // Hitung ulang total bahan baku dari details
                $totalBahanBaku = $bom->details->sum('subtotal');

                // Hitung BTKL (60% dari total bahan baku)
                $btkl = $totalBahanBaku * 0.6;

                // Hitung BOP (40% dari total bahan baku)
                $bop = $totalBahanBaku * 0.4;

                // Hitung HPP (Harga Pokok Produksi)
                $hpp = $totalBahanBaku + $btkl + $bop;

                // Update BOM
                $bom->update([
                    'total_btkl' => $btkl,
                    'btkl_per_unit' => $btkl,
                    'total_bop' => $bop,
                    'bop_per_unit' => $bop,
                    'bop_rate' => 0.4,
                    'total_biaya' => $hpp,
                ]);

                // Update harga produk
                if ($bom->produk) {
                    $margin = $bom->produk->margin_percent ?? 0;
                    $hargaJual = $hpp * (1 + ($margin / 100));

                    $bom->produk->update([
                        'harga_bom' => $hpp,
                        'harga_jual' => $hargaJual,
                    ]);
                }

                DB::commit();
                $updated++;

            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $this->newLine();
                $this->error("❌ Error updating BOM ID {$bom->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Recalculation completed!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Total BOMs', $boms->count()],
                ['Successfully Updated', $updated],
                ['Errors', $errors],
            ]
        );

        if ($updated > 0) {
            $this->newLine();
            $this->info('📊 Summary of changes:');
            $this->line('   - BTKL recalculated to 60% of material cost');
            $this->line('   - BOP recalculated to 40% of material cost');
            $this->line('   - HPP = Material + BTKL + BOP');
            $this->line('   - Product harga_bom updated with HPP');
            $this->line('   - Product harga_jual updated with HPP + margin');
        }

        return 0;
    }
}
