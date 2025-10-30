<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanTransactionalData extends Command
{
    protected $signature = 'app:clean-transactions {--force : Skip confirmation prompt}';
    protected $description = 'Kosongkan data transaksi dan jurnal (penjualan, pembelian, retur, produksi, stok, jurnal, penggajian) tanpa menghapus master data';

    public function handle(): int
    {
        if (!$this->option('force')) {
            if (!$this->confirm('PERINGATAN: Tindakan ini akan mengosongkan data transaksi dan jurnal. Lanjutkan?')) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }
        }

        // Daftar model (gunakan nama tabel dari model untuk aman)
        $models = [
            \App\Models\JournalLine::class,
            \App\Models\JournalEntry::class,
            \App\Models\StockMovement::class,
            \App\Models\StockLayer::class,
            \App\Models\PenjualanDetail::class,
            \App\Models\Penjualan::class,
            \App\Models\PembelianDetail::class,
            \App\Models\Pembelian::class,
            \App\Models\Retur::class,
            \App\Models\ProduksiDetail::class,
            \App\Models\Produksi::class,
            \App\Models\Penggajian::class,
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($models as $model) {
            try {
                $table = (new $model)->getTable();
                DB::table($table)->truncate();
                $this->line("TRUNCATE $table ... OK");
            } catch (\Throwable $e) {
                $this->warn("Lewati: $model (".$e->getMessage().")");
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Selesai mengosongkan data transaksi dan jurnal.');
        return self::SUCCESS;
    }
}
