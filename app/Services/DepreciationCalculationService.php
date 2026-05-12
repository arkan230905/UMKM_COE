<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\DepreciationSchedule;
use Carbon\Carbon;

class DepreciationCalculationService
{
    /**
     * Hitung penyusutan menggunakan metode garis lurus
     * Beban penyusutan = (Harga Perolehan - Nilai Residu) / Umur Ekonomis
     */
    public function hitungGarisLurus(Aset $aset, int $bulan): float
    {
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $nilaiTerdepresiasi = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
        $totalBulan = $aset->umur_manfaat * 12;
        
        return $nilaiTerdepresiasi / $totalBulan;
    }

    /**
     * Hitung penyusutan menggunakan metode saldo menurun ganda (Double Declining Balance)
     *
     * RUMUS BENAR:
     * - Tarif tahunan = 2 / umur_manfaat (misal 5 tahun → 40%)
     * - Beban tahunan = nilai_buku_awal_tahun × tarif_tahunan
     * - Beban bulanan = beban_tahunan / jumlah_bulan_dalam_tahun_itu
     *   (tahun pertama parsial: beban_tahunan × bulan_tersisa / 12, lalu bagi bulan_tersisa)
     *
     * BUKAN: nilai_buku × (tarif/12) per bulan — itu menghasilkan angka berbeda tiap bulan
     */
    public function hitungSaldoMenurun(Aset $aset, float $nilaiBukuAwal): float
    {
        // Kembalikan beban TAHUNAN penuh (bukan bulanan)
        // Pemanggil yang bertanggung jawab membagi dengan jumlah bulan
        $tarifTahunan = 2 / $aset->umur_manfaat;
        return $nilaiBukuAwal * $tarifTahunan;
    }

    /**
     * Hitung penyusutan menggunakan metode sum of years digits
     * Beban penyusutan = (Umur Manfaat - Tahun ke-n + 1) / Sum of Years × Total Disusutkan
     */
    public function hitungSumOfYearsDigits(Aset $aset, int $tahunKe): float
    {
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $totalDisusutkan = $totalPerolehan - (float)($aset->nilai_residu ?? 0);
        $sumOfYears = ($aset->umur_manfaat * ($aset->umur_manfaat + 1)) / 2;
        $sisaUmur = $aset->umur_manfaat - $tahunKe + 1;

        $yearlyDepreciation = ($sisaUmur / $sumOfYears) * $totalDisusutkan;
        return $yearlyDepreciation / 12; // Return monthly depreciation for the year
    }

