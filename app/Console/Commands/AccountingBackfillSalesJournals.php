<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Models\JournalEntry;
use App\Models\StockMovement;
use App\Services\JournalService;

class AccountingBackfillSalesJournals extends Command
{
    protected $signature = 'accounting:backfill-sales-journals {--dry-run : Tampilkan rencana tanpa menulis data}';
    protected $description = 'Backfill jurnal penjualan (Kas/Piutang vs Penjualan) dan HPP (HPP vs Persediaan Barang Jadi) untuk penjualan yang belum memiliki jurnal';

    public function handle(JournalService $journal): int
    {
        $dry = (bool)$this->option('dry-run');
        $posted = 0; $skipped = 0;

        $items = Penjualan::orderBy('id')->get();
        foreach ($items as $p) {
            $tanggal = (string)($p->tanggal ?? now()->toDateString());
            $total   = (float)($p->total ?? 0);

            // 1) Jurnal penjualan
            $hasSale = JournalEntry::where('ref_type','sale')->where('ref_id',$p->id)->exists();
            if (!$hasSale && $total > 0) {
                if ($dry) {
                    $this->line("DRY: sale penjualan#{$p->id} tanggal={$tanggal} total={$total}");
                } else {
                    $journal->post($tanggal, 'sale', (int)$p->id, 'Backfill Penjualan', [
                        ['code' => '101', 'debit' => $total, 'credit' => 0], // asumsi kas; dapat diganti piutang jika diperlukan
                        ['code' => '401', 'debit' => 0, 'credit' => $total],
                    ]);
                }
                $posted++;
            } else { $skipped++; }

            // 2) Jurnal HPP
            $hasCogs = JournalEntry::where('ref_type','sale_cogs')->where('ref_id',$p->id)->exists();
            if (!$hasCogs) {
                $cogs = (float) StockMovement::where('item_type','product')
                    ->where('direction','out')
                    ->where('ref_type','sale')
                    ->where('ref_id',$p->id)
                    ->sum('total_cost');
                if ($cogs > 0) {
                    if ($dry) {
                        $this->line("DRY: sale_cogs penjualan#{$p->id} tanggal={$tanggal} cogs={$cogs}");
                    } else {
                        $journal->post($tanggal, 'sale_cogs', (int)$p->id, 'Backfill HPP Penjualan', [
                            ['code' => '501', 'debit' => $cogs, 'credit' => 0],
                            ['code' => '123', 'debit' => 0, 'credit' => $cogs],
                        ]);
                    }
                    $posted++;
                }
            } else { $skipped++; }
        }

        $this->info("Selesai. Diposting: {$posted}, dilewati: {$skipped}." . ($dry ? ' (DRY RUN)' : ''));
        return self::SUCCESS;
    }
}
