<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaPokokProduksiBiayaBahanBaku extends Model
{
    use HasFactory;

    protected $table = 'harga_pokok_produksi_biaya_bahan_baku';

    protected $fillable = [
        'user_id',
        'bom_job_bbb_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'bom_job_bbb_id' => 'integer',
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

    public function biayaBahanBaku()
    {
        return $this->belongsTo(BiayaBahanBaku::class, 'bom_job_bbb_id');
    }

    // Keep old relationship name for backward compatibility
    public function bomJobBbb()
    {
        return $this->belongsTo(BiayaBahanBaku::class, 'bom_job_bbb_id');
    }

    public function scopeByUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
