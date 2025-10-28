<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\JournalEntry;
use App\Models\JournalLine;

class AccountingRebuildJournals extends Command
{
    protected $signature = 'accounting:rebuild {--dry-run : Tampilkan rencana tanpa menghapus/menulis}';
    protected $description = 'Kosongkan jurnal dan bangun ulang dari transaksi yang ada (pembelian/produksi/penjualan)';

    public function handle(): int
    {
        $dry = (bool)$this->option('dry-run');

        $entries = JournalEntry::count();
        $lines   = JournalLine::count();
        if ($dry) {
            $this->info("DRY: akan menghapus journal_entries={$entries}, journal_lines={$lines}");
            $this->line('DRY: kemudian akan menjalankan backfill: purchase, production, sales');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($entries, $lines) {
            // Hapus lines terlebih dahulu, kemudian entries
            JournalLine::query()->delete();
            JournalEntry::query()->delete();
        });
        $this->info("Dihapus: journal_entries={$entries}, journal_lines={$lines}");

        // Backfill sesuai urutan logis: purchase -> production -> sales
        Artisan::call('accounting:backfill-purchase-journals');
        $this->line(trim(Artisan::output()));

        Artisan::call('accounting:backfill-production-journals');
        $this->line(trim(Artisan::output()));

        Artisan::call('accounting:backfill-sales-journals');
        $this->line(trim(Artisan::output()));

        // Bersihkan orphan jika ada hal tersisa (harusnya tidak perlu, tapi aman)
        Artisan::call('accounting:clean-orphans');
        $this->line(trim(Artisan::output()));

        $this->info('Rebuild jurnal selesai.');
        return self::SUCCESS;
    }
}
