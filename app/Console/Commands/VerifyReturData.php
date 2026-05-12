<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Retur;
use App\Models\Pembelian;
use App\Models\Penjualan;

class VerifyReturData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:verify-retur-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify retur data refers to correct transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verifying Retur Data...');
        
        $returs = Retur::all();
        
        foreach ($returs as $retur) {
            $this->info('');
            $this->info('Retur ID: ' . $retur->id);
            $this->info('  Type: ' . $retur->type);
            $this->info('  Status: ' . $retur->status);
            $this->info('  Tanggal: ' . $retur->tanggal);
            $this->info('  Jumlah: ' . $retur->jumlah);
            
            if (isset($retur->pembelian_id)) {
                $this->info('  Pembelian ID: ' . $retur->pembelian_id);
                
                $pembelian = Pembelian::find($retur->pembelian_id);
                if ($pembelian) {
                    $this->info('  Pembelian Found: ' . $pembelian->no_pembelian);
                    $this->info('  Vendor: ' . ($pembelian->vendor->nama_vendor ?? 'Unknown'));
                    $this->info('  Total Pembelian: ' . $pembelian->total);
                } else {
                    $this->error('  Pembelian NOT FOUND!');
                }
            }
            
            if (isset($retur->ref_id)) {
                $this->info('  Ref ID: ' . $retur->ref_id);
                
                // Try to find penjualan
                $penjualan = Penjualan::find($retur->ref_id);
                if ($penjualan) {
                    $this->info('  Penjualan Found: ' . $penjualan->no_penjualan);
                    $this->info('  Customer: ' . ($penjualan->customer->nama_customer ?? 'Unknown'));
                    $this->info('  Total Penjualan: ' . $penjualan->total);
                } else {
                    $this->error('  Penjualan NOT FOUND!');
                }
            }
            
            // Check details
            $this->info('  Details:');
            foreach ($retur->details as $detail) {
                if (isset($detail->produk_id)) {
                    $this->info('    - Produk ID: ' . $detail->produk_id . ' Qty: ' . $detail->qty);
                }
                if (isset($detail->bahan_baku_id)) {
                    $this->info('    - Bahan Baku ID: ' . $detail->bahan_baku_id . ' Qty: ' . $detail->qty);
                }
                if (isset($detail->bahan_pendukung_id)) {
                    $this->info('    - Bahan Pendukung ID: ' . $detail->bahan_pendukung_id . ' Qty: ' . $detail->qty);
                }
            }
        }
        
        return 0;
    }
}
