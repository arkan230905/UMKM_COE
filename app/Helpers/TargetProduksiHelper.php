<?php

namespace App\Helpers;

use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;
use Illuminate\Support\Facades\Cache;

/**
 * Target Produksi Helper
 * 
 * Helper class untuk memudahkan integrasi modul lain dengan Master Target Produksi
 * 
 * @package App\Helpers
 */
class TargetProduksiHelper
{
    /**
     * Get target produksi untuk produk dan bulan tertentu
     * 
     * @param int $produkId
     * @param int $bulan (1-12)
     * @param int|null $tahun (default: current year)
     * @return int Target produksi atau 0 jika tidak ada
     */
    public static function getTargetBulan(int $produkId, int $bulan, ?int $tahun = null): int
    {
        $tahun = $tahun ?? now()->year;

        $detail = TargetProduksiDetail::whereHas('targetProduksi', function ($query) use ($produkId, $tahun) {
            $query->where('user_id', auth()->id())
                ->where('produk_id', $produkId)
                ->where('tahun', $tahun);
        })
            ->where('bulan', $bulan)
            ->first();

        return $detail ? $detail->target_bulanan : 0;
    }

    /**
     * Get target produksi tahunan untuk produk
     * 
     * @param int $produkId
     * @param int|null $tahun (default: current year)
     * @return int Target tahunan atau 0 jika tidak ada
     */
    public static function getTargetTahunan(int $produkId, ?int $tahun = null): int
    {
        $tahun = $tahun ?? now()->year;

        $target = TargetProduksi::where('user_id', auth()->id())
            ->where('produk_id', $produkId)
            ->where('tahun', $tahun)
            ->first();

        return $target ? $target->total_target_tahunan : 0;
    }

    /**
     * Get all targets untuk bulan tertentu (semua produk)
     * 
     * @param int $bulan (1-12)
     * @param int|null $tahun (default: current year)
     * @return \Illuminate\Support\Collection
     */
    public static function getAllTargetsBulan(int $bulan, ?int $tahun = null)
    {
        $tahun = $tahun ?? now()->year;

        return TargetProduksiDetail::whereHas('targetProduksi', function ($query) use ($tahun) {
            $query->where('user_id', auth()->id())
                ->where('tahun', $tahun);
        })
            ->where('bulan', $bulan)
            ->with(['targetProduksi.produk'])
            ->get()
            ->map(function ($detail) {
                return [
                    'produk_id' => $detail->targetProduksi->produk_id,
                    'produk_nama' => $detail->targetProduksi->produk->nama_produk,
                    'target' => $detail->target_bulanan,
                ];
            });
    }

    /**
     * Check apakah bulan dapat diedit
     * 
     * @param int $bulan (1-12)
     * @param int|null $tahun (default: current year)
     * @return bool
     */
    public static function isMonthEditable(int $bulan, ?int $tahun = null): bool
    {
        $tahun = $tahun ?? now()->year;
        return !TargetProduksiDetail::checkLockStatus($tahun, $bulan);
    }

    /**
     * Get nama bulan dalam bahasa Indonesia
     * 
     * @param int $bulan (1-12)
     * @return string
     */
    public static function getNamaBulan(int $bulan): string
    {
        return TargetProduksiDetail::getMonthName($bulan);
    }

    /**
     * Calculate kebutuhan tenaga kerja berdasarkan target
     * 
     * @param int $produkId
     * @param int $bulan
     * @param int $outputPerOrang Output standar per orang per bulan
     * @param int|null $tahun
     * @return int Jumlah tenaga kerja yang dibutuhkan
     */
    public static function calculateKebutuhanTenagaKerja(
        int $produkId, 
        int $bulan, 
        int $outputPerOrang,
        ?int $tahun = null
    ): int {
        $target = self::getTargetBulan($produkId, $bulan, $tahun);
        
        if ($target <= 0 || $outputPerOrang <= 0) {
            return 0;
        }

        return (int) ceil($target / $outputPerOrang);
    }

    /**
     * Calculate estimasi BTKL berdasarkan target
     * 
     * @param int $produkId
     * @param int $bulan
     * @param float $jamKerjaPerUnit Jam kerja per unit produk
     * @param float $tarifPerJam Tarif upah per jam
     * @param int|null $tahun
     * @return float Estimasi BTKL
     */
    public static function calculateEstimasiBTKL(
        int $produkId,
        int $bulan,
        float $jamKerjaPerUnit,
        float $tarifPerJam,
        ?int $tahun = null
    ): float {
        $target = self::getTargetBulan($produkId, $bulan, $tahun);
        return $target * $jamKerjaPerUnit * $tarifPerJam;
    }

    /**
     * Calculate tarif BOP per unit berdasarkan target
     * 
     * @param float $totalBopBulanan Total BOP untuk bulan tersebut
     * @param int $produkId
     * @param int $bulan
     * @param int|null $tahun
     * @return float Tarif BOP per unit atau 0 jika target = 0
     */
    public static function calculateTarifBOP(
        float $totalBopBulanan,
        int $produkId,
        int $bulan,
        ?int $tahun = null
    ): float {
        $target = self::getTargetBulan($produkId, $bulan, $tahun);
        
        if ($target <= 0) {
            return 0;
        }

        return $totalBopBulanan / $target;
    }

