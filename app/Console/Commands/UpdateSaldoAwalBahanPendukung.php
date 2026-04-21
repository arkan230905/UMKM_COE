<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanPendukung;
use Carbon\Carbon;

class UpdateSaldoAwalBahanPendukung extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-saldo-awal-bahan-pendukung';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update saldo awal untuk semua bahan pendukung';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Data saldo awal bahan pendukung
        $saldoAwalData = [
            'Tepung Terigu' => 400,
            'Tepung Maizena' => 400,
            'Lada' => 400,
            'Bubuk Kaldu Ayam' => 400,
            'Bubuk Bawang Putih' => 400,
        ];

        $updated = 0;
        $notFound = [];

        foreach ($saldoAwalData as $namaBahan => $saldoAwal) {
            $bahanPendukung = BahanPendukung::where('nama_bahan', $namaBahan)->first();

            if ($bahanPendukung) {
                $bahanPendukung->update([
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
