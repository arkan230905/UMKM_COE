<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_details';

    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'jumlah',
        'harga_satuan',
        'diskon_persen',
        'diskon_nominal',
        'subtotal',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    /**
     * Boot method untuk model
     */
    protected static function booted()
    {
        parent::booted();
        
        // Auto-assign user_id saat creating
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
        
        // Global scope untuk data isolation (multi-tenant)
        static::addGlobalScope('user', function ($builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });
    }
}
