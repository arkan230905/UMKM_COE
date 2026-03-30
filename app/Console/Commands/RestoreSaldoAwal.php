<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;

class RestoreSaldoAwal extends Command
{
    protected $signature = 'restore:saldo-awal';
    protected $description = 'Restore saldo_awal for COAs';

    public function handle()
    {
        $this->info('Restoring saldo_awal for COAs...');
        
        // Restore saldo awal based on the original data
        $saldoAwalData = [
            '1101' => 75000000,    // Kas
            '1102' => 100000000,   // Kas di Bank
            '3100' => 175000000,   // Modal
            '122111' => 50000,     // Persediaan Air
            '122112' => 642202,    // Persediaan Minyak Goreng
            '122113' => 12000000,  // Persediaan Gas
            '122114' => 750000,    // Persediaan Ketumbar
            '122115' => 6000000,   // Persediaan Cabe Merah
            '122116' => 1400000,   // Persediaan Bawang Putih
            '122117' => 450000,    // Persediaan Tepung Maizena
            '122118' => 100000,    // Persediaan Merica Bubuk
            '122119' => 150000,    // Persediaan Listrik
            '122120' => 1250000,   // Persediaan Bawang Merah
            '122121' => 100000,    // Persediaan Kemasan
            '122123' => 1600000,   // Persediaan Ayam Potong
            '122124' => 2250000,   // Persediaan Ayam Kampung
            '122125' => 2500000,   // Persediaan Bebek
            '122126' => 750000,    // Persediaan Lada Hitam
        ];
        
        foreach ($saldoAwalData as $kodeAkun => $saldoAwal) {
            $coa = Coa::where('kode_akun', $kodeAkun)->first();
            if ($coa) {
                $coa->update(['saldo_awal' => $saldoAwal]);
                $this->info("  {$kodeAkun} {$coa->nama_akun}: Rp " . number_format($saldoAwal, 0, ',', '.'));
            } else {
                $this->warn("  COA {$kodeAkun} not found");
            }
        }
        
        // Now delete the opening balance journal entry since we're using saldo_awal again
        $openingEntry = \App\Models\JournalEntry::where('ref_type', 'opening_balance')->first();
        if ($openingEntry) {
            $this->info("Deleting opening balance journal entry");
            \App\Models\JournalLine::where('journal_entry_id', $openingEntry->id)->delete();
            $openingEntry->delete();
        }
        
        $this->info('Saldo awal restored and opening balance journal removed');
        
        return 0;
    }
}