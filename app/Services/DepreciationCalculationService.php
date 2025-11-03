<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\DepreciationSchedule;
use Carbon\Carbon;

class DepreciationCalculationService
{
    /**
     * Hitung penyusutan menggunakan metode garis lurus
     * Beban penyusutan = (Harga Perolehan - Nilai Sisa) / Umur Ekonomis
     */
    public function hitungGarisLurus(Aset $aset, int $bulan): float
    {
        $nilaiTerdepresiasi = $aset->harga_perolehan - $aset->nilai_sisa;
        $totalBulan = $aset->umur_ekonomis_tahun * 12;
        
        return $nilaiTerdepresiasi / $totalBulan;
    }

    /**
     * Hitung penyusutan menggunakan metode saldo menurun
     * Beban penyusutan = Nilai Buku Awal × Persentase Penyusutan
     */
    public function hitungSaldoMenurun(Aset $aset, float $nilaiBukuAwal): float
    {
        if (!$aset->persentase_penyusutan) {
            // Hitung persentase otomatis jika tidak ada
            $persentase = (1 - pow($aset->nilai_sisa / $aset->harga_perolehan, 1 / $aset->umur_ekonomis_tahun)) * 100;
            $aset->update(['persentase_penyusutan' => $persentase]);
        }

        return $nilaiBukuAwal * ($aset->persentase_penyusutan / 100);
    }

    /**
     * Hitung penyusutan menggunakan metode sum of years digits
     * Beban penyusutan = (Sisa Umur / Total Digit Tahun) × (Harga Perolehan - Nilai Sisa)
     */
    public function hitungSumOfYearsDigits(Aset $aset, int $tahunKe): float
    {
        $nilaiTerdepresiasi = $aset->harga_perolehan - $aset->nilai_sisa;
        $totalDigit = ($aset->umur_ekonomis_tahun * ($aset->umur_ekonomis_tahun + 1)) / 2;
        $sisaUmur = $aset->umur_ekonomis_tahun - $tahunKe + 1;

        return ($sisaUmur / $totalDigit) * $nilaiTerdepresiasi;
    }

    /**
     * Generate depreciation schedule untuk periode tertentu
     */
    public function generateSchedule(
        Aset $aset,
        Carbon $tanggalMulai,
        Carbon $tanggalAkhir,
        string $periodisitas = 'bulanan'
    ): array {
        $schedules = [];
        $nilaiAwal = $aset->harga_perolehan;
        $akumulasiPenyusutan = 0;
        $bulanKe = 0;
        $tahunKe = 0;

        if ($periodisitas === 'bulanan') {
            $current = $tanggalMulai->copy();
            
            while ($current <= $tanggalAkhir) {
                $bulanKe++;
                $tahunKe = ceil($bulanKe / 12);
                $periodeAkhir = $current->copy()->endOfMonth();

                // Hitung beban penyusutan sesuai metode
                $bebanPenyusutan = match ($aset->metode_penyusutan) {
                    'garis_lurus' => $this->hitungGarisLurus($aset, $bulanKe),
                    'saldo_menurun' => $this->hitungSaldoMenurun($aset, $nilaiAwal - $akumulasiPenyusutan),
                    'sum_of_years_digits' => $this->hitungSumOfYearsDigits($aset, $tahunKe),
                    default => $this->hitungGarisLurus($aset, $bulanKe),
                };

                $akumulasiPenyusutan += $bebanPenyusutan;
                $nilaiBuku = $nilaiAwal - $akumulasiPenyusutan;

                // Pastikan tidak melampaui nilai sisa
                if ($nilaiBuku < $aset->nilai_sisa) {
                    $bebanPenyusutan -= ($aset->nilai_sisa - $nilaiBuku);
                    $akumulasiPenyusutan = $nilaiAwal - $aset->nilai_sisa;
                    $nilaiBuku = $aset->nilai_sisa;
                }

                $schedules[] = [
                    'periode_mulai' => $current->format('Y-m-d'),
                    'periode_akhir' => $periodeAkhir->format('Y-m-d'),
                    'periode_bulan' => $bulanKe,
                    'nilai_awal' => $nilaiAwal - ($akumulasiPenyusutan - $bebanPenyusutan),
                    'beban_penyusutan' => round($bebanPenyusutan, 2),
                    'akumulasi_penyusutan' => round($akumulasiPenyusutan, 2),
                    'nilai_buku' => round($nilaiBuku, 2),
                ];

                $current->addMonth();
            }
        } else {
            // Periodisitas tahunan
            $current = $tanggalMulai->copy();
            $tahunKe = 0;

            while ($current <= $tanggalAkhir) {
                $tahunKe++;
                $periodeAkhir = $current->copy()->addYear()->subDay();

                $bebanPenyusutan = match ($aset->metode_penyusutan) {
                    'garis_lurus' => $this->hitungGarisLurus($aset, $tahunKe * 12),
                    'saldo_menurun' => $this->hitungSaldoMenurun($aset, $nilaiAwal - $akumulasiPenyusutan),
                    'sum_of_years_digits' => $this->hitungSumOfYearsDigits($aset, $tahunKe),
                    default => $this->hitungGarisLurus($aset, $tahunKe * 12),
                };

                $akumulasiPenyusutan += $bebanPenyusutan;
                $nilaiBuku = $nilaiAwal - $akumulasiPenyusutan;

                if ($nilaiBuku < $aset->nilai_sisa) {
                    $bebanPenyusutan -= ($aset->nilai_sisa - $nilaiBuku);
                    $akumulasiPenyusutan = $nilaiAwal - $aset->nilai_sisa;
                    $nilaiBuku = $aset->nilai_sisa;
                }

                $schedules[] = [
                    'periode_mulai' => $current->format('Y-m-d'),
                    'periode_akhir' => $periodeAkhir->format('Y-m-d'),
                    'periode_bulan' => $tahunKe * 12,
                    'nilai_awal' => $nilaiAwal - ($akumulasiPenyusutan - $bebanPenyusutan),
                    'beban_penyusutan' => round($bebanPenyusutan, 2),
                    'akumulasi_penyusutan' => round($akumulasiPenyusutan, 2),
                    'nilai_buku' => round($nilaiBuku, 2),
                ];

                $current->addYear();
            }
        }

        return $schedules;
    }

    /**
     * Simpan schedule ke database
     */
    public function saveSchedule(Aset $aset, array $schedules): void
    {
        foreach ($schedules as $schedule) {
            DepreciationSchedule::updateOrCreate(
                [
                    'aset_id' => $aset->id,
                    'periode_mulai' => $schedule['periode_mulai'],
                    'periode_akhir' => $schedule['periode_akhir'],
                ],
                [
                    'periode_bulan' => $schedule['periode_bulan'],
                    'nilai_awal' => $schedule['nilai_awal'],
                    'beban_penyusutan' => $schedule['beban_penyusutan'],
                    'akumulasi_penyusutan' => $schedule['akumulasi_penyusutan'],
                    'nilai_buku' => $schedule['nilai_buku'],
                    'status' => 'draft',
                ]
            );
        }
    }
}
