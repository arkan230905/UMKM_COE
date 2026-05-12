<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Retur;
use App\Models\ReturDetail;

class CheckReturnDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-return-details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check return details in detail';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Return Details...');
        
        $return = Retur::with('details')->find(3);
        
        if (!$return) {
            $this->error('Return #3 not found');
            return 1;
        }
        
        $this->info('');
        $this->line('Retur #' . $return->id . ' Details:');
        $this->line('Status: ' . $return->status);
        $this->line('Total: Rp ' . number_format($return->jumlah, 2));
        $this->line('Tanggal: ' . $return->tanggal);
        
        $this->info('');
        $this->info('Items:');
        
        foreach ($return->details as $detail) {
            if ($detail->bahan_baku_id) {
                $item = \App\Models\BahanBaku::find($detail->bahan_baku_id);
                $this->line('  Material: ' . ($item ? $item->nama_bahan : 'Unknown'));
            } elseif ($detail->bahan_pendukung_id) {
                $item = \App\Models\BahanPendukung::find($detail->bahan_pendukung_id);
                $this->line('  Support: ' . ($item ? $item->nama_bahan : 'Unknown'));
            }
            
            $this->line('    Qty: ' . $detail->qty);
            $this->line('    Harga Satuan: Rp ' . number_format($detail->harga_satuan, 2));
            $this->line('    Subtotal: Rp ' . number_format($detail->subtotal, 2));
            $this->line('');
        }
        
        return 0;
    }
}
