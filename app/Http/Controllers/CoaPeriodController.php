<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\CoaPeriod;
use App\Models\CoaPeriodBalance;
use App\Models\JurnalUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CoaPeriodController extends Controller
{
    /**
     * Hitung saldo akhir periode dan posting ke periode berikutnya
     */
    public function postPeriod(Request $request, $periodId)
    {
        try {
            DB::beginTransaction();

            $period = CoaPeriod::findOrFail($periodId);
            
            // Cek apakah periode sudah ditutup
            if ($period->is_closed) {
                return back()->with('error', 'Periode ini sudah ditutup sebelumnya.');
            }

            // Get semua akun COA
            $coas = Coa::where('is_akun_header', false)->get();
            
            foreach ($coas as $coa) {
                // Hitung saldo akhir berdasarkan jurnal
                $saldoAkhir = $this->calculateEndingBalance($coa, $period);
                
                // Simpan atau update saldo periode
                $balance = CoaPeriodBalance::updateOrCreate(
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

                // Posting ke periode berikutnya
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
            }

            // Tandai periode sebagai ditutup
            $period->update([
                'is_closed' => true,
                'closed_at' => now(),
                'closed_by' => auth()->id(),
            ]);

            DB::commit();

            return back()->with('success', 'Saldo periode berhasil diposting ke periode berikutnya.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal posting periode: ' . $e->getMessage());
        }
    }

    /**
     * Get saldo awal untuk periode tertentu
     */
    private function getOpeningBalance($coa, $period)
    {
        // Cek apakah ada saldo dari periode sebelumnya
        $previousPeriod = $period->getPreviousPeriod();
        
        if ($previousPeriod) {
            $previousBalance = CoaPeriodBalance::where('kode_akun', $coa->kode_akun)
                ->where('period_id', $previousPeriod->id)
                ->first();
            
            if ($previousBalance) {
                return $previousBalance->saldo_akhir;
            }
        }
        
        // Jika tidak ada periode sebelumnya, gunakan saldo awal dari COA
        return $coa->saldo_awal ?? 0;
    }

    /**
     * Hitung saldo akhir berdasarkan jurnal
     */
    private function calculateEndingBalance($coa, $period)
    {
        $saldoAwal = $this->getOpeningBalance($coa, $period);
        
        // Hitung total debit dan kredit dari jurnal umum menggunakan coa_id
        $debit = JurnalUmum::where('coa_id', $coa->id)
            ->whereBetween('tanggal', [$period->tanggal_mulai, $period->tanggal_selesai])
            ->sum('debit');
        
        $kredit = JurnalUmum::where('coa_id', $coa->id)
            ->whereBetween('tanggal', [$period->tanggal_mulai, $period->tanggal_selesai])
            ->sum('kredit');
        
        // Hitung saldo akhir berdasarkan saldo normal
        if ($coa->saldo_normal === 'debit') {
            return $saldoAwal + $debit - $kredit;
        } else {
            return $saldoAwal + $kredit - $debit;
        }
    }

    /**
     * Get atau create periode berikutnya
     */
    private function getOrCreateNextPeriod($period)
    {
        $nextMonth = Carbon::parse($period->tanggal_mulai)->addMonth();
        
        return CoaPeriod::firstOrCreate(
            ['periode' => $nextMonth->format('Y-m')],
            [
                'tanggal_mulai' => $nextMonth->startOfMonth()->toDateString(),
                'tanggal_selesai' => $nextMonth->endOfMonth()->toDateString(),
            ]
        );
    }

    /**
     * Buka kembali periode yang sudah ditutup
     */
    public function reopenPeriod($periodId)
    {
        try {
            DB::beginTransaction();

            $period = CoaPeriod::findOrFail($periodId);
            
            // Cek apakah ada periode setelahnya yang sudah ditutup
            $nextPeriod = $period->getNextPeriod();
            if ($nextPeriod && $nextPeriod->is_closed) {
                return back()->with('error', 'Tidak dapat membuka periode karena periode berikutnya sudah ditutup.');
            }

            // Buka periode
            $period->update([
                'is_closed' => false,
                'closed_at' => null,
                'closed_by' => null,
            ]);

            // Update status posting saldo
            CoaPeriodBalance::where('period_id', $period->id)
                ->update(['is_posted' => false]);

            DB::commit();

            return back()->with('success', 'Periode berhasil dibuka kembali.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuka periode: ' . $e->getMessage());
        }
    }
}
