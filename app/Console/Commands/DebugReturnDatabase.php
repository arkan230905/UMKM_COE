<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugReturnDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-return-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug return database records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Debugging Return Database Records...');
        
        // Check retur table
        $this->info('');
        $this->info('Retur Table:');
        $returs = DB::table('returs')->get();
        
        foreach ($returs as $retur) {
            $this->line('  ID: ' . $retur->id . ' - Status: ' . $retur->status . ' - Total: ' . $retur->jumlah . ' - Tanggal: ' . $retur->tanggal);
        }
        
        // Check retur_details table
        $this->info('');
        $this->info('Retur Details Table:');
        $details = DB::table('retur_details')->get();
        
        foreach ($details as $detail) {
            $this->line('  ID: ' . $detail->id . ' - Retur ID: ' . $detail->retur_id);
            
            // Check what columns exist
            $columns = array_keys((array)$detail);
            $this->line('    Available columns: ' . implode(', ', $columns));
            
            if (property_exists($detail, 'bahan_baku_id')) {
                $this->line('    Bahan Baku ID: ' . $detail->bahan_baku_id);
            }
            if (property_exists($detail, 'bahan_pendukung_id')) {
                $this->line('    Bahan Pendukung ID: ' . $detail->bahan_pendukung_id);
            }
            if (property_exists($detail, 'qty')) {
                $this->line('    Qty: ' . $detail->qty);
            }
            if (property_exists($detail, 'harga_satuan')) {
                $this->line('    Harga Satuan: ' . $detail->harga_satuan);
            }
            if (property_exists($detail, 'subtotal')) {
                $this->line('    Subtotal: ' . $detail->subtotal);
            }
            if (property_exists($detail, 'jumlah')) {
                $this->line('    Jumlah: ' . $detail->jumlah);
            }
            $this->line('');
        }
        
        // Check stock movements for returns
        $this->info('');
        $this->info('Stock Movements for Returns:');
        $movements = DB::table('stock_movements')
            ->where('ref_type', 'return')
            ->get();
            
        foreach ($movements as $movement) {
            $this->line('  Item Type: ' . $movement->item_type . ' - Item ID: ' . $movement->item_id);
            $this->line('    Qty: ' . $movement->qty . ' - Direction: ' . $movement->direction);
            $this->line('    Ref ID: ' . $movement->ref_id . ' - Tanggal: ' . $movement->tanggal);
            $this->line('');
        }
        
        return 0;
    }
}
