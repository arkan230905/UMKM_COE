<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Produksi;

class AccountingCleanOrphans extends Command
{
    protected $signature = 'accounting:clean-orphans {--dry-run : Tampilkan saja tanpa menghapus}';
    protected $description = 'Hapus jurnal yang referensinya sudah tidak ada (orphan)';

    public function handle(): int
    {
        $dry = (bool)$this->option('dry-run');
        $map = [
            'purchase' => [Pembelian::class],
            'sale' => [Penjualan::class],
            'sale_cogs' => [Penjualan::class],
            'production_material' => [Produksi::class],
            'production_labor_overhead' => [Produksi::class],
            'production_finish' => [Produksi::class],
        ];

        $total = 0; $skipped = 0;
        $entries = JournalEntry::orderBy('id')->get();
        foreach ($entries as $e) {
            $types = $map[$e->ref_type] ?? null;
            if (!$types) { $skipped++; continue; }
            $exists = false;
            foreach ($types as $cls) {
                if ($cls::where('id', $e->ref_id)->exists()) { $exists = true; break; }
            }
            if (!$exists) {
                if ($dry) {
                    $this->line("DRY: delete entry id={$e->id} ref={$e->ref_type}#{$e->ref_id}");
                } else {
                    $e->delete();
                }
                $total++;
            }
        }
        $this->info("Selesai. Orphan dihapus: {$total}, dilewati: {$skipped}." . ($dry ? ' (DRY RUN)' : ''));
        return self::SUCCESS;
    }
}
