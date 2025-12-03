<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\KategoriAset;

class Aset extends Model
{
    protected $table = 'asets';
    
    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori_aset_id',
        'harga_perolehan',
        'biaya_perolehan',
        'nilai_residu',
        'umur_manfaat',
        'penyusutan_per_tahun',
        'penyusutan_per_bulan',
        'nilai_buku',
        'tanggal_beli',
        'tanggal_akuisisi',
        'status',
        'metode_penyusutan',
        'akumulasi_penyusutan',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_beli' => 'date',
        'tanggal_akuisisi' => 'date',
        'harga_perolehan' => 'decimal:2',
        'biaya_perolehan' => 'decimal:2',
        'nilai_residu' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'akumulasi_penyusutan' => 'decimal:2',
        'penyusutan_per_tahun' => 'decimal:2',
        'penyusutan_per_bulan' => 'decimal:2',
        'umur_manfaat' => 'integer',
    ];

    /**
     * Relationship ke COA
     */
    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }

    /**
     * Relationship ke KategoriAset
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriAset::class, 'kategori_aset_id');
    }

    /**
     * Relationship ke User (created_by)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship ke User (updated_by)
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship ke Depreciation Schedules
     */
    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class);
    }

    /**
     * Generate kode aset otomatis
     * Format: AST-YYYYMM-XXXX
     */
    public static function generateKodeAset()
    {
        $prefix = 'AST-' . date('Ym') . '-';
        $lastAsset = self::where('kode_aset', 'like', $prefix . '%')
            ->orderBy('kode_aset', 'desc')
            ->first();

        $number = $lastAsset ? (int) str_replace($prefix, '', $lastAsset->kode_aset) + 1 : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method untuk menangani event model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->kode_aset) {
                $model->kode_aset = self::generateKodeAset();
            }
            $model->metode_penyusutan = $model->metode_penyusutan ?? 'garis_lurus';
            $model->nilai_residu = $model->nilai_residu ?? 0;
            $model->nilai_buku = ($model->harga_perolehan ?? 0) + ($model->biaya_perolehan ?? 0);
            $model->akumulasi_penyusutan = $model->akumulasi_penyusutan ?? 0;
            $model->status = $model->status ?? 'aktif';
            if (Schema::hasColumn('asets', 'created_by')) {
                $model->created_by = $model->created_by ?? Auth::id();
            }
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    /**
     * Hitung penyusutan (untuk kompatibilitas dengan AsetController)
     */
    public function hitungPenyusutan()
    {
        // Calculate nilai_buku based on total perolehan (harga + biaya) and nilai_residu
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $this->nilai_buku = $total - (float)($this->nilai_residu ?? 0);
    }

    /**
     * Hitung beban penyusutan bulanan
     */
    public function hitungBebanPenyusutanBulanan(): float
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $nilaiTerdepresiasi = $total - (float)($this->nilai_residu ?? 0);
        $umurTahun = (int)($this->umur_manfaat ?? $this->umur_ekonomis_tahun ?? 0);
        $bulanEkonomis = max($umurTahun, 0) * 12;

        return $bulanEkonomis > 0 ? ($nilaiTerdepresiasi / $bulanEkonomis) : 0.0;
    }

    /**
     * Hitung beban penyusutan tahunan
     */
    public function hitungBebanPenyusutanTahunan(): float
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $nilaiTerdepresiasi = $total - (float)($this->nilai_residu ?? 0);
        $umurTahun = (int)($this->umur_manfaat ?? $this->umur_ekonomis_tahun ?? 0);
        return $umurTahun > 0 ? ($nilaiTerdepresiasi / $umurTahun) : 0.0;
    }

    /**
     * Cek apakah aset bisa dihapus
     */
    public function bisaDihapus(): bool
    {
        return $this->akumulasi_penyusutan == 0;
    }

    /**
     * Update nilai buku berdasarkan akumulasi penyusutan
     */
    public function updateNilaiBuku(): void
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $this->nilai_buku = $total - (float)($this->akumulasi_penyusutan ?? 0);
        $this->save();
    }

    /**
     * Accessor: penyusutan per tahun (depreciation_per_year) ala contoh.
     */
    public function getDepreciationPerYearAttribute(): float
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $umur = (int)($this->umur_manfaat ?? $this->umur_ekonomis_tahun ?? 0);
        if ($umur > 0) {
            return ($total - (float)($this->nilai_residu ?? 0)) / $umur;
        }
        return 0.0;
    }

    /**
     * Monthly schedule aggregated by year with monthly_breakdown, following the user's example.
     */
    public function calculateDepreciationSchedule(): array
    {
        $startDate = Carbon::parse($this->tanggal_akuisisi ?? $this->tanggal_beli ?? now());
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $residu = (float)($this->nilai_residu ?? 0);
        $umur = (int)($this->umur_manfaat ?? $this->umur_ekonomis_tahun ?? 0);
        if ($umur <= 0 || $total <= 0) return [];

        $perBulan = ($total - $residu) / ($umur * 12);
        $akumulasi = 0.0; $nilaiBuku = $total;
        $byYear = [];

        for ($m = 0; $m < $umur * 12; $m++) {
            $current = $startDate->copy()->addMonths($m);
            $year = $current->year;

            $akumulasi += $perBulan;
            $nilaiBuku -= $perBulan;

            if (!isset($byYear[$year])) {
                $byYear[$year] = [
                    'tahun' => $year,
                    'biaya_penyusutan' => 0,
                    'akumulasi_penyusutan' => 0,
                    'nilai_buku' => $total,
                    'start_month' => $current->copy(),
                    'end_month' => $current->copy(),
                    'monthly_breakdown' => [],
                ];
            }

            $byYear[$year]['monthly_breakdown'][] = [
                'month' => $current->format('F Y'),
                'biaya_penyusutan' => round($perBulan, 2),
                'akumulasi_penyusutan' => round($akumulasi, 2),
                'nilai_buku' => round(max($nilaiBuku, $residu), 2),
            ];

            $byYear[$year]['biaya_penyusutan'] += $perBulan;
            $byYear[$year]['akumulasi_penyusutan'] = $akumulasi;
            $byYear[$year]['nilai_buku'] = max($nilaiBuku, $residu);
            $byYear[$year]['end_month'] = $current->copy();
        }

        foreach ($byYear as &$row) {
            $row['biaya_penyusutan'] = round($row['biaya_penyusutan'], 2);
            $row['akumulasi_penyusutan'] = round($row['akumulasi_penyusutan'], 2);
            $row['nilai_buku'] = round($row['nilai_buku'], 2);
            $row['periode'] = $row['tahun'] . ' (' . $row['start_month']->format('F') . ' – ' . $row['end_month']->format('F') . ')';
            unset($row['start_month'], $row['end_month']);
        }

        return array_values($byYear);
    }

    /**
     * Generate jadwal penyusutan tahunan (garis lurus)
     */
    public function jadwalPenyusutan(): array
    {
        $total = (float)($this->harga_perolehan ?? 0) + (float)($this->biaya_perolehan ?? 0);
        $residu = (float)($this->nilai_residu ?? 0);
        $umur = (int)($this->umur_manfaat ?? $this->umur_ekonomis_tahun ?? 0);
        if ($umur <= 0 || $total <= 0) return [];

        $base = max($total - $residu, 0);
        $perTahun = $base / $umur;
        $start = $this->tanggal_akuisisi ?? $this->tanggal_beli ?? now()->toDateString();
        $startYear = Carbon::parse($start)->year;

        $acc = 0.0; $book = $total; $rows = [];
        for ($i = 0; $i < $umur; $i++) {
            $year = $startYear + $i;
            $dep = min($perTahun, max($book - $residu, 0));
            $acc += $dep; $book -= $dep;
            $rows[] = [
                'tahun' => $year,
                'beban_penyusutan' => round($dep, 2),
                'akumulasi_penyusutan' => round($acc, 2),
                'nilai_buku_akhir' => round($book, 2),
            ];
        }
        return $rows;
    }
}
