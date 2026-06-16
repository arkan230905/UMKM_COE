<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','item_type','item_id','tanggal','remaining_qty','unit_cost','satuan','ref_type','ref_id'
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && !$model->user_id) {
                $model->user_id = auth()->id();
            }
        });
    }
}
