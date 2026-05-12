<?php

namespace App\Services;

use Carbon\Carbon;

class DepreciationService
{
    public function scheduleStraightLine(array $asset, string $per = 'monthly'): array
    {
        [$cost,$residual,$lifeYears,$start] = [
            (float)($asset['acquisition_cost'] ?? 0),
            (float)($asset['residual_value'] ?? 0),
            (int)($asset['useful_life_years'] ?? 0),
            $asset['depr_start_date'] ?? null,
        ];
        if ($lifeYears <= 0 || !$start || $cost <= 0) return [];
        $months = $lifeYears * 12;
        $base = max($cost - $residual, 0);
        $perMonth = $months > 0 ? ($base / $months) : 0;
        $acc = 0; $book = $cost;
        $dt = Carbon::parse($start)->startOfMonth();
        $rows = [];
        for ($i=1; $i <= $months; $i++) {
            $dep = min($perMonth, $book - $residual);
            $acc += $dep; $book -= $dep;
            $rows[] = [
                'period' => $dt->copy()->format('Y-m'),
                'depreciation' => round($dep,2),
                'accumulated' => round($acc,2),
                'book_value' => round($book,2),
            ];
            $dt->addMonth();
            if ($book <= $residual + 0.005) break;
        }
        return $rows;
    }

    public function scheduleDDB(array $asset, string $per = 'monthly'): array
    {
        [$cost,$residual,$lifeYears,$start] = [
            (float)($asset['acquisition_cost'] ?? 0),
            (float)($asset['residual_value'] ?? 0),
            (int)($asset['useful_life_years'] ?? 0),
            $asset['depr_start_date'] ?? null,
        ];
        if ($lifeYears <= 0 || !$start || $cost <= 0) return [];
        $rateYear = 2 / $lifeYears; // double declining
        $months = $lifeYears * 12; $acc=0; $book=$cost; $dt=Carbon::parse($start)->startOfMonth();
        $rows=[];
        for ($i=1; $i <= $months; $i++) {
            $depYear = $book * $rateYear; // annual amount if full year
            $dep = $depYear / 12.0;
            if ($book - $dep < $residual) $dep = $book - $residual;
            if ($dep < 0) $dep = 0;
            $acc += $dep; $book -= $dep;
            $rows[] = [
                'period' => $dt->copy()->format('Y-m'),
                'depreciation' => round($dep,2),
                'accumulated' => round($acc,2),
                'book_value' => round($book,2),
            ];
            $dt->addMonth();
            if ($book <= $residual + 0.005) break;
        }
        return $rows;
    }

    public function scheduleSYD(array $asset, string $per = 'monthly'): array
    {
        [$cost,$residual,$lifeYears,$start] = [
            (float)($asset['acquisition_cost'] ?? 0),
            (float)($asset['residual_value'] ?? 0),
            (int)($asset['useful_life_years'] ?? 0),
            $asset['depr_start_date'] ?? null,
        ];
        if ($lifeYears <= 0 || !$start || $cost <= 0) return [];
        $base = max($cost - $residual,0);
        $syd = $lifeYears * ($lifeYears + 1) / 2.0;
        $dt = Carbon::parse($start)->startOfMonth();
        $acc=0; $book=$cost; $rows=[]; $months=$lifeYears*12;
        // convert annual SYD to monthly by spreading each year's quota evenly across 12 months
        for ($year=1; $year <= $lifeYears; $year++) {
            $annual = $base * (($lifeYears - $year + 1)/$syd);
            $perMonth = $annual / 12.0;
            for ($m=1;$m<=12;$m++){
                $dep = min($perMonth, $book - $residual);
                $acc += $dep; $book -= $dep;
                $rows[] = [
                    'period' => $dt->copy()->format('Y-m'),
                    'depreciation' => round($dep,2),
                    'accumulated' => round($acc,2),
                    'book_value' => round($book,2),
                ];
                $dt->addMonth();
                if (count($rows) >= $months || $book <= $residual + 0.005) break 2;
            }
        }
        return $rows;
    }

    public function scheduleUnitsOfProduction(array $asset, array $unitsPerPeriod = []): array
    {
        [$cost,$residual,$capacity,$start] = [
            (float)($asset['acquisition_cost'] ?? 0),
            (float)($asset['residual_value'] ?? 0),
            (int)($asset['units_capacity_total'] ?? 0),
            $asset['depr_start_date'] ?? null,
        ];
        if ($capacity <= 0 || !$start || $cost <= 0) return [];
        $ratePerUnit = max($cost - $residual, 0) / $capacity;
        $dt = Carbon::parse($start)->startOfMonth();
        $acc=0; $book=$cost; $rows=[];
        // If units per period not provided, assume equal monthly units over 5 years as a fallback
        if (empty($unitsPerPeriod)) {
            $months = 60; // default 5 years
            $per = (int) floor($capacity / $months);
            for ($i=0;$i<$months;$i++){ $unitsPerPeriod[] = $per; }
        }
        foreach ($unitsPerPeriod as $u) {
            $dep = $ratePerUnit * (int)$u;
            if ($book - $dep < $residual) { $dep = $book - $residual; }
            if ($dep < 0) $dep = 0;
            $acc += $dep; $book -= $dep;
            $rows[] = [
                'period' => $dt->copy()->format('Y-m'),
                'units' => (int)$u,
                'depreciation' => round($dep,2),
                'accumulated' => round($acc,2),
                'book_value' => round($book,2),
            ];
            $dt->addMonth();
            if ($book <= $residual + 0.005) break;
        }
        return $rows;
    }
}
