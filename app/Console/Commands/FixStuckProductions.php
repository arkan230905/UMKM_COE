<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Produksi;
use App\Models\ProduksiProses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixStuckProductions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'produksi:fix-stuck {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix productions that are stuck in "dalam_proses" status even though all processes are completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in DRY-RUN mode. No changes will be made.');
        }

        // Find productions that are stuck
        $stuckProductions = Produksi::where('status', 'dalam_proses')
            ->get()
            ->filter(function($produksi) {
                // Count total processes
                $totalProses = ProduksiProses::where('produksi_id', $produksi->id)
                    ->where('user_id', $produksi->user_id)
                    ->count();
                
                // Count completed processes
                $prosesSelesai = ProduksiProses::where('produksi_id', $produksi->id)
                    ->where('user_id', $produksi->user_id)
                    ->where('status', 'selesai')
                    ->count();
                
                // This production is stuck if all processes are complete but status is still "dalam_proses"
                return $totalProses > 0 && $prosesSelesai >= $totalProses;
            });

        if ($stuckProductions->isEmpty()) {
            $this->info('No stuck productions found. All good!');
            return 0;
        }

        $this->info("Found {$stuckProductions->count()} stuck production(s):");
        $this->newLine();

        foreach ($stuckProductions as $produksi) {
            $totalProses = ProduksiProses::where('produksi_id', $produksi->id)
                ->where('user_id', $produksi->user_id)
                ->count();
            
            $prosesSelesai = ProduksiProses::where('produksi_id', $produksi->id)
                ->where('user_id', $produksi->user_id)
                ->where('status', 'selesai')
                ->count();

            $this->line("ID: {$produksi->id}");
            $this->line("  Produk: {$produksi->produk->nama_produk}");
            $this->line("  Tanggal: {$produksi->tanggal->format('Y-m-d')}");
            $this->line("  Status: {$produksi->status}");
            $this->line("  Proses: {$prosesSelesai}/{$totalProses} selesai");
            $this->line("  Total Proses Field: {$produksi->total_proses}");
            $this->line("  Proses Selesai Field: {$produksi->proses_selesai}");

            if (!$dryRun) {
                DB::transaction(function() use ($produksi, $totalProses, $prosesSelesai) {
                    // Update fields
                    $produksi->update([
                        'status' => 'selesai',
                        'total_proses' => $totalProses,
                        'proses_selesai' => $prosesSelesai,
                        'proses_saat_ini' => null,
                        'waktu_selesai_produksi' => $produksi->waktu_selesai_produksi ?? now(),
                    ]);

                    Log::info('Fixed stuck production', [
                        'produksi_id' => $produksi->id,
                        'produk' => $produksi->produk->nama_produk,
                        'total_proses' => $totalProses,
                        'proses_selesai' => $prosesSelesai,
                        'previous_status' => 'dalam_proses',
                        'new_status' => 'selesai'
                    ]);
                });

                $this->info("  ✓ Fixed! Status updated to 'selesai'");
            } else {
                $this->comment("  Would update status to 'selesai'");
            }
            
            $this->newLine();
        }

        if (!$dryRun) {
            $this->info("Successfully fixed {$stuckProductions->count()} production(s)!");
        } else {
            $this->info("DRY-RUN complete. Run without --dry-run to apply changes.");
        }

        return 0;
    }
}
