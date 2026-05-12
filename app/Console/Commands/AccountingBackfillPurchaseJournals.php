<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\JournalEntry;
use App\Services\JournalService;

class AccountingBackfillPurchaseJournals extends Command
{
    protected $signature = 'accounting:backfill-purchase-journals {--dry-run : Tampilkan rencana tanpa menulis data}';
    protected $description = 'Backfill jurnal pembelian (Dr Persediaan Bahan Baku, Cr Kas/Bank) untuk pembelian yang belum memiliki jurnal purchase';

    public function handle(JournalService $journal): int
    {
        $dry = (bool)$this->option('dry-run');
        $count = 0; $skip = 0;

        $pembelians = Pembelian::orderBy('id')->get();
        foreach ($pembelians as $p) {
            $exists = JournalEntry::where('ref_type','purchase')->where('ref_id',$p->id)->exists();
            if ($exists) { $skip++; continue; }
            $tanggal = (string)($p->tanggal ?? now()->toDateString());
            $total = (float)($p->total ?? 0);
            if ($dry) {
                $this->line("DRY: post purchase journal pembelian#{$p->id} tanggal={$tanggal} total={$total}");
            } else {
                $journal->post($tanggal, 'purchase', (int)$p->id, 'Pembelian Bahan Baku (backfill)', [
                    ['code' => '121', 'debit' => $total, 'credit' => 0],
                    ['code' => '101', 'debit' => 0, 'credit' => $total],
                ]);
            }
            $count++;
        }

        $this->info("Selesai. Diposting: {$count}, dilewati: {$skip}." . ($dry ? ' (DRY RUN)' : ''));
        return self::SUCCESS;
    }
}
