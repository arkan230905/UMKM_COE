<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;

class FixPembelianTotal extends Command
{
    protected $signature = 'fix:pembelian-total';
    protected $description = 'Fix pembelian records with total = 0 by calculating from details';

    public function handle()
    {
        $this->info('Memperbaiki data pembelian dengan total = 0...');
        
        $pembelians = Pembelian::with('details')->where('total', 0)->get();
        
        $fixed = 0;
        foreach ($pembelians as $pembelian) {
            if ($pembelian->details && $pembelian->details->count() > 0) {
                $total = $pembelian->details->sum(function($detail) {
                    return ($detail->jumlah ?? 0) * ($detail->harga ?? 0);
                });
                
                if ($total > 0) {
                    $pembelian->total = $total;
                    if ($pembelian->payment_method === 'cash') {
                        $pembelian->terbayar = $total;
                        $pembelian->status = 'lunas';
                    }
                    $pembelian->save();
                    $fixed++;
                    $this->info("Fixed Pembelian ID {$pembelian->id}: Rp " . number_format($total, 0, ',', '.'));
                }
            }
        }
        
        $this->info("Selesai! Total {$fixed} pembelian diperbaiki.");
        return 0;
    }
}
