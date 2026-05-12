<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReturDetail;

class CheckReturRefDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-retur-ref-detail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check retur ref_detail_id mapping';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Retur Ref Detail Mapping...');
        
        $returDetails = ReturDetail::where('retur_id', 5)->get();
        
        foreach ($returDetails as $detail) {
            $this->info('');
            $this->info('Retur Detail ID: ' . $detail->id);
            $this->line('  Retur ID: ' . $detail->retur_id);
            $this->line('  Produk ID: ' . $detail->produk_id);
            $this->line('  Ref Detail ID: ' . $detail->ref_detail_id);
            $this->line('  Qty: ' . $detail->qty);
            $this->line('  Harga: ' . $detail->harga_satuan_asal);
            
            // Check ref detail in pembelian_details
            if ($detail->ref_detail_id) {
                $pembelianDetail = \App\Models\PembelianDetail::find($detail->ref_detail_id);
                if ($pembelianDetail) {
                    $this->info('  Ref Pembelian Detail Found:');
                    $this->line('    ID: ' . $pembelianDetail->id);
                    $this->line('    Tipe Item: ' . $pembelianDetail->tipe_item);
                    $this->line('    Bahan Baku ID: ' . ($pembelianDetail->bahan_baku_id ?? 'NULL'));
                    $this->line('    Bahan Pendukung ID: ' . ($pembelianDetail->bahan_pendukung_id ?? 'NULL'));
                    
                    if ($pembelianDetail->bahan_pendukung_id) {
                        $bahanPendukung = \App\Models\BahanPendukung::find($pembelianDetail->bahan_pendukung_id);
                        if ($bahanPendukung) {
                            $this->line('    Bahan Pendukung: ' . $bahanPendukung->nama_bahan);
                        }
                    }
                } else {
                    $this->error('  Ref Pembelian Detail NOT FOUND!');
                }
            }
        }
        
        return 0;
    }
}
