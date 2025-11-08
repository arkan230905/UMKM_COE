<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'bahan_baku_id',
        'jumlah',
        'satuan_resep',
        'total_biaya',
        'btkl_per_unit',
        'bop_rate',
        'bop_per_unit',
        'total_btkl',
        'total_bop',
        'periode',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'jumlah' => 'decimal:4',
        'total_biaya' => 'decimal:2',
        'btkl_per_unit' => 'decimal:2',
        'bop_rate' => 'decimal:2',
        'bop_per_unit' => 'decimal:2',
        'total_btkl' => 'decimal:2',
        'total_bop' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->periode)) {
                $model->periode = now()->format('Y-m');
            }
        });
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function details()
    {
        return $this->hasMany(BomDetail::class, 'bom_id');
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function hitungTotalBiaya()
    {
        $totalBahanBaku = $this->details->sum('total_harga');
        $this->total_biaya = $totalBahanBaku + $this->total_btkl + $this->total_bop;
        return $this->total_biaya;
    }

    public function updateHargaProduk()
    {
        if ($this->produk) {
            $this->produk->update(['harga' => $this->total_biaya]);
        }
        return $this;
    }
}
