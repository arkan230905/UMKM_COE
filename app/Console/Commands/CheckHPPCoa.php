<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class CheckHPPCoa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:hpp-coa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check available COA for Harga Pokok Penjualan';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=== CHECKING COA FOR HARGA POKOK PENJUALAN ===");
        
        // Check for existing HPP COA
        $hppCoas = Coa::where('nama_akun', 'like', '%hpp%')
            ->orWhere('nama_akun', 'like', '%harga pokok%')
            ->orWhere('nama_akun', 'like', '%pokok%')
            ->get();
            
        $this->info("Found {$hppCoas->count()} COA related to HPP:");
        foreach ($hppCoas as $coa) {
            $this->info("  Kode: {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})");
        }
        
        // Check current BBB COA
        $bbbCoa = Coa::where('kode_akun', '51')->first();
        if ($bbbCoa) {
            $this->info("\nCurrent BBB COA (51):");
            $this->info("  Kode: {$bbbCoa->kode_akun} - {$bbbCoa->nama_akun} ({$bbbCoa->tipe_akun})");
        }
        
        // Check for available COA codes that could be used for HPP
        $this->info("\nAvailable COA codes in 5x range (Expense):");
        $expenseCoas = Coa::where('kode_akun', 'like', '5%')
            ->orderBy('kode_akun')
            ->get();
            
        foreach ($expenseCoas as $coa) {
            $this->info("  Kode: {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun})");
        }
        
        // Find next available COA code
        $this->info("\nFinding next available COA code:");
        $lastCoa = Coa::where('kode_akun', 'like', '5%')
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) DESC')
            ->first();
            
        if ($lastCoa) {
            $nextCode = (int)$lastCoa->kode_akun + 1;
            $this->info("  Next available code: {$nextCode}");
        } else {
            $this->info("  No expense COA found, could start with 51");
        }
        
        $this->info("\n=== CHECK COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
