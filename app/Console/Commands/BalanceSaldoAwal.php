<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class BalanceSaldoAwal extends Command
{
    protected $signature = 'balance:saldo-awal';
    protected $description = 'Balance saldo awal by adjusting Laba Ditahan';

    public function handle()
    {
        $this->info('Balancing saldo awal...');
        
        // Calculate current saldo awal totals
        $allCoa = Coa::all();
        $totalAssetSaldoAwal = 0;
        $totalEquitySaldoAwal = 0;
        
        foreach ($allCoa as $coa) {
            $saldoAwal = $coa->saldo_awal ?? 0;
            if ($saldoAwal != 0) {
                if ($coa->tipe_akun === 'Asset') {
                    $totalAssetSaldoAwal += $saldoAwal;
                    $this->info("  Asset {$coa->kode_akun}: Rp " . number_format($saldoAwal, 0, ',', '.'));
                } elseif ($coa->tipe_akun === 'Equity') {
                    $totalEquitySaldoAwal += $saldoAwal;
                    $this->info("  Equity {$coa->kode_akun}: Rp " . number_format($saldoAwal, 0, ',', '.'));
                }
            }
        }
        
        $this->info("\nTotals:");
        $this->info("Total Asset Saldo Awal: Rp " . number_format($totalAssetSaldoAwal, 0, ',', '.'));
        $this->info("Total Equity Saldo Awal: Rp " . number_format($totalEquitySaldoAwal, 0, ',', '.'));
        
        $difference = $totalAssetSaldoAwal - $totalEquitySaldoAwal;
        $this->info("Difference: Rp " . number_format($difference, 0, ',', '.'));
        
        if ($difference != 0) {
            // Find or create Laba Ditahan COA
            $labaDitahan = Coa::where('kode_akun', '3102')->first();
            if (!$labaDitahan) {
                $this->error('Laba Ditahan COA (3102) not found');
                return 1;
            }
            
            // Adjust Laba Ditahan saldo awal to balance
            $currentLabaDitahan = $labaDitahan->saldo_awal ?? 0;
            $newLabaDitahan = $currentLabaDitahan + $difference;
            
            $labaDitahan->update(['saldo_awal' => $newLabaDitahan]);
            
            $this->info("\nAdjusted Laba Ditahan:");
            $this->info("  Before: Rp " . number_format($currentLabaDitahan, 0, ',', '.'));
            $this->info("  After: Rp " . number_format($newLabaDitahan, 0, ',', '.'));
            $this->info("  Change: Rp " . number_format($difference, 0, ',', '.'));
            
            // Verify balance
            $newTotalEquity = $totalEquitySaldoAwal + $difference;
            $this->info("\nVerification:");
            $this->info("Total Asset: Rp " . number_format($totalAssetSaldoAwal, 0, ',', '.'));
            $this->info("Total Equity (adjusted): Rp " . number_format($newTotalEquity, 0, ',', '.'));
            $this->info("New Difference: Rp " . number_format($totalAssetSaldoAwal - $newTotalEquity, 0, ',', '.'));
            
            if ($totalAssetSaldoAwal == $newTotalEquity) {
                $this->info("✅ Saldo awal is now balanced!");
            }
        } else {
            $this->info("✅ Saldo awal is already balanced");
        }
        
        return 0;
    }
}