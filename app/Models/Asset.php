<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_aset',
        'nama_asset',
        'tanggal_perolehan',
        'tanggal_beli',
        'harga_perolehan',
        'nilai_sisa',
        'umur_ekonomis',
        'id_perusahaan',
        'locked',
        'expense_coa_id',
        'accum_depr_coa_id',
        'metode_penyusutan',
        'tarif_penyusutan',
        'bulan_mulai',
    ];

    protected $casts = [
        'tanggal_perolehan' => 'date',
        'tanggal_beli' => 'date',
        'harga_perolehan' => 'decimal:2',
        'nilai_sisa' => 'decimal:2',
        'umur_ekonomis' => 'integer',
        'id_perusahaan' => 'integer',
        'locked' => 'boolean',
    ];

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function expenseCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'expense_coa_id', 'id');
    }

    public function accumDeprCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'accum_depr_coa_id', 'id');
    }

    // Depreciation per year (example-style)
    public function getDepreciationPerYearAttribute(): float
    {
        if ((int)($this->umur_ekonomis ?? 0) > 0) {
            return ((float)$this->harga_perolehan - (float)($this->nilai_sisa ?? 0)) / (int)$this->umur_ekonomis;
        }
        return 0.0;
    }

    // Calculate schedule monthly then aggregate per year (example-style)
    public function calculateDepreciationSchedule(): array
    {
        $tanggal_beli = $this->tanggal_beli ? Carbon::parse($this->tanggal_beli) : Carbon::now();
        $umur = (int)($this->umur_ekonomis ?? 0);
        $harga = (float)($this->harga_perolehan ?? 0);
        $residu = (float)($this->nilai_sisa ?? 0);
        if ($umur <= 0 || $harga <= 0) return [];

        $penyusutan_per_bulan = ($harga - $residu) / ($umur * 12);
        $akumulasi = 0.0; $nilai_buku = $harga; $byYear = [];

        for ($month = 0; $month < $umur * 12; $month++) {
            $current_date = $tanggal_beli->copy()->addMonths($month);
            $current_year = $current_date->year;

            $akumulasi += $penyusutan_per_bulan;
            $nilai_buku -= $penyusutan_per_bulan;

            if (!isset($byYear[$current_year])) {
                $byYear[$current_year] = [
                    'tahun' => $current_year,
                    'biaya_penyusutan' => 0,
                    'akumulasi_penyusutan' => 0,
                    'nilai_buku' => $harga,
                    'start_month' => $current_date->copy(),
                    'end_month' => $current_date->copy(),
                    'monthly_breakdown' => [],
                ];
            }

            $byYear[$current_year]['monthly_breakdown'][] = [
                'month' => $current_date->format('F Y'),
                'biaya_penyusutan' => round($penyusutan_per_bulan, 2),
                'akumulasi_penyusutan' => round($akumulasi, 2),
                'nilai_buku' => round(max($nilai_buku, $residu), 2),
            ];

            $byYear[$current_year]['biaya_penyusutan'] += $penyusutan_per_bulan;
            $byYear[$current_year]['akumulasi_penyusutan'] = $akumulasi;
            $byYear[$current_year]['nilai_buku'] = max($nilai_buku, $residu);
            $byYear[$current_year]['end_month'] = $current_date->copy();
        }

        foreach ($byYear as &$year) {
            $year['biaya_penyusutan'] = round($year['biaya_penyusutan'], 2);
            $year['akumulasi_penyusutan'] = round($year['akumulasi_penyusutan'], 2);
            $year['nilai_buku'] = round($year['nilai_buku'], 2);
            $year['periode'] = $year['tahun'] . ' (' . $year['start_month']->format('F') . ' – ' . $year['end_month']->format('F') . ')';
            unset($year['start_month'], $year['end_month']);
        }

        return array_values($byYear);
    }
}
