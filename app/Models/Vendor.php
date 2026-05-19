<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'nama_vendor',
        'kategori',
        'alamat',
        'no_telp',
        'email'
    ];
    
    /**
     * Boot method to auto-fill user_id for multi-tenant isolation
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
    
    protected $appends = ['nama'];

    public function pembelians()
    {
        return $this->hasMany(Pembelian::class);
    }
    
    /**
     * Accessor untuk backward compatibility
     * Agar bisa menggunakan $vendor->nama
     */
    public function getNamaAttribute()
    {
        return $this->nama_vendor;
    }
    
    /**
     * Accessor untuk telepon
     */
    public function getTeleponAttribute()
    {
        return $this->no_telp;
    }
}
