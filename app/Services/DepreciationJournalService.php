<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\DepreciationSchedule;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Coa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepreciationJournalService
{
    /**
     * Generate jurnal untuk depreciation schedule
     * Debit: Beban Penyusutan
     * Kredit: Akumulasi Penyusutan
     */
    public function generateJournal(DepreciationSchedule $schedule): Jurnal
    {
        $aset = $schedule->aset;
        
        // Cari COA untuk Beban Penyusutan (expense account)
        $bebanPenyusutanCoa = Coa::where('nama_coa', 'like', '%Beban Penyusutan%')
            ->orWhere('kode_coa', 'like', '%6%') // Biasanya di range 6xxx
            ->first();

        // Cari COA untuk Akumulasi Penyusutan (contra asset account)
        $akumulasiCoa = Coa::where('nama_coa', 'like', '%Akumulasi Penyusutan%')
            ->orWhere('kode_coa', 'like', '%31%') // Biasanya di range 31xx
            ->first();

        if (!$bebanPenyusutanCoa || !$akumulasiCoa) {
            throw new \Exception('COA untuk Beban Penyusutan atau Akumulasi Penyusutan tidak ditemukan');
        }

        // Buat jurnal header
        $jurnal = Jurnal::create([
            'nomor_jurnal' => $this->generateNomorJurnal(),
            'tanggal_jurnal' => $schedule->periode_akhir,
            'deskripsi' => "Penyusutan Aset: {$aset->nama_aset} ({$aset->kode_aset})",
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        // Buat jurnal detail - Debit Beban Penyusutan
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $bebanPenyusutanCoa->id,
            'debit' => $schedule->beban_penyusutan,
            'kredit' => 0,
            'keterangan' => "Penyusutan {$aset->nama_aset}",
        ]);

        // Buat jurnal detail - Kredit Akumulasi Penyusutan
        JurnalDetail::create([
            'jurnal_id' => $jurnal->id,
            'coa_id' => $akumulasiCoa->id,
            'debit' => 0,
            'kredit' => $schedule->beban_penyusutan,
            'keterangan' => "Akumulasi Penyusutan {$aset->nama_aset}",
        ]);

        return $jurnal;
    }

    /**
     * Post depreciation schedule dan jurnal
     */
    public function postSchedule(DepreciationSchedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            // Generate jurnal jika belum ada
            if (!$schedule->jurnal_id) {
                $jurnal = $this->generateJournal($schedule);
                $schedule->update(['jurnal_id' => $jurnal->id]);
            }

            // Post jurnal
            $jurnal = $schedule->jurnal;
            $jurnal->update([
                'status' => 'posted',
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            // Update depreciation schedule status
            $schedule->update([
                'status' => 'posted',
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            // Update aset
            $aset = $schedule->aset;
            $aset->update([
                'akumulasi_penyusutan' => $aset->akumulasi_penyusutan + $schedule->beban_penyusutan,
                'nilai_buku' => $aset->harga_perolehan - ($aset->akumulasi_penyusutan + $schedule->beban_penyusutan),
            ]);
        });
    }

    /**
     * Reverse (unpost) depreciation schedule dan jurnal
     */
    public function reverseSchedule(DepreciationSchedule $schedule, string $alasan = ''): void
    {
        DB::transaction(function () use ($schedule, $alasan) {
            if ($schedule->status !== 'posted') {
                throw new \Exception('Hanya schedule yang sudah di-post yang bisa di-reverse');
            }

            // Reverse jurnal
            $jurnal = $schedule->jurnal;
            $jurnalReverse = Jurnal::create([
                'nomor_jurnal' => $this->generateNomorJurnal(),
                'tanggal_jurnal' => now()->toDateString(),
                'deskripsi' => "Reverse: {$jurnal->deskripsi}",
                'status' => 'posted',
                'created_by' => Auth::id(),
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            // Copy detail dengan debit/kredit terbalik
            foreach ($jurnal->details as $detail) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnalReverse->id,
                    'coa_id' => $detail->coa_id,
                    'debit' => $detail->kredit,
                    'kredit' => $detail->debit,
                    'keterangan' => "Reverse: {$detail->keterangan}",
                ]);
            }

            // Update schedule
            $schedule->update([
                'status' => 'reversed',
                'reversed_by' => Auth::id(),
                'reversed_at' => now(),
                'keterangan' => $alasan,
            ]);

            // Update aset
            $aset = $schedule->aset;
            $aset->update([
                'akumulasi_penyusutan' => $aset->akumulasi_penyusutan - $schedule->beban_penyusutan,
                'nilai_buku' => $aset->harga_perolehan - ($aset->akumulasi_penyusutan - $schedule->beban_penyusutan),
            ]);
        });
    }

    /**
     * Generate nomor jurnal otomatis
     */
    private function generateNomorJurnal(): string
    {
        $prefix = 'JUR-' . date('Ym') . '-';
        $lastJurnal = Jurnal::where('nomor_jurnal', 'like', $prefix . '%')
            ->orderBy('nomor_jurnal', 'desc')
            ->first();

        $number = $lastJurnal ? (int) str_replace($prefix, '', $lastJurnal->nomor_jurnal) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
