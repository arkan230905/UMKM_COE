<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BahanBaku;
use App\Models\BahanPendukung;
use App\Models\StockMovement;
use App\Models\JournalLine;
use App\Models\Account;

class TestReturnFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-return-flow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test complete return flow: stock reduction and cash increase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Complete Return Flow...');
        
        // 1. Check stock movements for returns
        $this->info('');
        $this->info('1. Stock Movements for Returns:');
        $stockMovements = StockMovement::where('ref_type', 'return')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($stockMovements as $movement) {
            $this->line('  ' . $movement->tanggal . ' - ' . $movement->item_type . 
                ' ID:' . $movement->item_id . 
                ' Qty:' . $movement->qty . 
                ' Direction:' . $movement->direction .
                ' Ref:' . $movement->ref_id);
        }
        
        // 2. Check journal lines for returns
        $this->info('');
        $this->info('2. Journal Lines for Returns:');
        $journalLines = JournalLine::whereHas('entry', function($query) {
                $query->where('ref_type', 'purchase_return');
            })
            ->with('account')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        foreach ($journalLines as $line) {
            $accountName = $line->account ? $line->account->name : 'Unknown';
            $this->line('  ' . $line->account->code . ' - ' . $accountName . 
                ' | Debit: ' . number_format($line->debit, 2) . 
                ' | Credit: ' . number_format($line->credit, 2));
        }
        
        // 3. Check current stock levels
        $this->info('');
        $this->info('3. Current Stock Levels:');
        
        $materials = BahanBaku::take(3)->get();
        foreach ($materials as $material) {
            $this->line('  ' . $material->nama_bahan . ': ' . $material->stok . ' ' . ($material->satuanRelation->kode ?? 'pcs'));
        }
        
        $supports = BahanPendukung::take(3)->get();
        foreach ($supports as $support) {
            $this->line('  ' . $support->nama_bahan . ': ' . $support->stok . ' ' . ($support->satuanRelation->kode ?? 'pcs'));
        }
        
        // 4. Check cash account balance
        $this->info('');
        $this->info('4. Cash Account Status:');
        $kasAccount = Account::where('code', '101')->first();
        if ($kasAccount) {
            $this->line('  Cash Account: ' . $kasAccount->name . ' (Code: ' . $kasAccount->code . ')');
        }
        
        return 0;
    }
}
