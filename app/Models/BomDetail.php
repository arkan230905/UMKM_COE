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
        'kuantitas',
        'harga_satuan',
        'subtotal',
        'keterangan'
    ];

    protected $casts = [
        'kuantitas' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            $model->subtotal = $model->kuantitas * $model->harga_satuan;
            
            // Update total biaya di BOM
            if ($model->bom) {
                $model->bom->updateHargaJual();
            }
        });

        static::deleted(function ($model) {
            // Update total biaya di BOM saat detail dihapus
            if ($model->bom) {
                $model->bom->updateHargaJual();
            }
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
