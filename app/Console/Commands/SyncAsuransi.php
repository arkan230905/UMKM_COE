<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Jabatan;
use Illuminate\Support\Facades\DB;

class SyncAsuransi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:asuransi {--set-zero : Set all asuransi to 0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi asuransi di kualifikasi tenaga kerja. Default: tampilkan, --set-zero: set semua jadi 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('set-zero')) {
            // Update ALL asuransi to 0
            $updated = DB::table('jabatans')->update(['asuransi' => 0]);
            $this->info("✓ Updated $updated records: semua asuransi di kualifikasi → 0");
            return Command::SUCCESS;
        }

        // Tampilkan semua kualifikasi dengan asuransi != 0
        $jabatans = DB::table('jabatans')
            ->whereRaw('asuransi IS NOT NULL AND asuransi != 0')
            ->get(['id', 'nama', 'asuransi']);

        if ($jabatans->isEmpty()) {
            $this->info("✓ OK: Semua kualifikasi sudah punya asuransi = 0 atau NULL");
            return Command::SUCCESS;
        }

        $this->warn("⚠ Ditemukan kualifikasi dengan asuransi != 0:");
        $this->table(['ID', 'Nama', 'Asuransi'], $jabatans->map(fn($j) => [
            $j->id,
            $j->nama,
            $j->asuransi
        ])->toArray());

        $this->newLine();
        $this->info("Run: php artisan sync:asuransi --set-zero (untuk set semua jadi 0)");

        return Command::SUCCESS;
    }
}
