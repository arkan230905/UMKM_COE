<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use Carbon\Carbon;

class UpdateTanggalSaldoAwal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-tanggal-saldo-awal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tanggal saldo awal menjadi 1 April 2026 untuk semua bahan baku dan bahan pendukung';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tanggalSaldoAwal = Carbon::create(2026, 4, 1)->startOfDay();

        // Update bahan baku
        $bahanBakuUpdated = BahanBaku::where('saldo_awal', '>', 0)
            ->update(['tanggal_saldo_awal' => $tanggalSaldoAwal]);

        // Update bahan pendukung
        $bahanPendukungUpdated = BahanPendukung::where('saldo_awal', '>', 0)
            ->update(['tanggal_saldo_awal' => $tanggalSaldoAwal]);

        $this->info("✓ Bahan Baku: {$bahanBakuUpdated} record diupdate");
        $this->info("✓ Bahan Pendukung: {$bahanPendukungUpdated} record diupdate");
        $this->newLine();
        $this->info("Tanggal saldo awal diubah menjadi: " . $tanggalSaldoAwal->format('d-m-Y'));
    }
}
