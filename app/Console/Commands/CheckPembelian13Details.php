<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;

class CheckPembelian13Details extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-pembelian13-details';

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
        $this->info('Checking Pembelian ID 13 Details...');
        
        $pembelian = Pembelian::with(['vendor', 'details'])->find(13);
        
        if ($pembelian) {
            $this->info('');
            $this->info('Pembelian Found:');
            $this->line('  ID: ' . $pembelian->id);
            $this->line('  Nomor: ' . ($pembelian->nomor_pembelian ?? 'NULL'));
            $this->line('  Tanggal: ' . $pembelian->tanggal);
            $this->line('  Total: ' . $pembelian->total);
            $this->line('  Vendor: ' . ($pembelian->vendor->nama_vendor ?? 'Unknown'));
            $this->line('  Status: ' . $pembelian->status);
            
            $this->info('');
            $this->info('Details:');
            foreach ($pembelian->details as $index => $detail) {
                $this->line('  Detail ' . ($index + 1) . ':');
                $this->line('    ID: ' . $detail->id);
                $this->line('    Bahan Baku ID: ' . $detail->bahan_baku_id);
                $this->line('    Bahan Pendukung ID: ' . ($detail->bahan_pendukung_id ?? 'NULL'));
                $this->line('    Jumlah: ' . $detail->jumlah);
                $this->line('    Harga: ' . $detail->harga_satuan);
                
                // Check bahan baku
                if ($detail->bahan_baku_id) {
                    $bahanBaku = \App\Models\BahanBaku::find($detail->bahan_baku_id);
                    if ($bahanBaku) {
                        $this->line('    Bahan Baku: ' . $bahanBaku->nama_bahan);
                    } else {
                        $this->error('    Bahan Baku ID ' . $detail->bahan_baku_id . ' NOT FOUND!');
                    }
                }
                
                // Check bahan pendukung
                if ($detail->bahan_pendukung_id) {
                    $bahanPendukung = \App\Models\BahanPendukung::find($detail->bahan_pendukung_id);
                    if ($bahanPendukung) {
                        $this->line('    Bahan Pendukung: ' . $bahanPendukung->nama_bahan);
                    } else {
                        $this->error('    Bahan Pendukung ID ' . $detail->bahan_pendukung_id . ' NOT FOUND!');
                    }
                }
                
                $this->line('');
            }
        } else {
            $this->error('Pembelian ID 13 NOT FOUND!');
        }
        
        return 0;
    }
}