    /**
     * Get persentase pencapaian untuk produk dan bulan tertentu
     * 
     * @param int $produkId
     * @param int $bulan
     * @param int $realisasi Realisasi produksi
     * @param int|null $tahun
     * @return float Persentase (0-100+)
     */
    public static function getPersentasePencapaian(
        int $produkId,
        int $bulan,
        int $realisasi,
        ?int $tahun = null
    ): float {
        $target = self::getTargetBulan($produkId, $bulan, $tahun);
        
        if ($target <= 0) {
            return 0;
        }

        return round(($realisasi / $target) * 100, 2);
    }

    /**
     * Get status pencapaian (badge color)
     * 
     * @param float $persentase
     * @return array ['status' => string, 'color' => string, 'label' => string]
     */
    public static function getStatusPencapaian(float $persentase): array
    {
        if ($persentase >= 100) {
            return [
                'status' => 'achieved',
                'color' => 'success',
                'label' => 'Tercapai',
            ];
        } elseif ($persentase >= 80) {
            return [
                'status' => 'good',
                'color' => 'info',
                'label' => 'Baik',
            ];
        } elseif ($persentase >= 60) {
            return [
                'status' => 'warning',
                'color' => 'warning',
                'label' => 'Perlu Perhatian',
            ];
        } else {
            return [
                'status' => 'critical',
                'color' => 'danger',
                'label' => 'Kritis',
            ];
        }
    }

    /**
     * Check apakah produk memiliki target untuk tahun tertentu
     * 
     * @param int $produkId
     * @param int|null $tahun
     * @return bool
     */
    public static function hasTarget(int $produkId, ?int $tahun = null): bool
    {
        $tahun = $tahun ?? now()->year;

        return TargetProduksi::where('user_id', auth()->id())
            ->where('produk_id', $produkId)
            ->where('tahun', $tahun)
            ->exists();
    }

    /**
     * Get list produk yang memiliki target untuk tahun tertentu
     * 
     * @param int|null $tahun
     * @return \Illuminate\Support\Collection
     */
    public static function getProdukWithTarget(?int $tahun = null)
    {
        $tahun = $tahun ?? now()->year;

        return TargetProduksi::where('user_id', auth()->id())
            ->where('tahun', $tahun)
            ->with('produk')
            ->get()
            ->pluck('produk');
    }

    /**
     * Get summary target vs realisasi untuk periode tertentu
     * 
     * @param int $produkId
     * @param int $bulanAwal
     * @param int $bulanAkhir
     * @param int|null $tahun
     * @return array
     */
    public static function getSummaryPeriode(
        int $produkId,
        int $bulanAwal,
        int $bulanAkhir,
        ?int $tahun = null
    ): array {
        $tahun = $tahun ?? now()->year;
        $totalTarget = 0;
        $totalRealisasi = 0;
        $details = [];

        for ($bulan = $bulanAwal; $bulan <= $bulanAkhir; $bulan++) {
            $target = self::getTargetBulan($produkId, $bulan, $tahun);
            
            // Get realisasi from Produksi model (implement sesuai model Anda)
            $realisasi = \App\Models\Produksi::where('user_id', auth()->id())
                ->where('produk_id', $produkId)
                ->whereYear('tanggal_produksi', $tahun)
                ->whereMonth('tanggal_produksi', $bulan)
                ->sum('jumlah_produksi') ?? 0;

            $totalTarget += $target;
            $totalRealisasi += $realisasi;

            $details[] = [
                'bulan' => $bulan,
                'nama_bulan' => self::getNamaBulan($bulan),
                'target' => $target,
                'realisasi' => $realisasi,
                'selisih' => $realisasi - $target,
                'persentase' => $target > 0 ? round(($realisasi / $target) * 100, 2) : 0,
            ];
        }

        return [
            'periode' => self::getNamaBulan($bulanAwal) . ' - ' . self::getNamaBulan($bulanAkhir) . ' ' . $tahun,
            'total_target' => $totalTarget,
            'total_realisasi' => $totalRealisasi,
            'selisih' => $totalRealisasi - $totalTarget,
            'persentase' => $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0,
            'details' => $details,
        ];
    }

    /**
     * Clear cache (jika menggunakan caching)
     * 
     * @param int|null $produkId
     * @param int|null $tahun
     * @return void
     */
    public static function clearCache(?int $produkId = null, ?int $tahun = null): void
    {
        if ($produkId && $tahun) {
            Cache::forget("target_produksi_{$produkId}_{$tahun}");
        } else {
            Cache::forget('target_produksi_*');
        }
    }

    /**
     * Format number untuk display
     * 
     * @param int|float $number
     * @param int $decimals
     * @return string
     */
    public static function formatNumber($number, int $decimals = 0): string
    {
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Get icon berdasarkan status
     * 
     * @param string $status (achieved, good, warning, critical)
     * @return string Heroicon name
     */
    public static function getStatusIcon(string $status): string
    {
        return match ($status) {
            'achieved' => 'heroicon-o-check-circle',
            'good' => 'heroicon-o-arrow-trending-up',
            'warning' => 'heroicon-o-exclamation-triangle',
            'critical' => 'heroicon-o-x-circle',
            default => 'heroicon-o-question-mark-circle',
        };
    }
}
