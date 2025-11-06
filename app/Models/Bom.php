<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id',
        'kode_bom',
        'total_biaya',
        'persentase_keuntungan',
        'harga_jual',
        'catatan'
    ];

    protected $casts = [
        'total_biaya' => 'decimal:2',
        'persentase_keuntungan' => 'decimal:2',
        'harga_jual' => 'decimal:2'
    ];

    protected $appends = ['keuntungan'];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (is_null($model->total_biaya)) {
                $model->total_biaya = $model->hitungTotalBiaya();
            }
            // Tidak mengubah harga_jual produk di tahap BOM
        });
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function details()
    {
        return $this->hasMany(BomDetail::class);
    }

    public function getKeuntunganAttribute()
    {
        return $this->total_biaya * ($this->persentase_keuntungan / 100);
    }

    public function hitungTotalBiaya()
    {
        return $this->details->sum('subtotal');
    }

    public function hitungHargaJual()
    {
        return $this->total_biaya + $this->keuntungan;
    }

    public function updateHargaJual()
    {
        $this->total_biaya = $this->hitungTotalBiaya();
        $this->harga_jual = $this->hitungHargaJual();
        $this->save();

        // Update harga jual di produk
        if ($this->produk) {
            $this->produk->update(['harga_jual' => $this->harga_jual]);
        }
        
        return $this;
    }

    public static function calculateBopForAll($periode = null)
    {
        $bom = new static;
        return $bom->calculateBop($periode);
    }
}
