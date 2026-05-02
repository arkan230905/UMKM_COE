<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'nama_vendor',
        'kategori',
        'alamat',
        'no_telp',
        'email'
    ];
    
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
