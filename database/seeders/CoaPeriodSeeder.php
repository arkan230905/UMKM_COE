<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoaPeriod;
use App\Models\Coa;
use App\Models\CoaPeriodBalance;
use Carbon\Carbon;

class CoaPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat periode untuk 6 bulan terakhir dan 6 bulan ke depan
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        
        for ($i = 0; $i < 12; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $periode = $date->format('Y-m');
            
            $period = CoaPeriod::firstOrCreate(
                ['periode' => $periode],
                [
                    'tanggal_mulai' => $date->copy()->startOfMonth()->toDateString(),
                    'tanggal_selesai' => $date->copy()->endOfMonth()->toDateString(),
                    'is_closed' => false,
                ]
            );
            
            $this->command->info("Periode {$periode} berhasil dibuat.");
        }
        
        // Inisialisasi saldo awal untuk semua periode dari saldo_awal COA
        $allPeriods = CoaPeriod::orderBy('periode', 'asc')->get();
        $coas = Coa::where('is_akun_header', false)->get();
        
        foreach ($allPeriods as $period) {
            foreach ($coas as $coa) {
                CoaPeriodBalance::firstOrCreate(
                    [
                        'kode_akun' => $coa->kode_akun,
                        'period_id' => $period->id,
                    ],
                    [
                        'saldo_awal' => $coa->saldo_awal ?? 0,
                        'saldo_akhir' => 0,
                        'is_posted' => false,
                    ]
                );
            }
            
            $this->command->info("Saldo periode {$period->periode} berhasil diinisialisasi.");
        }
    }
}
