<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembelian;
use App\Models\PembelianDetail;

class CheckSupportPurchases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-support-purchases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check support material purchases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Support Material Purchases...');
        
        // 1. Check pembelian details with support materials
        $this->info('');
        $this->info('1. Purchase Details with Support Materials:');
        $supportDetails = PembelianDetail::whereNotNull('bahan_pendukung_id')
            ->with(['bahanPendukung', 'pembelian'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        foreach ($supportDetails as $detail) {
            $itemName = $detail->bahanPendukung ? $detail->bahanPendukung->nama_bahan : 'Unknown';
            $this->line('  ' . $itemName . ' - Qty: ' . $detail->qty . 
                ' @ ' . number_format($detail->harga_satuan, 2) . 
                ' - Purchase #' . $detail->pembelian_id . 
                ' (' . $detail->pembelian->tanggal . ')');
        }
        
        // 2. Check if there are any support materials at all
        $this->info('');
        $this->info('2. Support Materials Summary:');
        $totalSupportDetails = PembelianDetail::whereNotNull('bahan_pendukung_id')->count();
        $this->line('  Total purchase details with support materials: ' . $totalSupportDetails);
        
        // 3. Check if support materials should have initial stock
        $this->info('');
        $this->info('3. Initial Stock Check:');
        $supports = \App\Models\BahanPendukung::all();
        foreach ($supports as $support) {
            $this->line('  ' . $support->nama_bahan . ': Current stock = ' . $support->stok);
        }
        
        return 0;
    }
}
