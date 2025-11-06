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
        'total_harga',
        'keterangan'
    ];

    protected $casts = [
        'jumlah' => 'decimal:4',
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

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
