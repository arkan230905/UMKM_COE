<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProsesProduksi;
use App\Services\JournalService;

class FixBTKLBOPJournals extends Command
{
    protected $signature = 'fix:btkl-bop-journals';
    protected $description = 'Fix BTKL & BOP journal entries - change BOP from debit to credit';

    public function handle()
    {
        $this->info('Memperbaiki jurnal BTKL & BOP...');
        
        $journal = app(JournalService::class);
        
        // Get all production records
        $produksis = ProsesProduksi::all();
        
        $count = 0;
        foreach ($produksis as $produksi) {
            // Delete existing labor overhead journals
            $journal->deleteByRef('production_labor_overhead', (int)$produksi->id);
            
            // Recreate with correct debit/credit positions
            $this->createLaborOverheadJournals($produksi);
            
            $count++;
            $this->line("✓ Fixed produksi ID: {$produksi->id}");
        }
        
        $this->info("Selesai! Total {$count} jurnal BTKL & BOP telah diperbaiki.");
    }
    
    private function createLaborOverheadJournals($produksi)
    {
        $journal = app(JournalService::class);
        $tanggal = $produksi->tanggal;
        
        // Journal for Labor and Overhead (BTKL & BOP → WIP)
        $laborOverheadEntries = [];
        $totalLaborOverhead = $produksi->total_btkl + $produksi->total_bop;
        
        if ($totalLaborOverhead > 0) {
            $coaWIP = \App\Models\Coa::where('kode_akun', '117')->first(); // Barang Dalam Proses
            $coaBTKL = \App\Models\Coa::where('kode_akun', '52')->first(); // Biaya Tenaga Kerja Langsung
            $coaBOP = \App\Models\Coa::where('kode_akun', '53')->first(); // Biaya Overhead Pabrik
            
            if ($coaWIP) {
                $laborOverheadEntries[] = [
                    'code' => $coaWIP->kode_akun,
                    'debit' => $totalLaborOverhead,
                    'credit' => 0,
                    'memo' => 'Transfer BTKL & BOP ke WIP'
                ];
            }
            
            if ($coaBTKL && $produksi->total_btkl > 0) {
                $laborOverheadEntries[] = [
                    'code' => $coaBTKL->kode_akun,
                    'debit' => 0,
                    'credit' => $produksi->total_btkl,
                    'memo' => 'Alokasi BTKL ke produksi'
                ];
            }
            
            if ($coaBOP && $produksi->total_bop > 0) {
                $laborOverheadEntries[] = [
                    'code' => $coaBOP->kode_akun,
                    'debit' => 0,
                    'credit' => $produksi->total_bop,
                    'memo' => 'Alokasi BOP ke produksi'
                ];
            }
            
            if (!empty($laborOverheadEntries)) {
                $journal->post($tanggal, 'production_labor_overhead', (int)$produksi->id, 'Alokasi BTKL & BOP ke Produksi', $laborOverheadEntries);
            }
        }
    }
}
