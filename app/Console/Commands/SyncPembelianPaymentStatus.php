<?php

namespace App\Console\Commands;

use App\Models\Pembelian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPembelianPaymentStatus extends Command
{
    protected $signature = 'pembelian:sync-payment-status';
    protected $description = 'Sync pembelian payment status from pelunasan records';

    public function handle()
    {
        $this->info('🔄 Syncing pembelian payment status...');
        
        $pembelians = Pembelian::all();
        $updated = 0;
        
        foreach ($pembelians as $pembelian) {
            $oldStatus = $pembelian->status;
            $oldTerbayar = $pembelian->terbayar;
            
            // Use the new sync method
            $pembelian->syncPaymentStatus();
            
            // Check if needs update
            if ($pembelian->status != $oldStatus || $pembelian->terbayar != $oldTerbayar) {
                $this->line("Updating Pembelian ID {$pembelian->id} ({$pembelian->nomor_pembelian}):");
                $this->line("  - Payment Method: {$pembelian->payment_method}");
                $this->line("  - Terbayar: Rp " . number_format($oldTerbayar ?? 0, 0, ',', '.') . " → Rp " . number_format($pembelian->terbayar, 0, ',', '.'));
                $this->line("  - Status: {$oldStatus} → {$pembelian->status}");
                
                $pembelian->save();
                $updated++;
            }
        }
        
        $this->info("✅ Synced {$updated} pembelian records!");
        
        return 0;
    }
}
