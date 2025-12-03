<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CoaPeriod;
use App\Models\Coa;
use App\Models\CoaPeriodBalance;
use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

class PostCoaPeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coa:post-period {periode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post saldo akhir periode COA ke periode berikutnya';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periodeInput = $this->argument('periode');
        
        if ($periodeInput) {
            // Post periode spesifik
            $period = CoaPeriod::where('periode', $periodeInput)->first();
            
            if (!$period) {
                $this->error("Periode {$periodeInput} tidak ditemukan!");
                return 1;
            }
        } else {
            // Post periode bulan lalu
            $lastMonth = now()->subMonth()->format('Y-m');
            $period = CoaPeriod::where('periode', $lastMonth)->first();
            
            if (!$period) {
                $this->error("Periode {$lastMonth} tidak ditemukan!");
                return 1;
            }
        }
        
        if ($period->is_closed) {
            $this->warn("Periode {$period->periode} sudah ditutup sebelumnya.");
            return 0;
        }
        
        $this->info("Memproses posting periode {$period->periode}...");
        
        try {
            DB::beginTransaction();
            
            $coas = Coa::where('is_akun_header', false)->get();
            $bar = $this->output->createProgressBar($coas->count());
            $bar->start();
            
            foreach ($coas as $coa) {
                // Hitung saldo akhir
                $saldoAkhir = $this->calculateEndingBalance($coa, $period);
                
                // Update saldo periode
                CoaPeriodBalance::updateOrCreate(
                    [
                        'kode_akun' => $coa->kode_akun,
                        'period_id' => $period->id,
                    ],
                    [
                        'saldo_awal' => $this->getOpeningBalance($coa, $period),
                        'saldo_akhir' => $saldoAkhir,
                        'is_posted' => true,
                    ]
                );
                
                // Post ke periode berikutnya
                $nextPeriod = $this->getOrCreateNextPeriod($period);
                if ($nextPeriod) {
                    CoaPeriodBalance::updateOrCreate(
                        [
                            'kode_akun' => $coa->kode_akun,
                            'period_id' => $nextPeriod->id,
                        ],
                        [
                            'saldo_awal' => $saldoAkhir,
                            'saldo_akhir' => 0,
                            'is_posted' => false,
                        ]
                    );
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            
            // Tutup periode
            $period->update([
                'is_closed' => true,
                'closed_at' => now(),
                'closed_by' => 1, // System user
            ]);
            
            DB::commit();
            
            $this->info("✓ Periode {$period->periode} berhasil diposting!");
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ Gagal posting periode: " . $e->getMessage());
            return 1;
        }
    }
    
    private function getOpeningBalance($coa, $period)
    {
        $previousPeriod = $period->getPreviousPeriod();
        
        if ($previousPeriod) {
            $previousBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                ->where('period_id', $previousPeriod->id)
                ->first();
            
            if ($previousBalance) {
                return $previousBalance->saldo_akhir;
            }
        }
        
        return $coa->saldo_awal ?? 0;
    }
    
    private function calculateEndingBalance($coa, $period)
    {
        $saldoAwal = $this->getOpeningBalance($coa, $period);
        
        $debit = JurnalUmum::where('coa_id', $coa->id)
            ->whereBetween('tanggal', [$period->tanggal_mulai, $period->tanggal_selesai])
            ->sum('debit');
        
        $kredit = JurnalUmum::where('coa_id', $coa->id)
            ->whereBetween('tanggal', [$period->tanggal_mulai, $period->tanggal_selesai])
            ->sum('kredit');
        
        if ($coa->saldo_normal === 'debit') {
            return $saldoAwal + $debit - $kredit;
        } else {
            return $saldoAwal + $kredit - $debit;
        }
    }
    
    private function getOrCreateNextPeriod($period)
    {
        $nextMonth = \Carbon\Carbon::parse($period->tanggal_mulai)->addMonth();
        
        return CoaPeriod::firstOrCreate(
            ['periode' => $nextMonth->format('Y-m')],
            [
                'tanggal_mulai' => $nextMonth->startOfMonth()->toDateString(),
                'tanggal_selesai' => $nextMonth->endOfMonth()->toDateString(),
            ]
        );
    }
}
