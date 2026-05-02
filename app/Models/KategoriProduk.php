<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriProduk extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_produks';
    protected $fillable = [
        'kode_kategori',
        'nama',
        'deskripsi'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the produks for the kategori.
     */
    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
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
