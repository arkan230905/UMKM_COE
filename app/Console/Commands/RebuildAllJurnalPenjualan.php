<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penjualan;
use App\Services\JournalService;

class RebuildAllJurnalPenjualan extends Command
{
    protected $signature   = 'jurnal:rebuild-all-penjualan {--user-id=}';
    protected $description = 'Rebuild jurnal untuk semua transaksi penjualan (atau user tertentu)';

    public function handle(): int
    {
        $userId = $this->option('user-id');
        
        $query = Penjualan::with('details.produk', 'produk');
        
        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("Rebuilding jurnal penjualan untuk user ID {$userId}...");
        } else {
            $this->info("Rebuilding jurnal penjualan untuk semua user...");
        }
        
        $penjualans = $query->get();
        $total = $penjualans->count();
        
        if ($total === 0) {
            $this->warn("Tidak ada transaksi penjualan yang ditemukan.");
            return 0;
        }
        
        $this->info("Total transaksi: {$total}");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $success = 0;
        $failed = 0;
        $errors = [];
        
        foreach ($penjualans as $penjualan) {
            try {
                // Set auth untuk user penjualan
                if ($penjualan->user_id) {
                    \Illuminate\Support\Facades\Auth::loginUsingId($penjualan->user_id);
                }
                
                JournalService::createJournalFromPenjualan($penjualan);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'penjualan_id' => $penjualan->id,
                    'nomor_penjualan' => $penjualan->nomor_penjualan,
                    'error' => $e->getMessage()
                ];
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Selesai!");
        $this->info("Berhasil: {$success}");
        $this->error("Gagal: {$failed}");
        
        if (!empty($errors)) {
            $this->newLine();
            $this->error("Errors:");
            foreach ($errors as $error) {
                $this->line("  - Penjualan #{$error['nomor_penjualan']} (ID: {$error['penjualan_id']}): {$error['error']}");
            }
        }
        
        return $failed > 0 ? 1 : 0;
    }
}
