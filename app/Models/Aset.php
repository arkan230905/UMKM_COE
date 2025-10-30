<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Aset extends Model
{
    protected $table = 'asets';
    
    protected $fillable = [
        'kode_aset',
        'nama_aset',
        'kategori_aset_id',
        'harga_perolehan',
        'biaya_perolehan',
        'total_perolehan',
        'nilai_residu',
        'umur_manfaat',
        'penyusutan_per_tahun',
        'penyusutan_per_bulan',
        'acquisition_cost',
        'residual_value',
        'useful_life_years',
        'depr_start_date',
        'depr_method',
        'units_capacity_total',
        'nilai_buku',
        'status',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_beli' => 'date',
        'depr_start_date' => 'date',
        'harga' => 'decimal:2',
        'acquisition_cost' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'useful_life_years' => 'integer',
        'units_capacity_total' => 'integer',
    ];

    /**
     * Generate kode aset otomatis
     * Format: AST-YYYYMM-XXXX
     * 
     * @return string
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

    // Boot method untuk menangani event model
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set nilai default jika tidak diisi
            $model->depr_method = $model->depr_method ?? 'straight_line';
            $model->residual_value = $model->residual_value ?? 0;
            $model->nilai_buku = $model->acquisition_cost ?? $model->harga;
            $model->status = $model->status ?? 'aktif';
        });
    }
}
