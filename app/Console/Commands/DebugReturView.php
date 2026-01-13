<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Retur;

class DebugReturView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-retur-view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug retur view data loading';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Debugging Retur View Data Loading...');
        
        // Load data exactly like in controller
        $returs = Retur::with(['penjualan', 'pembelian.vendor', 'details.produk'])
            ->latest()
            ->limit(1)
            ->get();
        
        foreach ($returs as $retur) {
            $this->info('');
            $this->info('Retur ID: ' . $retur->id);
            $this->info('  Type: ' . $retur->type);
            $this->info('  Status: ' . $retur->status);
            $this->info('  Pembelian ID: ' . $retur->pembelian_id);
            
            // Check pembelian relationship
            $this->info('  Pembelian relationship loaded: ' . ($retur->relationLoaded('pembelian') ? 'YES' : 'NO'));
            if ($retur->pembelian) {
                $this->info('  Pembelian exists: YES');
                $this->info('  Pembelian Vendor relationship loaded: ' . ($retur->pembelian->relationLoaded('vendor') ? 'YES' : 'NO'));
                if ($retur->pembelian->vendor) {
                    $this->info('  Vendor Name: ' . $retur->pembelian->vendor->nama_vendor);
                } else {
                    $this->error('  Vendor is NULL!');
                }
            } else {
                $this->error('  Pembelian is NULL!');
            }
            
            // Check details
            $this->info('  Details relationship loaded: ' . ($retur->relationLoaded('details') ? 'YES' : 'NO'));
            foreach ($retur->details as $detail) {
                $this->info('    Detail ID: ' . $detail->id);
                $this->info('    Produk relationship loaded: ' . ($detail->relationLoaded('produk') ? 'YES' : 'NO'));
                if ($detail->produk) {
                    $this->info('    Produk Name: ' . $detail->produk->nama_produk);
                } else {
                    $this->error('    Produk is NULL!');
                }
            }
        }
        
        return 0;
    }
}
