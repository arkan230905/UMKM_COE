<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coa;
use App\Http\Controllers\AkuntansiController;

class DebugLabaRugiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:laba-rugi-data {--user=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug laba rugi data to identify HPP placement issue';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->option('user');
        $this->info("=== DEBUGGING LABA RUGI DATA FOR USER ID: {$userId} ===");
        
        // Simulate laba rugi controller logic
        $periode = now()->format('Y-m');
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        $from = \Carbon\Carbon::create($tahun, $bulan, 1)->format('Y-m-d');
        $to = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        
        $this->info("Periode: {$periode} ({$from} to {$to})");
        
        // Get COAs
        $coas = Coa::where('user_id', $userId)->get();
        $this->info("Total COAs: " . $coas->count());
        
        // Get account summary (simplified)
        $mutasiByKodeAkun = [];
        foreach ($coas as $coa) {
            $mutasiByKodeAkun[$coa->kode_akun] = [
                'total_debit' => 0,
                'total_kredit' => 0
            ];
        }
        
        $accountData = [];
        foreach ($coas as $coa) {
            $saldoAwal = (float)($coa->saldo_awal ?? 0);
            $totalDebit = $mutasiByKodeAkun[$coa->kode_akun]['total_debit'] ?? 0;
            $totalKredit = $mutasiByKodeAkun[$coa->kode_akun]['total_kredit'] ?? 0;
            
            $firstDigit = substr($coa->kode_akun, 0, 1);
            $isDebitNormal = !in_array($firstDigit, ['2', '3', '4']);
            
            if ($isDebitNormal) {
                $saldoAkhir = $saldoAwal + $totalDebit - $totalKredit;
            } else {
                $saldoAkhir = $saldoAwal - $totalDebit + $totalKredit;
            }
            
            $accountData[$coa->kode_akun] = [
                'coa' => $coa,
                'saldo_akhir' => $saldoAkhir
            ];
        }
        
        // Filter pendapatan and beban
        $pendapatan = $coas->filter(function($coa) use ($accountData) {
            if (!in_array($coa->tipe_akun, ['Revenue', 'revenue', 'Pendapatan'])) return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo != 0;
        })->sortBy('kode_akun');
        
        $beban = $coas->filter(function($coa) use ($accountData) {
            if (!in_array($coa->tipe_akun, ['Expense', 'expense', 'Beban', 'Biaya'])) return false;
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            return $saldo != 0;
        })->sortBy('kode_akun');
        
        $this->info("\n📊 PENDAPATAN DATA:");
        $this->info("Count: " . $pendapatan->count());
        foreach ($pendapatan as $coa) {
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            $this->info("  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun}): Rp " . number_format($saldo, 2));
        }
        
        $this->info("\n📊 BEBAN DATA:");
        $this->info("Count: " . $beban->count());
        foreach ($beban as $coa) {
            $saldo = $accountData[$coa->kode_akun]['saldo_akhir'] ?? 0;
            $this->info("  {$coa->kode_akun} - {$coa->nama_akun} ({$coa->tipe_akun}): Rp " . number_format($saldo, 2));
        }
        
        // Check specifically for HPP
        $this->info("\n🔍 HPP ANALYSIS:");
        $hppCoa = Coa::where('kode_akun', '560')->where('user_id', $userId)->first();
        if ($hppCoa) {
            $hppInPendapatan = $pendapatan->contains('kode_akun', '560');
            $hppInBeban = $beban->contains('kode_akun', '560');
            
            $this->info("HPP COA Found: {$hppCoa->nama_akun}");
            $this->info("HPP in Pendapatan: " . ($hppInPendapatan ? '❌ YES' : '✅ NO'));
            $this->info("HPP in Beban: " . ($hppInBeban ? '✅ YES' : '❌ NO'));
            
            if ($hppInPendapatan) {
                $this->error("❌ PROBLEM: HPP appears in pendapatan section!");
                $this->info("This should not happen with correct tipe_akun = 'Expense'");
            }
            
            if ($hppInBeban) {
                $this->info("✅ HPP correctly appears in beban section");
            }
        }
        
        $this->info("\n=== DEBUG COMPLETED ===");
        
        return Command::SUCCESS;
    }
}
