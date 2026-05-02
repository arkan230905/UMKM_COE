<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type','item_id','tanggal','remaining_qty','unit_cost','satuan','ref_type','ref_id'
    ];

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
