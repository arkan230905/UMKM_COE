<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use Carbon\Carbon;

class UpdateSaldoAwalBahanBakuCorrect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-saldo-awal-bahan-baku-correct';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update saldo awal bahan baku dengan data yang benar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Data saldo awal bahan baku yang benar
        $saldoAwalData = [
            'Ayam Potong' => 50,      // 50 Kg
            'Ayam Kampung' => 40,     // 40 Ekor
            'Bebek' => 50,            // 50 Ekor
        ];

        $tanggalSaldoAwal = Carbon::create(2026, 4, 1)->startOfDay();
        $updated = 0;
        $notFound = [];

        foreach ($saldoAwalData as $namaBahan => $saldoAwal) {
            $bahanBaku = BahanBaku::where('nama_bahan', $namaBahan)->first();

            if ($bahanBaku) {
                $bahanBaku->update([
                    'saldo_awal' => $saldoAwal,
                    'tanggal_saldo_awal' => $tanggalSaldoAwal,
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
