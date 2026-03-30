<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use App\Services\JournalService;

class CreateOpeningBalance extends Command
{
    protected $signature = 'create:opening-balance';
    protected $description = 'Create opening balance journal to balance the neraca';

    public function handle()
    {
        $this->info('Creating opening balance journal...');
        
        $journal = app(JournalService::class);
        
        // Calculate total saldo awal for assets
        $allCoa = Coa::all();
        $totalAssetSaldoAwal = 0;
        $totalEquitySaldoAwal = 0;
        
        foreach ($allCoa as $coa) {
            $saldoAwal = $coa->saldo_awal ?? 0;
            if ($saldoAwal != 0) {
                if (in_array($coa->tipe_akun, ['Asset'])) {
                    $totalAssetSaldoAwal += $saldoAwal;
                } elseif (in_array($coa->tipe_akun, ['Equity'])) {
                    $totalEquitySaldoAwal += $saldoAwal;
                }
            }
        }
        
        $this->info("Total Asset Saldo Awal: Rp " . number_format($totalAssetSaldoAwal, 0, ',', '.'));
        $this->info("Total Equity Saldo Awal: Rp " . number_format($totalEquitySaldoAwal, 0, ',', '.'));
        
        $difference = $totalAssetSaldoAwal - $totalEquitySaldoAwal;
        $this->info("Difference: Rp " . number_format($difference, 0, ',', '.'));
        
        if ($difference != 0) {
            // Find Laba Ditahan COA
            $labaDitahan = Coa::where('kode_akun', '3102')->first();
            if (!$labaDitahan) {
                $this->error('Laba Ditahan COA (3102) not found');
                return 1;
            }
            
            // Create opening balance journal entry
            $lines = [];
            
            // Add all asset saldo awal as debit
            foreach ($allCoa as $coa) {
                $saldoAwal = $coa->saldo_awal ?? 0;
                if ($saldoAwal > 0 && $coa->tipe_akun === 'Asset') {
                    $lines[] = ['code' => $coa->kode_akun, 'debit' => $saldoAwal, 'credit' => 0];
                }
            }
            
            // Add all equity saldo awal as credit
            foreach ($allCoa as $coa) {
                $saldoAwal = $coa->saldo_awal ?? 0;
                if ($saldoAwal > 0 && $coa->tipe_akun === 'Equity') {
                    $lines[] = ['code' => $coa->kode_akun, 'debit' => 0, 'credit' => $saldoAwal];
                }
            }
            
            // Add balancing entry to Laba Ditahan
            if ($difference > 0) {
                // Assets > Equity, so we need to credit Laba Ditahan (retained earnings)
                $lines[] = ['code' => $labaDitahan->kode_akun, 'debit' => 0, 'credit' => $difference];
                $this->info("Adding Rp " . number_format($difference, 0, ',', '.') . " to Laba Ditahan as opening retained earnings");
            } else {
                // Equity > Assets, so we need to debit Laba Ditahan
                $lines[] = ['code' => $labaDitahan->kode_akun, 'debit' => abs($difference), 'credit' => 0];
                $this->info("Reducing Laba Ditahan by Rp " . number_format(abs($difference), 0, ',', '.'));
            }
            
            // Post the opening balance journal
            $journal->post('2026-01-01', 'opening_balance', 0, 'Saldo Awal Periode', $lines);
            
            $this->info("Opening balance journal created successfully");
        } else {
            $this->info("Saldo awal already balanced, no journal needed");
        }
        
        return 0;
    }
}