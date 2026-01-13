<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockMovement;

class CheckReturnStockMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-return-stock-movements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check stock movements for return transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Stock Movements for Returns:');
        
        $movements = StockMovement::where('ref_type', 'return')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        foreach ($movements as $movement) {
            $this->line($movement->tanggal . ' - ' . $movement->item_type . 
                ' ID:' . $movement->item_id . 
                ' Qty:' . $movement->qty . 
                ' Ref:' . $movement->ref_id);
        }
        
        return 0;
    }
}
