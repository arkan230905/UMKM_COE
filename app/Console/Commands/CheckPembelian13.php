<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;

class CheckPembelian13 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-pembelian13';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pembelian ID 13 details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Pembelian ID 13...');
        
        $pembelian = Pembelian::with(['vendor', 'details'])->find(13);
        
        if ($pembelian) {
            $this->info('Pembelian Found:');
            $this->line('  ID: ' . $pembelian->id);
            $this->line('  No. Pembelian: ' . ($pembelian->no_pembelian ?? 'NULL'));
            $this->line('  Tanggal: ' . $pembelian->tanggal);
            $this->line('  Total: ' . $pembelian->total);
            $this->line('  Vendor: ' . ($pembelian->vendor->nama_vendor ?? 'Unknown'));
            $this->line('  Status: ' . $pembelian->status);
            
            $this->info('Details:');
            foreach ($pembelian->details as $detail) {
                $this->line('  - ' . ($detail->bahan_baku->nama_bahan ?? $detail->bahan_pendukung->nama_bahan ?? 'Unknown') . ' Qty: ' . $detail->jumlah);
            }
        } else {
            $this->error('Pembelian ID 13 NOT FOUND!');
        }
        
        return 0;
    }
}
