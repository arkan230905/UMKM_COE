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
     * Beban penyusutan = Nilai Buku Awal × (2 / Umur Manfaat) -> ini adalah tarif tahunan
     */
    public function hitungSaldoMenurun(Aset $aset, float $nilaiBukuAwal): float
    {
        $tarifTahunan = 2 / $aset->umur_manfaat; // Double declining rate (decimal per tahun)
        $tarifBulanan = $tarifTahunan / 12; // Monthly rate
        return $nilaiBukuAwal * $tarifBulanan; // Return monthly depreciation based on current book value
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
     * Generate jadwal penyusutan per bulan untuk semua metode
     * Sesuai dengan aturan:
     * - Jika tanggal > 15, mulai bulan berikutnya
     * - Jika tanggal ≤ 15, mulai bulan tersebut
     */
    public function generateMonthlySchedule(Aset $aset): array
    {
        $totalPerolehan = (float)($aset->harga_perolehan ?? 0) + (float)($aset->biaya_perolehan ?? 0);
        $nilaiResidu = (float)($aset->nilai_residu ?? 0);
        $umurManfaat = (int)($aset->umur_manfaat ?? 0);
        
        if ($umurManfaat <= 0 || $totalPerolehan <= 0) {
            return [];
        }

        // Tentukan tanggal mulai penyusutan
        $tanggalMulai = $aset->tanggal_akuisisi ?? $aset->tanggal_beli;
        if (!$tanggalMulai) {
            return [];
        }
        
        $startDate = Carbon::parse($tanggalMulai);
        
        // Aturan: jika tanggal > 15, mulai bulan berikutnya
        if ($startDate->day > 15) {
            $startDate->addMonth()->day(1);
        }
        
        $schedule = [];
        $currentDate = $startDate->copy();
        $akumulasiPenyusutan = 0;
        $nilaiBuku = $totalPerolehan;
        $bulanKe = 0;
        
        // Tambahkan baris awal (Harga Perolehan)
        $schedule[] = [
            'tahun_bulan' => 'HP',
            'penyusutan' => 0,
            'akumulasi_penyusutan' => 0,
            'nilai_buku' => $totalPerolehan,
            'rincian' => null
        ];
        
        // Hitung jadwal per bulan
        $tahunPenyusutanKe = 1;
        $bulanDalamTahunPenyusutan = 0;
        $totalBulan = $umurManfaat * 12;
        
        // Loop maksimal sesuai umur manfaat (60 bulan untuk 5 tahun)
        for ($i = 0; $i < $totalBulan; $i++) {
            $bulanKe++;
            $bulanDalamTahunPenyusutan++;
            
            // Reset counter tahun penyusutan jika sudah 12 bulan
            if ($bulanDalamTahunPenyusutan > 12) {
                $tahunPenyusutanKe++;
                $bulanDalamTahunPenyusutan = 1;
            }
            
            // Hitung penyusutan bulan ini sesuai metode
            $penyusutanBulanan = $this->hitungPenyusutanBulanan(
                $aset, 
                $tahunPenyusutanKe, 
                $bulanDalamTahunPenyusutan, 
                $nilaiBuku, 
                $bulanKe
            );
            
            // ATURAN KHUSUS UNTUK SALDO MENURUN GANDA
            if ($aset->metode_penyusutan === 'saldo_menurun') {
                // Hitung rate tahunan (40% untuk umur 5 tahun)
                $rateTahunan = 2 / $aset->umur_manfaat;
                
                // Hitung penyusutan normal bulan ini
                $penyusutanNormal = ($rateTahunan * $nilaiBuku) / 12;
                
                // Cek apakah ini bulan terakhir umur manfaat
                $isBulanTerakhir = ($i == $totalBulan - 1);
                
                // Cek apakah akan melewati nilai residu
                $akanLewatiResidu = ($nilaiBuku - $penyusutanNormal) <= $nilaiResidu;
                
                if ($akanLewatiResidu || $isBulanTerakhir) {
                    // Pakai selisih langsung, bukan rate
                    $penyusutanBulanan = $nilaiBuku - $nilaiResidu;
                    $nilaiBukuBaru = $nilaiResidu; // tepat balance
                    
                    // Tambahkan baris terakhir
                    $akumulasiPenyusutan += $penyusutanBulanan;
                    $schedule[] = [
                        'tahun_bulan' => $currentDate->format('M Y'),
                        'penyusutan' => $penyusutanBulanan,
                        'akumulasi_penyusutan' => $akumulasiPenyusutan,
                        'nilai_buku' => $nilaiBukuBaru,
                        'rincian' => null
                    ];
                    break; // STOP → tidak ada baris setelah ini
                } else {
                    // Gunakan penyusutan normal
                    $penyusutanBulanan = $penyusutanNormal;
                }
            } elseif ($aset->metode_penyusutan === 'sum_of_years_digits') {
                // ATURAN KHUSUS UNTUK SUM OF YEARS DIGITS
                // Cek apakah ini bulan terakhir umur manfaat
                $isBulanTerakhir = ($i == $totalBulan - 1);
                
                if ($isBulanTerakhir) {
                    // Bulan terakhir: pastikan akumulasi tepat = total_disusutkan
                    $targetAkumulasi = $totalPerolehan - $nilaiResidu;
                    $penyusutanBulanan = $targetAkumulasi - $akumulasiPenyusutan;
                    $nilaiBukuBaru = $nilaiResidu; // tepat balance
                    
                    // Tambahkan baris terakhir
                    $akumulasiPenyusutan += $penyusutanBulanan;
                    $schedule[] = [
                        'tahun_bulan' => $currentDate->format('M Y'),
                        'penyusutan' => $penyusutanBulanan,
                        'akumulasi_penyusutan' => $akumulasiPenyusutan,
                        'nilai_buku' => $nilaiBukuBaru,
                        'rincian' => null
                    ];
                    break; // STOP → tidak ada baris setelah ini
                }
            } else {
                // Untuk metode lain, gunakan logic yang sudah ada
                // Pastikan tidak melebihi batas
                $maksPenyusutan = $nilaiBuku - $nilaiResidu;
                if ($penyusutanBulanan > $maksPenyusutan) {
                    $penyusutanBulanan = $maksPenyusutan;
                }
                
                // Jika sudah mencapai nilai residu, hentikan perhitungan
                if ($nilaiBuku <= $nilaiResidu) {
                    $akumulasiPenyusutan = $totalPerolehan - $nilaiResidu;
                    $nilaiBuku = $nilaiResidu;
                    
                    // Tambahkan bulan terakhir
                    $schedule[] = [
                        'tahun_bulan' => $currentDate->format('M Y'),
                        'penyusutan' => $penyusutanBulanan,
                        'akumulasi_penyusutan' => $akumulasiPenyusutan,
                        'nilai_buku' => $nilaiBuku,
                        'rincian' => null
                    ];
                    break;
                }
            }
            
            $akumulasiPenyusutan += $penyusutanBulanan;
            $nilaiBuku -= $penyusutanBulanan;
            
            $schedule[] = [
                'tahun_bulan' => $currentDate->format('M Y'),
                'penyusutan' => $penyusutanBulanan,
                'akumulasi_penyusutan' => $akumulasiPenyusutan,
                'nilai_buku' => $nilaiBuku,
                'rincian' => null
            ];
            
            $currentDate->addMonth();
        }
        
        return $schedule;
    }
    
    /**
     * Hitung penyusutan bulanan berdasarkan metode
     */
    private function hitungPenyusutanBulanan(
        Aset $aset, 
        int $tahunPenyusutanKe, 
        int $bulanDalamTahunPenyusutan, 
        float $nilaiBuku, 
        int $bulanKe
    ): float {
        $metode = $aset->metode_penyusutan;
        
        switch ($metode) {
            case 'garis_lurus':
                return $this->hitungGarisLurus($aset, $bulanKe);
                
            case 'saldo_menurun':
                // Untuk saldo menurun, hitung tahunan lalu bagi 12
                $penyusutanTahunan = $this->hitungSaldoMenurun($aset, $nilaiBuku);
                return $penyusutanTahunan / 12;
                
            case 'sum_of_years_digits':
                // Hitung penyusutan tahunan untuk tahun penyusutan ke-n, lalu bagi 12
                $penyusutanTahunan = $this->hitungSumOfYearsDigits($aset, $tahunPenyusutanKe);
                return $penyusutanTahunan / 12;
                
            default:
                return $this->hitungGarisLurus($aset, $bulanKe);
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