    /**
     * Generate jadwal penyusutan per bulan untuk semua metode.
     *
     * LOGIKA SALDO MENURUN GANDA (DDB):
     * 1. Tentukan tanggal mulai (aturan: tgl > 15 → mulai bulan berikutnya).
     * 2. Hitung beban TAHUNAN: depr_year = book_value_awal_tahun × (2/umur).
     * 3. Tahun pertama parsial: depr_year_1 = depr_year_full × (sisa_bulan/12).
     * 4. Beban bulanan = depr_year_n / jumlah_bulan_dalam_tahun_n (konsisten).
     * 5. Bulan terakhir: adjust agar book_value = nilai_residu.
     */
    public function generateMonthlySchedule(Aset $aset): array
    {
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu    = (float)($aset->nilai_residu ?? 0);
        $umurManfaat    = (int)($aset->umur_manfaat ?? 0);

        if ($umurManfaat <= 0 || $totalPerolehan <= 0) return [];

        $tanggalMulai = $aset->tanggal_akuisisi ?? $aset->tanggal_beli;
        if (!$tanggalMulai) return [];

        $startDate = Carbon::parse($tanggalMulai)->startOfDay();

        // Aturan: tgl > 15 → mulai bulan berikutnya
        if ($startDate->day > 15) {
            $startDate = $startDate->addMonthNoOverflow()->startOfMonth();
        }

        $schedule = [];

        // Baris awal: Harga Perolehan
        $schedule[] = [
            'tahun_bulan'          => 'HP',
            'penyusutan'           => 0,
            'akumulasi_penyusutan' => 0,
            'nilai_buku'           => $totalPerolehan,
            'rincian'              => null,
        ];

        $nilaiBuku          = $totalPerolehan;
        $akumulasiPenyusutan = 0;
        $totalBulan         = $umurManfaat * 12;
        $currentDate        = $startDate->copy();

        // Hitung bulan tersisa di tahun kalender pertama (inklusif bulan mulai)
        $bulanTersisaTahunPertama = 13 - $startDate->month; // Sep=9 → 13-9=4

        // ── Bangun daftar segmen tahunan ──────────────────────────────────────
        // Setiap segmen: [jumlah_bulan, book_value_awal_tahun]
        // Ini memudahkan kita menghitung depr_month = depr_year / jumlah_bulan
        $segmen = [];
        $bvAwal = $totalPerolehan;
        $bulanTerpakai = 0;

        for ($t = 1; $t <= $umurManfaat + 1; $t++) {
            if ($bulanTerpakai >= $totalBulan) break;

            if ($t === 1) {
                $jmlBulan = min($bulanTersisaTahunPertama, $totalBulan);
            } else {
                $jmlBulan = min(12, $totalBulan - $bulanTerpakai);
            }
            if ($jmlBulan <= 0) break;

            // Beban tahunan penuh untuk nilai buku saat ini
            $deprYearFull = $this->hitungDeprTahunan($aset, $bvAwal, $nilaiResidu);

            // Tahun pertama: pro-rata
            if ($t === 1) {
                $deprYear = $deprYearFull * ($bulanTersisaTahunPertama / 12);
            } else {
                $deprYear = $deprYearFull;
            }

            // Pastikan tidak melebihi sisa yang bisa disusutkan
            $maxDepr = $bvAwal - $nilaiResidu;
            $deprYear = min($deprYear, $maxDepr);

            $deprMonth = $jmlBulan > 0 ? $deprYear / $jmlBulan : 0;

            $segmen[] = [
                'jumlah_bulan' => $jmlBulan,
                'depr_month'   => $deprMonth,
                'bv_awal'      => $bvAwal,
            ];

            $bvAwal -= $deprYear;
            $bulanTerpakai += $jmlBulan;

            if ($bvAwal <= $nilaiResidu) break;
        }

        // ── Bangun jadwal bulanan dari segmen ────────────────────────────────
        $bulanGlobal = 0;
        foreach ($segmen as $seg) {
            for ($b = 0; $b < $seg['jumlah_bulan']; $b++) {
                $bulanGlobal++;
                $isBulanTerakhir = ($bulanGlobal === $totalBulan);

                $deprBulanIni = $seg['depr_month'];

                // Bulan terakhir: adjust agar book_value tepat = nilai_residu
                if ($isBulanTerakhir || ($nilaiBuku - $deprBulanIni) < $nilaiResidu) {
                    $deprBulanIni = max(0, $nilaiBuku - $nilaiResidu);
                }

                $akumulasiPenyusutan += $deprBulanIni;
                $nilaiBuku           -= $deprBulanIni;

                $schedule[] = [
                    'tahun_bulan'          => $currentDate->format('M Y'),
                    'penyusutan'           => round($deprBulanIni, 2),
                    'akumulasi_penyusutan' => round($akumulasiPenyusutan, 2),
                    'nilai_buku'           => round($nilaiBuku, 2),
                    'rincian'              => null,
                ];

                $currentDate->addMonthNoOverflow();

                if ($nilaiBuku <= $nilaiResidu) goto done;
            }
        }
        done:

        return $schedule;
    }

