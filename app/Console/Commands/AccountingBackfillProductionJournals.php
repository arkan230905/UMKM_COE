<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use App\Models\JournalEntry;
use App\Models\StockMovement;
use App\Services\JournalService;

class AccountingBackfillProductionJournals extends Command
{
    protected $signature = 'accounting:backfill-production-journals {--dry-run : Tampilkan rencana tanpa menulis data}';
    protected $description = 'Backfill jurnal produksi (material->WIP, BTKL/BOP->WIP, WIP->Barang Jadi) untuk produksi yang belum memiliki jurnal';

    public function handle(JournalService $journal): int
    {
        $dry = (bool)$this->option('dry-run');
        $posted = 0; $skipped = 0;

        $items = Produksi::orderBy('id')->get();
        foreach ($items as $p) {
            $tanggal = (string)($p->tanggal ?? now()->toDateString());

            // 1) Material -> WIP
            $hasMat = JournalEntry::where('ref_type','production_material')->where('ref_id',$p->id)->exists();
            if (!$hasMat) {
                $matCost = (float) StockMovement::where('item_type','material')
                    ->where('direction','out')
                    ->where('ref_type','production')
                    ->where('ref_id',$p->id)
                    ->sum('total_cost');
                if ($matCost > 0) {
                    if ($dry) {
                        $this->line("DRY: production_material produksi#{$p->id} tanggal={$tanggal} cost={$matCost}");
                    } else {
                        try {
                            $journal->post($tanggal, 'production_material', (int)$p->id, 'Backfill konsumsi bahan ke WIP', [
                                ['code' => '122', 'debit' => $matCost, 'credit' => 0],
                                ['code' => '121', 'debit' => 0, 'credit' => $matCost],
                            ]);
                            $posted++;
                        } catch (\Exception $e) {
                            $this->error("ERROR posting production_material for produksi#{$p->id}: " . $e->getMessage());
                        }
                    }
                }
            } else { $skipped++; }

            // 2) BTKL/BOP -> WIP
            $hasLB = JournalEntry::where('ref_type','production_labor_overhead')->where('ref_id',$p->id)->exists();
            if (!$hasLB) {
                $btkl = (float)($p->total_btkl ?? 0);
                $bop  = (float)($p->total_bop ?? 0);
                $sum  = $btkl + $bop;
                if ($sum > 0) {
                    if ($dry) {
                        $this->line("DRY: production_labor_overhead produksi#{$p->id} tanggal={$tanggal} btkl={$btkl} bop={$bop}");
                    } else {
                        try {
                            $lines = [ ['code' => '122', 'debit' => $sum, 'credit' => 0] ];
                            if ($btkl > 0) { $lines[] = ['code' => '211', 'debit' => 0, 'credit' => $btkl]; }
                            if ($bop  > 0) { $lines[] = ['code' => '212', 'debit' => 0, 'credit' => $bop]; }
                            $journal->post($tanggal, 'production_labor_overhead', (int)$p->id, 'Backfill BTKL/BOP ke WIP', $lines);
                            $posted++;
                        } catch (\Exception $e) {
                            $this->error("ERROR posting production_labor_overhead for produksi#{$p->id}: " . $e->getMessage());
                        }
                    }
                }
            } else { $skipped++; }

            // 3) Selesai produksi: WIP -> Barang Jadi
            $hasFin = JournalEntry::where('ref_type','production_finish')->where('ref_id',$p->id)->exists();
            if (!$hasFin) {
                $fgCost = (float) StockMovement::where('item_type','product')
                    ->where('direction','in')
                    ->where('ref_type','production')
                    ->where('ref_id',$p->id)
                    ->sum('total_cost');
                if ($fgCost > 0) {
                    if ($dry) {
                        $this->line("DRY: production_finish produksi#{$p->id} tanggal={$tanggal} cost={$fgCost}");
                    } else {
                        try {
                            $journal->post($tanggal, 'production_finish', (int)$p->id, 'Backfill selesai produksi', [
                                ['code' => '123', 'debit' => $fgCost, 'credit' => 0],
                                ['code' => '122', 'debit' => 0, 'credit' => $fgCost],
                            ]);
                            $posted++;
                        } catch (\Exception $e) {
                            $this->error("ERROR posting production_finish for produksi#{$p->id}: " . $e->getMessage());
                        }
                    }
                }
            } else { $skipped++; }
        }

        $this->info("Selesai. Diposting: {$posted}, dilewati: {$skipped}." . ($dry ? ' (DRY RUN)' : ''));
        return self::SUCCESS;
    }
}
