<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CoaPeriod;
use App\Models\Coa;
use App\Models\CoaPeriodBalance;
use Carbon\Carbon;

class CreateCoaPeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coa:create-period {periode?} {--months=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat periode baru untuk COA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodeInput = $this->argument('periode');
        $months = $this->option('months');
        
        if ($periodeInput) {
            // Buat periode spesifik
            $this->createPeriod($periodeInput);
        } else {
            // Buat periode untuk beberapa bulan ke depan
            $startDate = now()->startOfMonth();
            
            for ($i = 0; $i < $months; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $periode = $date->format('Y-m');
                $this->createPeriod($periode);
            }
        }
        
        $this->info("✓ Selesai!");
        return 0;
    }
    
    private function createPeriod($periode)
    {
        try {
            $date = Carbon::parse($periode . '-01');
            
            $period = CoaPeriod::firstOrCreate(
                ['periode' => $periode],
                [
                    'tanggal_mulai' => $date->copy()->startOfMonth()->toDateString(),
                    'tanggal_selesai' => $date->copy()->endOfMonth()->toDateString(),
                    'is_closed' => false,
                ]
            );
            
            if ($period->wasRecentlyCreated) {
                $this->info("Periode {$periode} berhasil dibuat.");
                
                // Inisialisasi saldo
                $this->initializeBalances($period);
            } else {
                $this->warn("Periode {$periode} sudah ada.");
            }
            
        } catch (\Exception $e) {
            $this->error("Gagal membuat periode {$periode}: " . $e->getMessage());
        }
    }
    
    private function initializeBalances($period)
    {
        $coas = Coa::where('is_akun_header', false)->get();
        
        // Cek periode sebelumnya
        $previousPeriod = $period->getPreviousPeriod();
        
        foreach ($coas as $coa) {
            $saldoAwal = 0;
            
            if ($previousPeriod) {
                // Ambil saldo akhir periode sebelumnya
                $previousBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                    ->where('period_id', $previousPeriod->id)
                    ->first();
                
                if ($previousBalance) {
                    $saldoAwal = $previousBalance->saldo_akhir;
                }
            } else {
                // Gunakan saldo awal dari COA
                $saldoAwal = $coa->saldo_awal ?? 0;
            }
            
            CoaPeriodBalance::firstOrCreate(
                [
                    'kode_akun' => $coa->kode_akun,
                    'period_id' => $period->id,
                ],
                [
                    'saldo_awal' => $saldoAwal,
                    'saldo_akhir' => 0,
                    'is_posted' => false,
                ]
            );
        }
        
        $this->info("  → Saldo periode {$period->periode} diinisialisasi untuk {$coas->count()} akun.");
    }
}