    /**
     * Hitung beban penyusutan TAHUNAN PENUH berdasarkan metode.
     * Untuk DDB: depr = book_value × (2/umur).
     * Untuk garis lurus: depr = (cost - residu) / umur.
     * Untuk SYD: depr = (sisa_umur / sum_of_years) × (cost - residu).
     */
    private function hitungDeprTahunan(Aset $aset, float $nilaiBuku, float $nilaiResidu): float
    {
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $umurManfaat    = (int)$aset->umur_manfaat;

        switch ($aset->metode_penyusutan) {
            case 'saldo_menurun':
                // DDB: tarif = 2/umur, beban = book_value × tarif
                return $nilaiBuku * (2 / $umurManfaat);

            case 'garis_lurus':
                return ($totalPerolehan - $nilaiResidu) / $umurManfaat;

            case 'sum_of_years_digits':
                // Hitung tahun ke berapa berdasarkan book value
                $totalDisusutkan = $totalPerolehan - $nilaiResidu;
                $sudahDisusutkan = $totalPerolehan - $nilaiBuku;
                $sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2;
                // Estimasi tahun ke-n dari akumulasi
                $tahunKe = 1;
                $akum = 0;
                for ($t = 1; $t <= $umurManfaat; $t++) {
                    $sisaUmur = $umurManfaat - $t + 1;
                    $depr = ($totalDisusutkan * $sisaUmur) / $sumOfYears;
                    $akum += $depr;
                    if ($akum >= $sudahDisusutkan) { $tahunKe = $t; break; }
                }
                $sisaUmur = $umurManfaat - $tahunKe + 1;
                return ($totalDisusutkan * $sisaUmur) / $sumOfYears;

            default:
                return ($totalPerolehan - $nilaiResidu) / $umurManfaat;
        }
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

    /**
     * Calculate monthly depreciation for current month
     */
    public function calculateCurrentMonthDepreciation(Aset $aset): float
    {
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);
        $umurManfaat = (int)($aset->umur_manfaat ?? 0);
        
        if ($umurManfaat <= 0 || $totalPerolehan <= 0) {
            return 0;
        }
        
        // Check if asset depreciation has started
        $tanggalMulai = $aset->tanggal_akuisisi ?? $aset->tanggal_beli;
        if (!$tanggalMulai) {
            return 0;
        }
        
        $startDate = Carbon::parse($tanggalMulai);
        
        // Rule: if date > 15, start next month
        if ($startDate->day > 15) {
            $startDate->addMonth()->day(1);
        }
        
        // Check if current month is within depreciation period
        $currentDate = now();
        $endDate = $startDate->copy()->addMonths($umurManfaat * 12);
        
        if ($currentDate->lt($startDate) || $currentDate->gt($endDate)) {
            return 0; // Outside depreciation period
        }
        
        // Calculate which month/year of depreciation we're in
        $monthsElapsed = $startDate->diffInMonths($currentDate) + 1;
        $yearOfDepreciation = ceil($monthsElapsed / 12);
        
        // Calculate based on method
        switch ($aset->metode_penyusutan) {
            case 'garis_lurus':
                return ($totalPerolehan - $nilaiResidu) / ($umurManfaat * 12);
                
            case 'saldo_menurun':
                // Double Declining Balance - based on current book value
                $rateTahunan = 2 / $umurManfaat;
                $rateBulanan = $rateTahunan / 12;
                
                // Get current book value
                $akumulasiSebelumnya = $aset->hitungAkumulasiPenyusutanSaatIni();
                $nilaiBukuSaatIni = $totalPerolehan - $akumulasiSebelumnya;
                
                $penyusutanBulanIni = $nilaiBukuSaatIni * $rateBulanan;
                
                // Don't exceed residual value
                if ($nilaiBukuSaatIni - $penyusutanBulanIni < $nilaiResidu) {
                    $penyusutanBulanIni = $nilaiBukuSaatIni - $nilaiResidu;
                }
                
                return max(0, $penyusutanBulanIni);
                
            case 'sum_of_years_digits':
                if ($yearOfDepreciation > $umurManfaat) {
                    return 0; // Fully depreciated
                }
                
                $sumOfYears = ($umurManfaat * ($umurManfaat + 1)) / 2;
                $sisaUmur = $umurManfaat - $yearOfDepreciation + 1;
                $penyusutanTahunIni = (($totalPerolehan - $nilaiResidu) * $sisaUmur) / $sumOfYears;
                
                return $penyusutanTahunIni / 12;
                
            default:
                return ($totalPerolehan - $nilaiResidu) / ($umurManfaat * 12);
        }
    }
}
