<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriProduk extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_produks';
    protected $fillable = [
        'user_id',
        'nama',
        'deskripsi'
    ];

    protected $dates = ['deleted_at'];
    
    /**
     * Boot method - apply global scope for multi-tenant
     */
    protected static function boot()
    {
        parent::boot();
        
        // CRITICAL: Apply global scope untuk multi-tenant isolation
        static::addGlobalScope(new \App\Scopes\UserScope);
        
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    /**
     * Get the produks for the kategori.
     */
    public function produks()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}
