<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use Carbon\Carbon;

class UpdateSaldoAwalBahanBaku extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-saldo-awal-bahan-baku';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update saldo awal untuk semua bahan baku';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Data saldo awal bahan baku
        $saldoAwalData = [
            'Ayam Potong' => 10,      // 10 Ekor
            'Ayam Kampung' => 5,      // 5 Ekor
            'Bebek' => 10,            // 10 Ekor
        ];

        $updated = 0;
        $notFound = [];

        foreach ($saldoAwalData as $namaBahan => $saldoAwal) {
            $bahanBaku = BahanBaku::where('nama_bahan', $namaBahan)->first();

            if ($bahanBaku) {
                $bahanBaku->update([
                    'saldo_awal' => $saldoAwal,
                    'tanggal_saldo_awal' => Carbon::now()->startOfDay(),
                ]);
                $this->info("✓ {$namaBahan}: saldo awal diupdate menjadi {$saldoAwal}");
                $updated++;
            } else {
                $this->warn("✗ {$namaBahan}: tidak ditemukan di database");
                $notFound[] = $namaBahan;
            }
        }

        $this->newLine();
        $this->info("Total diupdate: {$updated}");
        if (!empty($notFound)) {
            $this->warn("Tidak ditemukan: " . implode(', ', $notFound));
        }
    }
}
