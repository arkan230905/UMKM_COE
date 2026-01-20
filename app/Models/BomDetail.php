<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_id',
        'bahan_baku_id',
        'jumlah',
        'satuan',
        'harga_per_satuan',
        'total_harga'
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'harga_per_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->total_harga = (float)($model->jumlah ?? 0) * (float)($model->harga_per_satuan ?? 0);
        });

        static::deleted(function ($model) {
            // no-op
        });
    }

    /**
     * Get the bahan baku that owns the BOM detail.
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class)->withDefault();
    }

    /**
     * Get the bahan pendukung that owns the BOM detail.
     */
    public function bahanPendukung()
    {
        return $this->belongsTo(BahanPendukung::class)->withDefault();
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
