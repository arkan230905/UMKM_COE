<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'biaya_bahan_baku';

    protected $fillable = [
        'user_id',
        'produk_id',
        'bahan_baku_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'subtotal',
        'keterangan'
    ];

    protected $casts = [
        'jumlah' => 'decimal:4',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Relationship to BahanBaku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    /**
     * Relationship to HargaPokokProduksiBiayaBahanBaku
     */
    public function hargaPokokProduksiBiayaBahanBaku()
    {
        return $this->hasMany(HargaPokokProduksiBiayaBahanBaku::class);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    /**
     * Scope to filter by product
     */
    public function scopeByProduk($query, $produkId)
    {
        return $query->where('produk_id', $produkId);
    }

    /**
     * Boot method to auto-fill user_id and calculate subtotal
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Auto-fill user_id if not set
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
            
            // Auto-calculate subtotal
            $model->subtotal = $model->jumlah * $model->harga_satuan;
        });
        
        static::updating(function ($model) {
            // Auto-calculate subtotal when updating
            if ($model->isDirty(['jumlah', 'harga_satuan'])) {
                $model->subtotal = $model->jumlah * $model->harga_satuan;
            }
        });
    }
}