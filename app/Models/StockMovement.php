<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type','item_id','tanggal','direction','qty','satuan','unit_cost','total_cost','ref_type','ref_id'
    ];
}
