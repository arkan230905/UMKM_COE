<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\AssetDepreciation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetDepreciationService
{
    /**
     * Generate depreciation schedule per year and post journals based on method.
     */
    public function computeAndPost(Aset $asset): void
    {
        if (empty($asset->umur_manfaat) || $asset->umur_manfaat <= 0) {
            throw new \InvalidArgumentException('Umur manfaat harus lebih dari 0.');
        }

        DB::transaction(function () use ($asset) {
            // Clear old schedule for this asset
            AssetDepreciation::where('asset_id', $asset->id)->delete();

            $method = $asset->metode_penyusutan ?? 'garis_lurus';
            
            switch ($method) {
                case 'saldo_menurun':
                    $this->computeDoubleDecliningBalance($asset);
                    break;
                case 'sum_of_years_digits':
                    $this->computeSumOfYearsDigits($asset);
                    break;
                case 'garis_lurus':
                default:
                    $this->computeStraightLine($asset);
                    break;
            }

            // Lock asset from deletion after depreciation generated
            $asset->locked = true;
            $asset->save();
        });
    }

    /**
     * Calculate straight-line depreciation
     */
    private function computeStraightLine(Aset $asset): void
    {
        $cost = (float)$asset->harga_perolehan + (float)$asset->biaya_perolehan;
        $residual = (float)$asset->nilai_residu;
        $life = (int)$asset->umur_manfaat;
        $base = max($cost - $residual, 0);
        $perYear = $life > 0 ? round($base / $life, 2) : 0.0;

        $startDate = Carbon::parse($asset->tanggal_beli);
        $startYear = $startDate->year;
        $acc = 0.0; $book = $cost;

        // Hitung sisa bulan di tahun pertama dari tanggal beli
        $bulanBeli = $startDate->month;
        $monthsInFirstYear = 12 - $bulanBeli + 1; // Termasuk bulan beli

        for ($i = 0; $i < $life; $i++) {
            $year = $startYear + $i;
            $depr = 0;

            if ($i == 0) {
                // Tahun pertama: gunakan metode proporsional berdasarkan bulan perolehan
                $depr = $asset->hitungPenyusutanTahunPertama();
            } else {
                // Tahun-tengah: penyusutan penuh
                $depr = min($perYear, max($book - $residual, 0));
            }
            
            $acc += $depr;
            $book -= $depr;

            $this->createDepreciationRecord($asset, $year, $depr, $acc, $book);
        }
    }

    /**
     * Calculate double declining balance depreciation
     * Logic sesuai Excel: rate × book value awal tahun, partial year calculation
     */
    private function computeDoubleDecliningBalance(Aset $asset): void
    {
        $cost = (float)$asset->harga_perolehan + (float)$asset->biaya_perolehan;
        $residual = (float)$asset->nilai_residu;
        $life = (int)$asset->umur_manfaat;
        
        if ($life <= 0) return;

        // Gunakan tarif yang diinput user (jika ada), default ke rumus (100%/life)×2
        $tarifPersen = $asset->tarif_penyusutan ?? ((100 / $life) * 2);
        $rate = $tarifPersen / 100;
        
        $startDate = Carbon::parse($asset->tanggal_beli);
        $startYear = $startDate->year;
        $acc = 0.0;
        $book = $cost;

        // Hitung sisa bulan di tahun pertama dari tanggal beli
        $bulanBeli = $startDate->month;
        $monthsInFirstYear = 12 - $bulanBeli + 1; // Termasuk bulan beli

        for ($i = 0; $i < $life; $i++) {
            $year = $startYear + $i;
            $depr = 0;

            if ($i == 0) {
                // Tahun pertama: gunakan logika yang sama dengan create.blade.php
                $tahunanPenuh = $rate * $book;
                $tanggalPerolehan = $asset->tanggal_akuisisi ?? $asset->tanggal_beli;
                
                // Gunakan bulan_mulai jika ada, otherwise gunakan tanggal
                if ($asset->bulan_mulai && $asset->bulan_mulai >= 1 && $asset->bulan_mulai <= 12) {
                    $bulanPerolehan = $asset->bulan_mulai;
                } elseif ($tanggalPerolehan) {
                    $tanggal = Carbon::parse($tanggalPerolehan);
                    $bulanPerolehan = $tanggal->month;
                } else {
                    $bulanPerolehan = 1; // Default Januari
                }
                
                $sisaBulan = 13 - $bulanPerolehan;
                
                if ($sisaBulan < 12) {
                    // Partial year: (Tarif% * sisa bulan) / 12 * nilai buku awal
                    $depr = ($rate * $sisaBulan) / 12 * $book;
                } else {
                    // Full year: Tarif% * nilai buku awal
                    $depr = $rate * $book;
                }
            } else if (($i + 1) == $life) {
                // Tahun terakhir: ambil sisa nilai buku agar nilai buku akhir = 0
                $depr = $book; // Langsung ambil semua nilai buku
            } else {
                // Tahun-tengah: nilai buku akhir tahun sebelumnya × tarif (penuh 1 tahun)
                // Namun, jika nilai buku setelah penyusutan ini menjadi terlalu kecil,
                // maka sisa nilai buku akan disusutkan di tahun ini.
                $depr = $book * $rate;
                
                // Periksa apakah ini adalah tahun-tahun terakhir dan nilai buku sudah rendah
                // Jika sisa umur manfaat tinggal 2 tahun atau kurang, dan penyusutan standar
                // akan meninggalkan nilai buku yang sangat kecil, maka habiskan saja.
                $remainingLife = $life - ($i + 1);
                if ($remainingLife <= 1 && ($book - $depr) < ($book * 0.1)) { // Jika sisa nilai buku kurang dari 10% dari nilai buku awal tahun
                    $depr = $book; // Habiskan sisa nilai buku
                }
            }
            
            // Pastikan penyusutan tidak negatif
            if ($depr < 0) {
                $depr = 0;
            }

            $acc += $depr;
            $book -= $depr;

            $this->createDepreciationRecord($asset, $year, $depr, $acc, $book);

            // Jika nilai buku sudah mencapai atau di bawah residu, hentikan
            if ($book <= $residual) {
                break;
            }
        }
    }

    /**
     * Calculate yearly depreciation - sesuai logika Excel
     * Gunakan DDB rate setiap tahun, tidak switch ke straight-line terlalu awal
     */
    private function calculateYearlyDepreciation(float $bookValue, float $rate, float $residual, int $remainingLife): float
    {
        // Double declining balance depreciation (sesuai Excel)
        $ddbDepreciation = $bookValue * $rate;
        
        // Pastikan tidak melebihi nilai yang bisa disusutkan
        $maxDepreciable = $bookValue - $residual;
        
        // Gunakan DDB, kecuali di tahun terakhir dimana kita habiskan sisa
        if ($remainingLife == 1) {
            // Tahun terakhir: habiskan semua sisa book value
            return $maxDepreciable;
        }
        
        return min($ddbDepreciation, $maxDepreciable);
    }

    /**
     * Create depreciation record and journal entries
     */
    private function createDepreciationRecord(Aset $asset, int $year, float $depr, float $acc, float $book): void
    {
        AssetDepreciation::create([
            'asset_id' => $asset->id,
            'tahun' => $year,
            'beban_penyusutan' => $depr,
            'akumulasi_penyusutan' => $acc,
            'nilai_buku_akhir' => $book,
        ]);
    }

    /**
     * Calculate Sum of the Years' Digits depreciation
     * Logika: Setiap tahun bisa memiliki 1 atau 2 baris terpisah
     */
    private function computeSumOfYearsDigits(Aset $asset): void
    {
        $cost = (float)$asset->harga_perolehan + (float)$asset->biaya_perolehan;
        $residual = (float)$asset->nilai_residu;
        $life = (int)$asset->umur_manfaat;
        $depreciableAmount = max($cost - $residual, 0);
        
        if ($life <= 0 || $depreciableAmount <= 0) return;

        // Hitung total jumlah angka tahun (5+4+3+2+1 = 15 untuk umur 5 tahun)
        $totalYearsDigits = $life * ($life + 1) / 2;
        
        // Ambil tanggal perolehan
        $tanggalPerolehan = $asset->tanggal_perolehan ?? $asset->tanggal_akuisisi ?? $asset->tanggal_beli;
        if (!$tanggalPerolehan) {
            $tanggalPerolehan = $asset->tanggal_beli;
        }
        
        $startDate = Carbon::parse($tanggalPerolehan);
        $startYear = $startDate->year;
        $startMonth = $startDate->month;
        
        // Hitung bulan tersisa di tahun pertama
        $remainingMonthsFirstYear = 12 - $startMonth + 1;
        $carryOverMonths = 12 - $remainingMonthsFirstYear; // Bulan yang dibawa ke tahun berikutnya
        
        $acc = 0.0;
        $book = $cost;
        
        for ($i = 0; $i < $life; $i++) {
            $year = $startYear + $i;
            $currentYearDigit = $life - $i; // Angka tahun untuk tahun ini (5,4,3,2,1)
            
            if ($i == 0) {
                // Tahun pertama: 1 baris saja
                $depr = $depreciableAmount * ($remainingMonthsFirstYear / 12) * ($currentYearDigit / $totalYearsDigits);
                $depr = min($depr, max($book - $residual, 0));
                
                $acc += $depr;
                $book -= $depr;
                
                $this->createDepreciationRecord($asset, $year, $depr, $acc, $book);
                
                if ($book <= $residual) break;
            } else {
                // Tahun-tahun berikutnya: 2 baris
                
                // Baris 1: carry over months dengan angka tahun sebelumnya
                $prevYearDigit = $currentYearDigit + 1;
                $depr1 = $depreciableAmount * ($carryOverMonths / 12) * ($prevYearDigit / $totalYearsDigits);
                $depr1 = min($depr1, max($book - $residual, 0));
                
                $acc += $depr1;
                $book -= $depr1;
                
                $this->createDepreciationRecord($asset, $year, $depr1, $acc, $book);
                
                if ($book <= $residual) break;
                
                // Baris 2: remaining months dengan angka tahun saat ini
                $depr2 = $depreciableAmount * ($remainingMonthsFirstYear / 12) * ($currentYearDigit / $totalYearsDigits);
                $depr2 = min($depr2, max($book - $residual, 0));
                
                $acc += $depr2;
                $book -= $depr2;
                
                $this->createDepreciationRecord($asset, $year, $depr2, $acc, $book);
                
                if ($book <= $residual) break;
            }
        }
        
        // Tambahkan sisa carry over months di tahun terakhir jika masih ada sisa
        if ($book > $residual && $carryOverMonths > 0) {
            $finalYear = $startYear + $life;
            $finalDepr = $depreciableAmount * ($carryOverMonths / 12) * (1 / $totalYearsDigits);
            $finalDepr = min($finalDepr, max($book - $residual, 0));
            
            $acc += $finalDepr;
            $book -= $finalDepr;
            
            $this->createDepreciationRecord($asset, $finalYear, $finalDepr, $acc, $book);
        }
    }
}
