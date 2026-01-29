<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;

class UpdateTransferStatus extends Command
{
    protected $signature = 'pembelian:update-transfer-status';
    protected $description = 'Update transfer pembelian status to LUNAS';

    public function handle()
    {
        $this->info('Updating transfer pembelian status to LUNAS...');

        // Update semua pembelian dengan payment_method = transfer dan status = belum_lunas
        $updated = Pembelian::where('payment_method', 'transfer')
            ->where('status', 'belum_lunas')
            ->update([
                'status' => 'lunas',
                'terbayar' => \DB::raw('total_harga'),
                'sisa_pembayaran' => 0
            ]);

        $this->info("Updated {$updated} pembelian records");

        // Tampilkan hasil
        $pembelians = Pembelian::where('payment_method', 'transfer')->get();
        $this->info("\nTransfer Pembelian Status Summary:");
        $this->info("=====================================");

        foreach ($pembelians as $p) {
            $this->info("ID: {$p->id} | Status: {$p->status} | Terbayar: Rp " . number_format($p->terbayar, 0, ',', '.') . " | Sisa: Rp " . number_format($p->sisa_pembayaran, 0, ',', '.'));
        }

        $this->info("\nUpdate completed successfully!");
        return Command::SUCCESS;
    }
}
