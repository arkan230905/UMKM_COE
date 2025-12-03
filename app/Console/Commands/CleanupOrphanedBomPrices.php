<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class CleanupOrphanedBomPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bom:cleanup-orphaned-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset harga_bom untuk produk yang tidak memiliki BOM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Mencari produk dengan harga_bom tapi tidak punya BOM...');
        $this->newLine();

        // Cari produk yang punya harga_bom tapi tidak punya BOM
        $produks = Produk::whereDoesntHave('boms')
            ->where(function($query) {
                $query->where('harga_bom', '>', 0)
                      ->orWhereNotNull('harga_bom');
            })
            ->get();

        if ($produks->isEmpty()) {
            $this->info('✅ Tidak ada produk yang perlu dibersihkan.');
            return 0;
        }

        $this->info("Ditemukan {$produks->count()} produk yang perlu dibersihkan:");
        $this->newLine();

        $bar = $this->output->createProgressBar($produks->count());
        $bar->start();

        $cleaned = 0;

        foreach ($produks as $produk) {
            try {
                DB::beginTransaction();

                $oldHargaBom = $produk->harga_bom;
                $oldHargaJual = $produk->harga_jual;

                // Reset harga_bom ke 0
                $produk->update([
                    'harga_bom' => 0,
                ]);

                DB::commit();

                $this->newLine();
                $this->line("✓ {$produk->nama_produk}");
                $this->line("  Harga BOM: Rp " . number_format($oldHargaBom, 0, ',', '.') . " → Rp 0");
                
                $cleaned++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->newLine();
                $this->error("✗ Error: {$produk->nama_produk} - " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Cleanup selesai!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Total Produk Ditemukan', $produks->count()],
                ['Berhasil Dibersihkan', $cleaned],
                ['Gagal', $produks->count() - $cleaned],
            ]
        );

        $this->newLine();
        $this->info('💡 Catatan:');
        $this->line('   - Harga BOM sudah direset ke 0');
        $this->line('   - Harga jual tidak diubah (tetap seperti semula)');
        $this->line('   - Jika ingin membuat BOM baru, silakan buat dari menu BOM');

        return 0;
    }
}
