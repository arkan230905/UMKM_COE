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
            
            // Hitung total biaya secara otomatis saat menyimpan
            if ($model->isDirty(['total_biaya', 'total_btkl', 'total_bop'])) {
                $model->hitungTotalBiaya();
            }
        });
        
        static::deleted(function ($model) {
            // Hapus detail BOM terkait
            $model->details()->delete();
        });
    }

    /**
     * Get the produk that owns the BOM.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class)->withDefault();
    }
    
    /**
     * Get the main bahan baku for the BOM.
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id')->withDefault();
    }
    
    /**
     * Get the details for the BOM.
     */
    public function details()
    {
        return $this->hasMany(BomDetail::class)->with(['bahanBaku' => function($query) {
            $query->with('satuan')->withDefault();
        }]);
    }
    
    /**
     * Calculate total cost of BOM.
     */
    public function calculateTotalCost()
    {
        $total = 0;
        
        foreach ($this->details as $detail) {
            $total += $detail->harga_per_satuan * $detail->jumlah;
        }
        
        $this->total_biaya = $total;
        $this->save();
        
        return $total;
    }
    
    /**
     * Calculate total cost including BTKL and BOP.
     */
    public function hitungTotalBiaya()
    {
        $totalBahanBaku = $this->details->sum('total_harga');
        $this->total_biaya = $totalBahanBaku + $this->total_btkl + $this->total_bop;
        return $this->total_biaya;
    }
    
    /**
     * Update related product price.
     */
    public function updateProductPrice()
    {
        if ($this->produk) {
            $hargaJual = $this->total_biaya * (1 + ($this->produk->margin_percent / 100));
            $this->produk->update(['harga_jual' => $hargaJual]);
        }
        return $this;
    }
}
