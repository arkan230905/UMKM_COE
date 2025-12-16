<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\AssetDepreciation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        // Hapus semua data penyusutan lama untuk aset ini
        \App\Models\AssetDepreciation::where('asset_id', $asset->id)->delete();

        // Tentukan metode penyusutan
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

        // Lock asset supaya tidak bisa dihapus setelah jadwal penyusutan dibuat
        $asset->locked = true;
        $asset->save();
    });
}

    /**
     * Calculate double declining balance depreciation
     * Logic sesuai Excel: rate × book value awal tahun, partial year calculation
     */
    private function computeDoubleDecliningBalance(Aset $asset): void
    {
        $cost = (float) $asset->harga_perolehan + (float) ($asset->biaya_perolehan ?? 0);
        $residual = (float) $asset->nilai_residu;
        $life = (int) $asset->umur_manfaat;

        if ($life <= 0 || $cost <= 0) {
            return;
        }

        $tarifPersen = $asset->tarif_penyusutan ?? ((100 / $life) * 2);
        $rate = $tarifPersen / 100;

        $startSource = $asset->tanggal_perolehan
            ?? $asset->tanggal_akuisisi
            ?? $asset->tanggal_beli
            ?? now();
        $startDate = Carbon::parse($startSource);

        $startMonth = $startDate->day > 15
            ? $startDate->copy()->addMonthNoOverflow()->startOfMonth()
            : $startDate->copy()->startOfMonth();

        $currentYear = $startMonth->year;
        $currentMonth = $startMonth->month;

        $totalMonths = $life * 12;
        $monthsFirstYear = 12 - $currentMonth + 1;
        if ($monthsFirstYear > $totalMonths) {
            $monthsFirstYear = $totalMonths;
        }

        $monthsRemaining = $totalMonths - $monthsFirstYear;
        $fullYears = intdiv(max($monthsRemaining, 0), 12);
        $monthsLastYear = $monthsRemaining % 12;

        $periods = [];
        if ($monthsFirstYear > 0) {
            $periods[] = ['year' => $currentYear, 'months' => $monthsFirstYear];
        }

        for ($i = 0; $i < $fullYears; $i++) {
            $year = $currentYear + ($i + 1);
            $periods[] = ['year' => $year, 'months' => 12];
        }

        if ($monthsLastYear > 0) {
            $year = $currentYear + $fullYears + (empty($periods) ? 0 : 1);
            $periods[] = ['year' => $year, 'months' => $monthsLastYear];
        }

        $book = $cost;
        $accumulated = 0.0;

        $periodCount = count($periods);

        foreach ($periods as $index => $period) {
            $months = (int) $period['months'];
            if ($months <= 0) {
                continue;
            }

            $fullYearDep = $book * $rate; // DDB base formula
            $depr = $fullYearDep * ($months / 12); // prorate by active months

            $maxDepreciable = round($book - $residual, 2);
            if ($maxDepreciable <= 0) {
                break;
            }

            if ($index === $periodCount - 1 || $depr > $maxDepreciable) {
                $depr = $maxDepreciable;
            }

            $depr = round($depr, 2);
            if ($depr <= 0) {
                continue;
            }

            $book = round($book - $depr, 2);
            if ($book < $residual) {
                $depr += $book - $residual;
                $book = $residual;
                $depr = round($depr, 2);
            }

            $accumulated = round($accumulated + $depr, 2);

            // catatan: prorata bulan pertama/terakhir sesuai aturan hari <=15 / >15
            $this->createDepreciationRecord(
                $asset,
                (int) $period['year'],
                $depr,
                $accumulated,
                $book
            );

            if ($book <= $residual + 0.00001) {
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

        if ($life <= 0 || $depreciableAmount <= 0) {
            return;
        }

        $totalYearsDigits = $life * ($life + 1) / 2;

        $tanggalPerolehan = $asset->tanggal_perolehan ?? $asset->tanggal_akuisisi ?? $asset->tanggal_beli;
        if (!$tanggalPerolehan) {
            $tanggalPerolehan = $asset->tanggal_beli;
        }

        $originalStartDate = Carbon::parse($tanggalPerolehan);
        [$effectiveStartDate, $monthsFirstYear, $carryOverMonths] = $this->determineSYDStartAllocation($originalStartDate);

        $startYear = $effectiveStartDate->year;

        $acc = 0.0;
        $book = $cost;

        for ($i = 0; $i < $life; $i++) {
            $year = $startYear + $i;
            $currentYearDigit = $life - $i;

            $portions = [];

            if ($i === 0) {
                if ($monthsFirstYear > 0) {
                    $portions[] = ['months' => $monthsFirstYear, 'digit' => $currentYearDigit];
                }
            } else {
                if ($carryOverMonths > 0) {
                    $prevYearDigit = $currentYearDigit + 1;
                    $portions[] = ['months' => $carryOverMonths, 'digit' => $prevYearDigit];
                }
                if ($monthsFirstYear > 0) {
                    $portions[] = ['months' => $monthsFirstYear, 'digit' => $currentYearDigit];
                }
            }

            foreach ($portions as $portion) {
                $months = (int) $portion['months'];
                $digit = (int) $portion['digit'];

                if ($months <= 0 || $digit <= 0) {
                    continue;
                }

                $depr = $depreciableAmount * ($months / 12) * ($digit / $totalYearsDigits);
                $depr = min($depr, max($book - $residual, 0));

                if ($depr <= 0) {
                    continue;
                }

                $depr = round($depr, 2);
                $acc = round($acc + $depr, 2);
                $book = round($book - $depr, 2);

                if ($book < $residual) {
                    $book = round($residual, 2);
                }

                $this->createDepreciationRecord($asset, $year, $depr, $acc, $book);

                if ($book <= $residual + 0.00001) {
                    break 2;
                }
            }
        }
    }

    /**
     * Determine effective start month allocation for SYD based on 15th-day rule.
     */
    private function determineSYDStartAllocation(Carbon $startDate): array
    {
        $effective = $startDate->copy();
        if ($startDate->day > 15) {
            $effective = $effective->addMonth()->startOfMonth();
        } else {
            $effective = $effective->startOfMonth();
        }

        $month = (int) $effective->month;
        $monthsFirstYear = max(0, min(12, 13 - $month));
        $carryOverMonths = 12 - $monthsFirstYear;
        if ($carryOverMonths < 0) {
            $carryOverMonths = 0;
        }

        return [$effective, $monthsFirstYear, $carryOverMonths];
    }
    /**
     * Calculate straight-line depreciation for an asset with unified proration.
     */
    private function computeStraightLine(Aset $asset): void
    {
        $cost = (float) $asset->harga_perolehan + (float) ($asset->biaya_perolehan ?? 0);
        $residual = (float) $asset->nilai_residu;
        $life = (int) $asset->umur_manfaat;

        $base = max($cost - $residual, 0);
        if ($life <= 0 || $base <= 0) {
            return;
        }

        [$effectiveStart, $allocations] = $this->buildYearlyAllocations($asset, true);
        if (empty($allocations)) {
            return;
        }

        $annualFull = $life > 0 ? $base / $life : 0;
        $accumulated = 0.0;
        $bookValue = $base + $residual;

        foreach ($allocations as $index => $allocation) {
            $months = (int) ($allocation['months'] ?? 0);
            if ($months <= 0) {
                continue;
            }

            $factor = $months / 12;
            $depr = round($annualFull * $factor, 2);

            $remainingDepreciable = round(max(($bookValue - $residual), 0), 2);
            $isLastPeriod = ($index === array_key_last($allocations));

            if ($depr > $remainingDepreciable || $isLastPeriod) {
                $depr = $remainingDepreciable;
            }

            if ($depr <= 0) {
                continue;
            }

            $accumulated = round($accumulated + $depr, 2);
            $bookValue = round(max($bookValue - $depr, $residual), 2);

            $this->createDepreciationRecord(
                $asset,
                (int) $allocation['year'],
                $depr,
                $accumulated,
                $bookValue
            );

            if ($bookValue <= $residual + 0.00001) {
                break;
            }
        }
    }

    /**
     * Build calendar year allocations based on global 15th-day proration rule.
     *
     * @return array{Carbon, array<int, array{year:int, months:int}>}
     */
    private function buildYearlyAllocations(Aset $asset, bool $includeEffective = false): array
    {
        $life = (int) $asset->umur_manfaat;
        if ($life <= 0) {
            return [$includeEffective ? now() : null, []];
        }

        $totalMonths = $life * 12;

        $startSource = $asset->tanggal_perolehan
            ?? $asset->tanggal_akuisisi
            ?? $asset->tanggal_beli
            ?? now();

        $originalStart = Carbon::parse($startSource);
        if ($originalStart->day > 15) {
            $effectiveStart = $originalStart->copy()->addMonthNoOverflow()->startOfMonth();
        } else {
            $effectiveStart = $originalStart->copy()->startOfMonth();
        }

        $allocations = [];
        $monthsRemaining = $totalMonths;
        $currentYear = $effectiveStart->year;
        $currentMonth = $effectiveStart->month;

        while ($monthsRemaining > 0) {
            $monthsThisYear = min($monthsRemaining, 12 - $currentMonth + 1);
            if ($monthsThisYear <= 0) {
                $currentYear++;
                $currentMonth = 1;
                continue;
            }

            $allocations[] = [
                'year' => $currentYear,
                'months' => $monthsThisYear,
            ];

            $monthsRemaining -= $monthsThisYear;
            $currentYear++;
            $currentMonth = 1;
        }

        return [$includeEffective ? $effectiveStart : null, $allocations];
    }

    /**
     * Build monthly depreciation breakdown for straight-line assets without mutating the stored schedule.
     *
     * @param  \App\Models\Aset  $asset
     * @param  \Illuminate\Support\Collection|array|null  $depreciationRows
     * @return array<int, array<string, mixed>>
     */
    public function buildMonthlySchedule(Aset $asset, $depreciationRows = null): array
    {
        $cost = (float)$asset->harga_perolehan + (float)($asset->biaya_perolehan ?? 0);
        $residual = (float)$asset->nilai_residu;
        $life = (int)$asset->umur_manfaat;

        $base = max($cost - $residual, 0);
        if ($life <= 0 || $base <= 0) {
            return [];
        }

        $startSource = $asset->tanggal_akuisisi ?? $asset->tanggal_beli ?? now();
        $startDate = Carbon::parse($startSource);

        if ($startDate->day > 15) {
            $startDate = $startDate->copy()->addMonth()->startOfMonth();
        } else {
            $startDate = $startDate->copy()->startOfMonth();
        }

        $totalMonths = $life * 12;
        $monthlyBase = $base / $totalMonths;

        $rows = $depreciationRows instanceof Collection
            ? $depreciationRows->keyBy('tahun')
            : collect($depreciationRows ?? [])->keyBy('tahun');

        $currentDate = $startDate->copy();
        $remainingMonths = $totalMonths;
        $remainingBase = $base;
        $accumulated = 0.0;

        $schedules = [];

        while ($remainingMonths > 0 && $remainingBase > 0.00001) {
            $currentYear = $currentDate->year;
            $monthsUntilYearEnd = 12 - $currentDate->month + 1;
            $monthsThisYear = max(0, min($remainingMonths, $monthsUntilYearEnd));

            if ($monthsThisYear <= 0) {
                $currentDate = $currentDate->copy()->startOfYear()->addYear();
                continue;
            }

            $targetAnnual = $monthlyBase * $monthsThisYear;
            if ($rows->has($currentYear)) {
                $targetAnnual = (float) $rows->get($currentYear)->beban_penyusutan;
            }

            $targetAnnual = min($targetAnnual, $remainingBase);
            $targetAnnual = round($targetAnnual, 2);

            if ($targetAnnual <= 0) {
                break;
            }

            $annualRemaining = $targetAnnual;
            $yearMonths = [];
            $startAccum = round($accumulated, 2);
            $startBook = round(max($cost - $accumulated, $residual), 2);

            for ($i = 0; $i < $monthsThisYear && $annualRemaining > 0.00001 && $remainingBase > 0.00001; $i++) {
                $isLastMonth = ($i === $monthsThisYear - 1)
                    || $annualRemaining <= $monthlyBase + 0.00001
                    || $remainingMonths === 1
                    || $remainingBase <= $monthlyBase + 0.00001;

                $rawAmount = $isLastMonth ? $annualRemaining : min($monthlyBase, $annualRemaining);
                $rawAmount = min($rawAmount, $remainingBase);

                $amount = round($rawAmount, 2);

                if ($amount <= 0 && $isLastMonth) {
                    $amount = round($annualRemaining, 2);
                }

                if ($amount <= 0) {
                    $currentDate = $currentDate->copy()->addMonth();
                    $remainingMonths--;
                    continue;
                }

                $accumulated = round($accumulated + $amount, 2);
                $remainingBase = round(max($remainingBase - $amount, 0), 2);
                $annualRemaining = round($annualRemaining - $amount, 2);
                $bookValue = round(max($cost - $accumulated, $residual), 2);

                $yearMonths[] = [
                    'label' => $currentDate->format('F Y'),
                    'amount' => $amount,
                    'accum' => $accumulated,
                    'book' => $bookValue,
                ];

                $currentDate = $currentDate->copy()->addMonth();
                $remainingMonths--;

                if ($bookValue <= $residual + 0.00001) {
                    $annualRemaining = 0;
                    $remainingBase = 0;
                    break;
                }
            }

            if (empty($yearMonths)) {
                continue;
            }

            $schedules[] = [
                'year' => $currentYear,
                'year_label' => $rows->has($currentYear) ? ($rows->get($currentYear)->tahun_label ?? null) : null,
                'months_in_period' => count($yearMonths),
                'annual_depreciation' => round(array_sum(array_column($yearMonths, 'amount')), 2),
                'accum_start' => $startAccum,
                'accum_end' => round($accumulated, 2),
                'book_start' => $startBook,
                'book_end' => round(max($cost - $accumulated, $residual), 2),
                'monthly_breakdown' => $yearMonths,
            ];
        }

        return $schedules;
    }

}
