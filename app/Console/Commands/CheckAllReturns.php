<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Retur;
use App\Models\ReturDetail;

class CheckAllReturns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-all-returns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all returns and their details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking All Returns...');
        
        $returns = Retur::with('details')->orderBy('created_at', 'desc')->get();
        
        foreach ($returns as $retur) {
            $this->info('');
            $this->line('Retur #' . $retur->id . ' - Status: ' . $retur->status . ' - Total: Rp ' . number_format($retur->jumlah, 2));
            $this->line('Tanggal: ' . $retur->tanggal);
            
            foreach ($retur->details as $detail) {
                if ($detail->bahan_baku_id) {
                    $item = \App\Models\BahanBaku::find($detail->bahan_baku_id);
                    $this->line('  - Material: ' . ($item ? $item->nama_bahan : 'Unknown') . ' Qty: ' . $detail->qty . ' Harga: ' . number_format($detail->harga_satuan, 2));
                } elseif ($detail->bahan_pendukung_id) {
                    $item = \App\Models\BahanPendukung::find($detail->bahan_pendukung_id);
                    $this->line('  - Support: ' . ($item ? $item->nama_bahan : 'Unknown') . ' Qty: ' . $detail->qty . ' Harga: ' . number_format($detail->harga_satuan, 2));
                }
            }
        }
        
        return 0;
    }
}
