<?php

namespace App\Services;

use App\Models\TargetProduksi;
use App\Models\TargetProduksiDetail;
use App\Models\Produksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TargetProduksiService
{
    /**
     * Create target produksi dengan details
     */
    public function create(array $data): TargetProduksi
    {
        return DB::transaction(function () use ($data) {
            // Create header
            $target = TargetProduksi::create([
                'tahun' => $data['tahun'],
                'produk_id' => $data['produk_id'],
                'total_target_tahunan' => $data['total_target_tahunan'],
            ]);

            // Create details (12 bulan)
            if (isset($data['details']) && is_array($data['details'])) {
                foreach ($data['details'] as $detail) {
                    $target->details()->create([
                        'bulan' => $detail['bulan'],
                        'target_bulanan' => $detail['target_bulanan'],
                    ]);
                }
            }

            return $target->load('details', 'produk');
        });
    }

    /**
     * Update target produksi
     */
    public function update(TargetProduksi $target, array $data): TargetProduksi
    {
        return DB::transaction(function () use ($target, $data) {
            // Update header
            $target->update([
                'total_target_tahunan' => $data['total_target_tahunan'],
            ]);

            // Update details (hanya yang editable)
            if (isset($data['details']) && is_array($data['details'])) {
                foreach ($data['details'] as $detailData) {
                    $detail = $target->details()
                        ->where('bulan', $detailData['bulan'])
                        ->first();

                    if ($detail && !$detail->isLocked()) {
                        $detail->update([
                            'target_bulanan' => $detailData['target_bulanan'],
                        ]);
                    }
                }
            }

            return $target->load('details', 'produk');
        });
    }

    /**
     * Generate target otomatis
     */
    public function generateAutoTarget(int $totalTarget, string $method, ?int $previousYear = null): array
    {
        return match($method) {
            'merata' => $this->generateMerataTarget($totalTarget),
            'persentase' => $this->generatePersentaseTarget($totalTarget),
            'histori' => $this->generateHistoriTarget($totalTarget, $previousYear),
            default => $this->generateMerataTarget($totalTarget),
        };
    }

    /**
     * Generate target dibagi rata
     */
    private function generateMerataTarget(int $totalTarget): array
    {
        $perBulan = floor($totalTarget / 12);
        $sisa = $totalTarget - ($perBulan * 12);

        $targets = [];
        for ($i = 1; $i <= 12; $i++) {
            $targets[] = [
                'bulan' => $i,
                'target_bulanan' => $perBulan + ($i == 12 ? $sisa : 0), // Sisa masuk ke Desember
            ];
        }

        return $targets;
    }

    /**
     * Generate target berdasarkan persentase custom
     * Default: distribusi normal dengan puncak di tengah tahun
     */
    private function generatePersentaseTarget(int $totalTarget): array
    {
        // Distribusi persentase (total 100%)
        $percentages = [
            1 => 7,   // Januari - awal tahun biasanya rendah
            2 => 7,   // Februari
            3 => 8,   // Maret - mulai naik
            4 => 8,   // April
            5 => 9,   // Mei
            6 => 10,  // Juni - puncak
            7 => 10,  // Juli - puncak
            8 => 9,   // Agustus
            9 => 9,   // September
            10 => 8,  // Oktober
            11 => 8,  // November
            12 => 7,  // Desember - akhir tahun turun
        ];

        $targets = [];
        $totalAllocated = 0;

        for ($i = 1; $i <= 11; $i++) {
            $target = floor($totalTarget * $percentages[$i] / 100);
            $targets[] = [
                'bulan' => $i,
                'target_bulanan' => $target,
            ];
            $totalAllocated += $target;
        }

        // Bulan terakhir dapat sisa
        $targets[] = [
            'bulan' => 12,
            'target_bulanan' => $totalTarget - $totalAllocated,
        ];

        return $targets;
    }

    /**
     * Generate target berdasarkan histori tahun sebelumnya
     */
    private function generateHistoriTarget(int $totalTarget, ?int $previousYear): array
    {
        if (!$previousYear) {
            return $this->generateMerataTarget($totalTarget);
        }

        // Ambil data histori
        $histori = TargetProduksiDetail::whereHas('targetProduksi', function ($query) use ($previousYear) {
            $query->where('tahun', $previousYear)
                ->where('user_id', auth()->id());
        })->get();

        if ($histori->isEmpty()) {
            return $this->generateMerataTarget($totalTarget);
        }

        // Hitung proporsi dari tahun sebelumnya
        $totalHistori = $histori->sum('target_bulanan');
        if ($totalHistori == 0) {
            return $this->generateMerataTarget($totalTarget);
        }

        $targets = [];
        $totalAllocated = 0;

        foreach ($histori as $index => $item) {
            if ($index < 11) { // Bulan 1-11
                $proportion = $item->target_bulanan / $totalHistori;
                $target = floor($totalTarget * $proportion);
                $targets[] = [
                    'bulan' => $item->bulan,
                    'target_bulanan' => $target,
                ];
                $totalAllocated += $target;
            }
        }

        // Bulan terakhir dapat sisa
        $targets[] = [
            'bulan' => 12,
            'target_bulanan' => $totalTarget - $totalAllocated,
        ];

        return $targets;
    }

    /**
     * Get realisasi produksi per bulan
     */
    public function getRealisasiBulanan(int $produkId, int $tahun): array
    {
        $realisasi = Produksi::where('user_id', auth()->id())
            ->where('produk_id', $produkId)
            ->whereYear('tanggal_produksi', $tahun)
            ->selectRaw('MONTH(tanggal_produksi) as bulan, SUM(jumlah_produksi) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[$i] = $realisasi[$i] ?? 0;
        }

        return $result;
    }

    /**
     * Get perbandingan target vs realisasi
     */
    public function getComparison(TargetProduksi $target): array
    {
        $realisasi = $this->getRealisasiBulanan($target->produk_id, $target->tahun);
        
        $comparison = [];
        foreach ($target->details as $detail) {
            $realisasiBulan = $realisasi[$detail->bulan] ?? 0;
            $comparison[] = [
                'bulan' => $detail->bulan,
                'nama_bulan' => $detail->nama_bulan,
                'target' => $detail->target_bulanan,
                'realisasi' => $realisasiBulan,
                'selisih' => $realisasiBulan - $detail->target_bulanan,
                'persentase' => $detail->target_bulanan > 0 
                    ? round(($realisasiBulan / $detail->target_bulanan) * 100, 2) 
                    : 0,
                'status' => $detail->lock_status,
            ];
        }

        return $comparison;
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary(int $tahun): array
    {
        $targets = TargetProduksi::where('user_id', auth()->id())
            ->where('tahun', $tahun)
            ->with(['produk', 'details'])
            ->get();

        $totalTarget = $targets->sum('total_target_tahunan');
        $totalRealisasi = 0;
        $bulanEditable = 0;

        foreach ($targets as $target) {
            $totalRealisasi += $target->total_realisasi;
            
            // Hitung bulan yang masih editable
            foreach ($target->details as $detail) {
                if (!$detail->isLocked()) {
                    $bulanEditable++;
                }
            }
        }

        return [
            'total_target' => $totalTarget,
            'total_realisasi' => $totalRealisasi,
            'persentase' => $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 2) : 0,
            'selisih' => $totalRealisasi - $totalTarget,
            'jumlah_produk' => $targets->count(),
            'bulan_editable' => $bulanEditable,
        ];
    }

    /**
     * Validasi apakah target bisa dihapus
     */
    public function canDelete(TargetProduksi $target): array
    {
        $hasProductions = $target->hasProductions();

        return [
            'can_delete' => !$hasProductions,
            'message' => $hasProductions 
                ? 'Target produksi ini tidak dapat dihapus karena sudah digunakan dalam transaksi produksi.'
                : 'Target produksi dapat dihapus.',
        ];
    }

    /**
     * Get available years for histori
     */
    public function getAvailableYears(int $produkId): array
    {
        return TargetProduksi::where('user_id', auth()->id())
            ->where('produk_id', $produkId)
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->toArray();
    }

    /**
     * Check uniqueness: produk + tahun
     */
    public function isUnique(int $produkId, int $tahun, ?int $excludeId = null): bool
    {
        $query = TargetProduksi::where('user_id', auth()->id())
            ->where('produk_id', $produkId)
            ->where('tahun', $tahun);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Get months list
     */
    public function getMonthsList(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
}
