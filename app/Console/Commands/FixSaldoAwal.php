<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class FixSaldoAwal extends Command
{
    protected $signature = 'fix:saldo-awal';
    protected $description = 'Reset saldo_awal to 0 since we now use journal entries for opening balance';

    public function handle()
    {
        $this->info('Resetting saldo_awal to 0 for all COAs...');
        
        $coas = Coa::where('saldo_awal', '!=', 0)->get();
        
        $this->info("Found {$coas->count()} COAs with non-zero saldo_awal");
        
        foreach ($coas as $coa) {
            $this->info("  {$coa->kode_akun} {$coa->nama_akun}: Rp " . number_format($coa->saldo_awal, 0, ',', '.') . " -> 0");
            $coa->update(['saldo_awal' => 0]);
        }
        
        $this->info('All saldo_awal reset to 0. Opening balances are now handled by journal entries only.');
        
        return 0;
    }
}