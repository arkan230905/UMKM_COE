<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaPokokProduksiBop extends Model
{
    use HasFactory;

    protected $table = 'harga_pokok_produksi_bop';

    protected $fillable = [
        'user_id',
        'produk_id',
        'bop_proses_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'bop_proses_id' => 'integer',
    ];
    
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bopProses()
    {
        return $this->belongsTo(BopProses::class);
    }

    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
