<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembelianDetail;

class CheckPurchaseDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-purchase-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check purchase details data integrity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Purchase Details Data...');
        
        // 1. Check all purchase details
        $this->info('');
        $this->info('1. All Purchase Details (Recent):');
        $details = PembelianDetail::with(['bahanBaku', 'bahanPendukung', 'pembelian'])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
            
        foreach ($details as $detail) {
            if ($detail->bahan_pendukung_id) {
                $itemName = $detail->bahanPendukung ? $detail->bahanPendukung->nama_bahan : 'Unknown Support';
                $type = 'SUPPORT';
            } elseif ($detail->bahan_baku_id) {
                $itemName = $detail->bahanBaku ? $detail->bahanBaku->nama_bahan : 'Unknown Material';
                $type = 'MATERIAL';
            } else {
                $itemName = 'Unknown';
                $type = 'UNKNOWN';
            }
            
            $this->line('  Purchase #' . $detail->pembelian_id . ' - ' . $type . ' - ' . $itemName);
            $this->line('    Jumlah: ' . $detail->jumlah . ' | Price: ' . number_format($detail->harga_satuan, 2) . ' | Subtotal: ' . number_format($detail->subtotal, 2));
            
            if (empty($detail->jumlah) || $detail->jumlah <= 0) {
                $this->error('    *** MISSING OR INVALID JUMLAH! ***');
            }
        }
        
        // 2. Check for problematic records
        $this->info('');
        $this->info('2. Problematic Records:');
        $problematic = PembelianDetail::where(function($query) {
                $query->whereNull('jumlah')
                      ->orWhere('jumlah', '<=', 0);
            })
            ->with(['bahanBaku', 'bahanPendukung'])
            ->get();
            
        foreach ($problematic as $detail) {
            $itemName = $detail->bahan_pendukung_id ? 
                ($detail->bahanPendukung ? $detail->bahanPendukung->nama_bahan : 'Unknown Support') :
                ($detail->bahanBaku ? $detail->bahanBaku->nama_bahan : 'Unknown Material');
            
            $this->line('  Purchase #' . $detail->pembelian_id . ' - ' . $itemName . ' - Jumlah: ' . $detail->jumlah);
        }
        
        return 0;
    }
}
